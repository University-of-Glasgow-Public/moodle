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

namespace local_ugassessment\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use context_system;

/**
 * Class get_data
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_data extends external_api {
    /**
     * No parameters needed for this function, but we must define the structure.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
                'since' => new external_value(PARAM_INT, 'Timestamp for incremental sync', VALUE_DEFAULT, 0),
                'limit' => new external_value(PARAM_INT, 'Maximum number of records to return', VALUE_OPTIONAL, 0),
                'lastcmid' => new external_value(PARAM_INT, 'Last course module ID for pagination', VALUE_DEFAULT, 0),
            ]);
    }

    /**
     * Execute the function.
     *
     * @return array
     */
    public static function execute($since = 0, $limit = 0, $lastcmid = 0) {
        global $DB;

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/ugassessment:view', $context);

        // PoC convenience: populate if empty. In production, this should be done via CLI or scheduled task.
        if (!$DB->record_exists('local_ugassessment_snapshot', [])) {
            \local_ugassessment\snapshot_builder::rebuild_snapshot();
        }

        $configlimit = (int)get_config('local_ugassessment', 'paginationlimit');
        if ($configlimit <= 0) {
            $configlimit = 1000;
        }
        if ($limit <= 0) {
            // No limit passed → use config.
            $limit = $configlimit;
        } else {
            // Enforce max cap.
            $limit = min($limit, $configlimit);
        }

        $params = [];
        $sql = "1=1";
        // Implementing incremental sync based on timeextracted and cmid for pagination.
        // This allows clients to fetch new/updated records since their last sync.
        if ($since > 0) {
            $sql .= " AND (
                timeextracted > :since1
                OR (timeextracted = :since2 AND cmid > :lastcmid)
            )";
            $params['since1'] = $since;
            $params['since2'] = $since;
            $params['lastcmid'] = $lastcmid;
        }

        $records = $DB->get_records_select(
            'local_ugassessment_snapshot',
            $sql,
            $params,
            'timeextracted ASC, cmid ASC', // Order by timeextracted first, then cmid for consistent pagination.
            '*',
            0,
            $limit
        );
        // Determine if there are more records to fetch for pagination.
        $hasmore = count($records) === $limit;

        // Get the last timeextracted and cmid for pagination info.
        $lasttime = 0;
        $lastcmid = 0;

        if (!empty($records)) {
            $last = end($records);
            $lasttime = $last->timeextracted;
            $lastcmid = $last->cmid;
        }

        return [
            'data' => array_values($records),
            'hasmore' => $hasmore,
            'lasttime' => $lasttime,
            'lastcmid' => $lastcmid,
        ];
    }

    /**
     * Returns the structure of the returned data.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
        'data' => new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'ID'),
                'coursefullname' => new external_value(PARAM_TEXT, 'Course name'),
                'coursevisible' => new external_value(PARAM_BOOL, 'Course visible'),
                'academicyear' => new external_value(PARAM_TEXT, 'Academic year'),
                'qualification' => new external_value(PARAM_TEXT, 'Qualification'),
                'semester' => new external_value(PARAM_TEXT, 'Semester'),
                'studentmygrades' => new external_value(PARAM_URL, 'Student My Grades Boolean'),
                'cmid' => new external_value(PARAM_INT, 'Course module ID'),
                'assessmenttype' => new external_value(PARAM_TEXT, 'Module type'),
                'activityname' => new external_value(PARAM_TEXT, 'Activity name'),
                'activityvisible' => new external_value(PARAM_BOOL, 'Activity visible'),
                'timecloseordue' => new external_value(PARAM_INT, 'Time close or due date'),
                'teamsubmission' => new external_value(PARAM_BOOL, 'Team submission'),
                'tags' => new external_value(PARAM_TEXT, 'Comma-separated list of tags'),
                'url' => new external_value(PARAM_URL, 'Activity URL'),
                'timeextracted' => new external_value(PARAM_INT, 'Last extracted timestamp'),
                'deleted' => new external_value(PARAM_BOOL, 'Soft delete flag'),
            ])
        ),
        'hasmore' => new external_value(PARAM_BOOL, 'Whether there are more records to fetch'),
        'lasttime' => new external_value(PARAM_INT, 'Last extracted timestamp'),
        'lastcmid' => new external_value(PARAM_INT, 'Last course module ID for pagination'),
        ]);
    }
}
