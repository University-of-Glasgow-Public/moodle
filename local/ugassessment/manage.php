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

/**
 * TODO describe file manage
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

global $PAGE, $OUTPUT, $SITE;

$context = context_system::instance();
require_capability('moodle/site:config', $context);


$url = new moodle_url('/local/ugassessment/manage.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('managepagename', 'local_ugassessment'));
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managepagename', 'local_ugassessment'), 2);

// Count of records in snapshot.
global $DB;
$count = $DB->count_records('local_ugassessment_snapshot');
$deletedcount = $DB->count_records('local_ugassessment_snapshot', ['deleted' => 1]);


$sql = "SELECT COUNT(DISTINCT coursefullname)
          FROM {local_ugassessment_snapshot}
         WHERE coursecode = :code";

$params = [
    'code' => 'MULTIPLE_CODES',
];

$multiplecodes = $DB->count_records_sql($sql, $params);

$sql = "SELECT COUNT(DISTINCT coursefullname)
          FROM {local_ugassessment_snapshot}
         WHERE coursecode IS NULL OR coursecode = ''";

$missingcodes = $DB->count_records_sql($sql);

$title = html_writer::tag('h4', 'Current snapshot record');

$list = html_writer::alist([
    '<i class="fa fa-database text-primary mr-2"></i> Total records: ' . $count,
    '<i class="fa fa-trash text-danger mr-2"></i> Deleted records: ' . $deletedcount,
    '<i class="fa fa-circle-plus text-warning mr-2"></i> Courses with multiple codes: ' . $multiplecodes,
    '<i class="fa fa-circle-minus text-warning mr-2"></i> Courses with missing codes: ' . $missingcodes,
], ['class' => 'ml-4']);

echo html_writer::div($title . $list, 'm-5');

// Export link only if there are records in the snapshot.
if ($count > 0) {
    $exporturl = new moodle_url('/local/ugassessment/export.php');
    $iconexport = $OUTPUT->pix_icon('i/export', '', 'moodle');

    echo html_writer::div(
        html_writer::link(
            $exporturl,
            $iconexport . ' ' . get_string('exportsnapshot', 'local_ugassessment'),
            ['class' => 'btn btn-secondary']
        ),
        'm-5'
    );
}

// Alert.

$iconwarning = $OUTPUT->pix_icon('a/refresh', '', 'moodle');

echo $OUTPUT->notification(
    $iconwarning . ' ' . get_string('refreshwarning', 'local_ugassessment'),
    'info'
);

// Button.
$rebuildurl = new moodle_url('/local/ugassessment/refresh.php');

echo html_writer::div(
    html_writer::link(
        $rebuildurl,
        get_string('refreshsnapshot', 'local_ugassessment'),
        ['class' => 'btn btn-info']
    ),
    'mb-3'
);

// Warning.

$iconwarning = $OUTPUT->pix_icon('i/warning', '', 'moodle');

echo $OUTPUT->notification(
    $iconwarning . ' ' . get_string('rebuildwarning', 'local_ugassessment'),
    'warning'
);

// Button.
$rebuildurl = new moodle_url('/local/ugassessment/rebuild.php');

echo html_writer::div(
    html_writer::link(
        $rebuildurl,
        get_string('rebuildsnapshot', 'local_ugassessment'),
        ['class' => 'btn btn-warning']
    ),
    'mb-3'
);

// Danger zone.

$icond = $OUTPUT->pix_icon('req', '', 'moodle');

echo $OUTPUT->notification(
    $icond . ' ' . get_string('resetwarning', 'local_ugassessment'),
    'notifyproblem'
);

// Button.
$reseturl = new moodle_url('/local/ugassessment/reset.php');

echo html_writer::div(
    html_writer::link(
        $reseturl,
        get_string('deletesnapshot', 'local_ugassessment'),
        ['class' => 'btn btn-danger']
    ),
    'mb-3'
);

echo $OUTPUT->footer();
