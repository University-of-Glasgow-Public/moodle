<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_ugassessment;
use core_tag_tag;

/**
 * Class snapshot_builder
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snapshot_builder {
    /**
     * Rebuild the snapshot table with current course and assessment metadata.
     * This is a heavy operation and should be run via CLI or scheduled task, not on demand.
     * The snapshot is a denormalized cache of course and assessment metadata to speed up API responses.
     */
    public static function rebuild_snapshot($reset = false) {
        global $DB;

        mtrace('UGAssessment: rebuild_snapshot started (reset=' . ($reset ? 'true' : 'false') . ')');
        $count = 0; // Counter for processed activities in the cron task log.
        $inserted = 0; // Counter for inserted records.
        $updated = 0; // Counter for updated records.
        $unchanged = 0; // Counter for unchanged records.
        $deleted = 0; // Counter for deleted records.
        $multiplecodecourses = []; // List of courses with multiple MyCampus codes.

        // If reset is true, clear existing snapshot.
        if ($reset) {
            self::delete_snapshot();
        }

        $keyword = trim((string)get_config('local_ugassessment', 'gradecategorykeyword'));
        if (empty($keyword)) {
            $keyword = 'summative';
        }

        $ignore = (int)get_config('local_ugassessment', 'ignorestudentmygrades');
        if (empty($ignore)) {
            $ignore = 0;
        }

        $enabledtypes = get_config('local_ugassessment', 'activitytypes');

        // Moodle stores multiselect as comma-separated string.
        $enabledtypes = !empty($enabledtypes) ? explode(',', $enabledtypes) : [];

        $courseids = self::get_filtered_courseids($ignore);

        if (empty($courseids)) {
            // Nothing to process.
            return;
        }

        // Preload MyCampus enrolment codes to minimize DB queries in the loop.
        $gucodemap = [];
        $gucodes = $DB->get_records_list('enrol_gudatabase_codes', 'courseid', $courseids);

        foreach ($gucodes as $g) {
            $gucodemap[$g->courseid][] = $g;
        }
        foreach ($gucodemap as $cid => $records) {
            if (count($records) > 1) {
                $multiplecodecourses[] = $cid;
            }
        }

        // Preload grade items for all courses to minimize DB queries in the loop.
        $gradeitems = $DB->get_records_list('grade_items', 'courseid', $courseids);

        $gradeitemmap = [];

        foreach ($gradeitems as $gi) {
            if ($gi->itemmodule && $gi->iteminstance) {
                $key = $gi->courseid . '_' . $gi->itemmodule . '_' . $gi->iteminstance;
                $gradeitemmap[$key][] = $gi;
            }
        }

        // Preload grade categories for all courses to minimize DB queries in the loop.
        $categories = $DB->get_records_list('grade_categories', 'courseid', $courseids);

        $categorymap = [];

        foreach ($categories as $cat) {
            $categorymap[$cat->id] = $cat;
        }

        // Preload assignment data if assignments are enabled to minimize DB queries in the loop.
        $assignmap = [];

        if (in_array('assign', $enabledtypes)) {
            $assigns = $DB->get_records_list('assign', 'course', $courseids);

            foreach ($assigns as $a) {
                $assignmap[$a->id] = $a;
            }
        }

        // Preload quiz data if quizzes are enabled to minimize DB queries in the loop.
        $quizmap = [];

        if (in_array('quiz', $enabledtypes)) {
            $quizzes = $DB->get_records_list('quiz', 'course', $courseids);

            foreach ($quizzes as $q) {
                $quizmap[$q->id] = $q;
            }
        }

        // Preload the full snapshot to minimize DB queries in the loop.
        // We will compare against this to determine if anything has changed.
        $snapshot = $DB->get_records('local_ugassessment_snapshot');

        $snapshotmap = [];

        foreach ($snapshot as $s) {
            $snapshotmap[$s->cmid] = $s;
        }

        list($insql, $params) = $DB->get_in_or_equal($courseids);

        $courses = $DB->get_records_select('course', "id $insql", $params);

        // Preload tags for course modules to minimize DB queries in the loop.
        $tagmap = [];

        $sql = "
            SELECT ti.itemid AS cmid, t.name
            FROM {tag_instance} ti
            JOIN {tag} t ON t.id = ti.tagid
            JOIN {course_modules} cm ON cm.id = ti.itemid
            WHERE ti.component = 'core'
            AND ti.itemtype = 'course_modules'
            AND t.isstandard = 1
            AND cm.course $insql
        ";

        $rs = $DB->get_recordset_sql($sql, $params);

        foreach ($rs as $tag) {
            $tagmap[$tag->cmid][] = $tag->name;
        }

        $rs->close();

        $handler = \core_course\customfield\course_handler::create();

        // We will keep track of the course modules we see in this run. After processing all courses.
        // Any snapshot records with cmids not in this list can be marked as deleted.
        $currentcmids = [];

        foreach ($courses as $course) {
            $data = $handler->get_instance_data($course->id);

            $fieldmap = [];

            foreach ($data as $fielddata) {
                $shortname = $fielddata->get_field()->get('shortname');
                $fieldmap[$shortname] = $fielddata->export_value();
            }

            // Get the MyCampus enrolment codes and subjects for this course.
            $gucodes = $gucodemap[$course->id] ?? [];

            $codes = [];
            $subjects = [];

            foreach ($gucodes as $g) {
                if (!empty($g->code)) {
                    $codes[] = $g->code;
                }
                if (!empty($g->subject)) {
                    $subjects[] = $g->subject;
                }
            }

            $modinfo = get_fast_modinfo($course);

            foreach ($modinfo->get_cms() as $cm) {
                if (!in_array($cm->modname, $enabledtypes)) {
                    continue;
                }
                // Match course module to grade item using the preloaded map.
                $key = $course->id . '_' . $cm->modname . '_' . $cm->instance;
                $gradeitem = $gradeitemmap[$key] ?? [];

                if (empty($gradeitem)) {
                    continue;
                }

                // Get category path. We need this to apply the summative filter.
                $issummative = false;

                foreach ($gradeitem as $gi) {
                    if (empty($gi->categoryid)) {
                        continue;
                    }

                    $category = $categorymap[$gi->categoryid] ?? null;

                    if ($category && self::is_summative_activity($category, $keyword, $categorymap)) {
                        $issummative = true;
                        break;
                    }
                }

                if (!$issummative) {
                    continue;
                }

                $record = new \stdClass();

                // Course metadata.
                $record->coursefullname   = $course->fullname;
                $record->coursevisible = $course->visible;

                // Course MyCampus codes and subjects as comma-separated values.
                if (count($gucodes) === 1) {
                    // Only one code should be there.
                    $g = reset($gucodes);
                    $record->coursecode = $g->code ?? null;
                    $record->coursesubject = $g->subject ?? null;
                } else if (count($gucodes) > 1) {
                    // Flag clearly.
                    $record->coursecode = 'MULTIPLE_CODES';
                    $record->coursesubject = null;
                } else {
                    // No codes.
                    $record->coursecode = null;
                    $record->coursesubject = null;
                }

                // Course custom field values.
                $record->academicyear = $fieldmap['academicyear'] ?? null;
                $record->qualification = $fieldmap['qualification'] ?? null;
                $record->semester = $fieldmap['semester'] ?? null;
                $record->studentmygrades = $fieldmap['studentmygrades'] ?? null;

                // Activity metadata.
                $dates = self::get_activity_dates_from_cm($cm);
                $record->timeopenorfrom = $dates['timeopenorfrom'];
                $record->timecloseordue = $dates['timecloseordue'];
                $record->tags = isset($tagmap[$cm->id])
                    ? implode(';', $tagmap[$cm->id])
                    : '';
                $record->cmid         = $cm->id;
                $record->assessmenttype      = $cm->modname;
                $record->activityname = $cm->name;
                $record->activityvisible = $cm->visible;
                $record->url          = $cm->url ? $cm->url->out(false) : '';

                // Activity might come back so we reset the deleted flag in case it was previously marked as deleted.
                $record->deleted = 0;

                $currentcmids[] = $cm->id;

                $count++; // Increment the counter for each processed activity.

                // Assignment specific data.

                if ($cm->modname === 'assign') {
                    $assign = $assignmap[$cm->instance] ?? null;

                    if ($assign) {
                        $record->teamsubmission = $assign->teamsubmission;
                        $record->timelimit = $assign->timelimit;
                    }
                }

                // Quiz specific data.
                if ($cm->modname === 'quiz') {
                    $quiz = $quizmap[$cm->instance] ?? null;

                    if ($quiz) {
                        $record->timelimit = $quiz->timelimit;
                    }
                }

                // Timestamp for when this record was extracted. This allows us to do incremental updates in the future if needed.
                $record->timeextracted = time();

                // Is the activity already in the snapshot? If so, check for changes.
                $existing = $snapshotmap[$cm->id] ?? null;

                if ($existing) {
                    // Compare relevant fields.
                    // If any relevant field has changed, update the record and timestamp.
                    // Otherwise, do nothing to preserve the original extraction time.
                    $changed = false;

                    foreach ($record as $field => $value) {
                        if ($field === 'timeextracted') {
                            continue; // Skip timestamp field in comparison.
                        }

                        if (!property_exists($existing, $field) || $existing->$field != $value) {
                            $changed = true;
                            break;
                        }
                    }

                    if ($changed) {
                        $record->id = $existing->id;
                        $record->timeextracted = time();
                        $DB->update_record('local_ugassessment_snapshot', $record);
                        $updated++;
                    } else {
                        $unchanged++;
                    }
                } else {
                    $record->timeextracted = time();
                    $DB->insert_record('local_ugassessment_snapshot', $record);
                    $snapshotmap[$record->cmid] = $record;
                    $inserted++;
                }
            }
        }
        if (!$reset) {
            // If not a full rebuild, we want to flag deleted records for activities that no longer exist.

            $countcmids = count($currentcmids);

            if ($countcmids === 0) {
                // No activities found, mark all as deleted.
                $condition = "deleted = 0";
                $params = [];
            } else {
                if ($countcmids === 1) {
                    // Only one activity, we can use a simple condition.
                    $single = reset($currentcmids);
                    $condition = "cmid <> :singlecmid AND deleted = 0";
                    $params = ['singlecmid' => $single];
                } else {
                    list($insql, $params) = $DB->get_in_or_equal(
                        $currentcmids,
                        SQL_PARAMS_NAMED,
                        'cmid'
                    );

                    $condition = "cmid NOT $insql AND deleted = 0";
                }
            }
            $now = time();

            $todelete = $DB->get_records_select('local_ugassessment_snapshot', $condition, $params, '', 'id');
            $deleted += count($todelete);

            $DB->set_field_select('local_ugassessment_snapshot', 'timeextracted', $now, $condition, $params);
            $DB->set_field_select('local_ugassessment_snapshot', 'deleted', 1, $condition, $params);
        }
        mtrace('UGAssessment: rebuild_snapshot completed');
        mtrace('UGAssessment: processed records = ' . $count
            . ' (inserted=' . $inserted
            . ', updated=' . $updated
            . ', unchanged=' . $unchanged
            . ', deleted=' . $deleted . ')');
        if (!empty($multiplecodecourses)) {
            mtrace('UGAssessment WARNING: courseids with multiple MyCampus codes = ' . implode(', ', $multiplecodecourses));
        }
    }

    /**
     * Get course IDs filtered by the selected custom field and value from settings.
     * Only courses that have the specified value in the specified custom field will be included.
     * This is used to determine which courses to include in the snapshot and API responses.
     * Returns an array of course IDs that match the filter criteria.
     */
    private static function get_filtered_courseids(int $ignore) {
        global $DB;

        $fieldshortname = get_config('local_ugassessment', 'fieldshortname');
        $fieldvalue     = get_config('local_ugassessment', 'fieldvalue');

        // Safety: if config not set, return empty.
        if (empty($fieldshortname) || $fieldvalue === '') {
            return [];
        }

        $params = [
            'shortname' => $fieldshortname,
            'component1' => 'core_course',
            'value'     => $fieldvalue,
        ];

        // Base SQL.
        $sql = "
            SELECT d.instanceid
            FROM {customfield_data} d
            JOIN {customfield_field} f ON f.id = d.fieldid
            JOIN {customfield_category} c ON c.id = f.categoryid
            WHERE f.shortname = :shortname
            AND c.component = :component1
            AND d.value = :value
        ";

        // Optional StudentMyGrades filter.
        if (!$ignore) {
            $sql .= "
                AND d.instanceid IN (
                    SELECT d2.instanceid
                    FROM {customfield_data} d2
                    JOIN {customfield_field} f2 ON f2.id = d2.fieldid
                    JOIN {customfield_category} c2 ON c2.id = f2.categoryid
                    WHERE f2.shortname = :smgshortname
                    AND c2.component = :component2
                    AND d2.value = '1'
                )
            ";

            $params['smgshortname'] = 'studentmygrades';
            $params['component2'] = 'core_course';
        }

        return $DB->get_fieldset_sql($sql, $params);
    }

    /**
     * Determine if a grade category (and thus the associated activity) is summative based on its path.
     * The path is a slash-separated list of category IDs from the root to the current category.
     * We check the level 2 category (the direct child of the root) for the presence of the word "summative" in its name.
     *
     * @param \stdClass $category grade category record.
     * @param string $keyword keyword to search in the level 2 category name.
     * @return bool
     */
    private static function is_summative_activity(\stdClass $category, string $keyword, array $categorymap): bool {
        global $DB;

        $pathids = explode('/', trim($category->path, '/'));

        // Need at least level 2.
        if (count($pathids) < 2) {
            return false;
        }

        $level2id = $pathids[1];

        if (empty($categorymap[$level2id])) {
            return false;
        }

        $level2category = $categorymap[$level2id];

        return stripos($level2category->fullname, $keyword) !== false;
    }

    /**
     * Delete the entire snapshot table.
     */
    public static function delete_snapshot() {
        global $DB;

        // Clear existing snapshot.
        $DB->delete_records('local_ugassessment_snapshot');
    }

    /**
     * Get activity dates from a course module.
     *
     * @param \stdClass $cm Course module record.
     * @return array
     */
    private static function get_activity_dates_from_cm($cm) {

        $data = (array)($cm->customdata ?? []);

        return [
            'timeopenorfrom'          => $data['timeopen'] ?? $data['allowsubmissionsfromdate'] ?? 0,
            'timecloseordue'    => $data['timeclose'] ?? $data['duedate'] ?? 0,
            'cutoffdate'        => $data['cutoffdate'] ?? 0,
        ];
    }

    /**
     * Get tags for a course module.
     *
     * @param \stdClass $cm Course module record.
     * @return array
     */
    private static function get_cm_tags($cm) {
        $tags = core_tag_tag::get_item_tags_array('core', 'course_modules', $cm->id, core_tag_tag::STANDARD_ONLY);

        if (empty($tags)) {
            return '';
        }

        return implode(';', array_values($tags));
    }
}
