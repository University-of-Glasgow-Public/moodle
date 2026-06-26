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
 * TODO describe file logs
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$page = optional_param('page', 0, PARAM_INT);
$perpage = 25;

$offset = $page * $perpage;

$filter = optional_param('filter', '', PARAM_ALPHANUMEXT);
$download = optional_param('download', '', PARAM_ALPHA);

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/ugassessment/logs.php');
$PAGE->set_pagelayout('admin');

$PAGE->set_context($context);

$PAGE->set_title('UG Assessment Logs');
$PAGE->set_heading('UG Assessment Logs');

$logmanager = get_log_manager();
$readers = $logmanager->get_readers();
$reader = reset($readers); // First available log reader.

$where = "component = :component";
$params = ['component' => 'local_ugassessment'];

if (!empty($filter)) {
    $where .= " AND eventname = :eventname";
    $params['eventname'] = '\\local_ugassessment\\event\\' . $filter;
}


$total = $reader->get_events_select_count(
    $where,
    $params
);

$events = $reader->get_events_select(
    $where,
    $params,
    "timecreated DESC",
    $offset,
    $perpage
);

$table = new html_table();
$table->head = [
    'Time',
    'User',
    'Action',
    'Details',
];

foreach ($events as $event) {
    $user = \core_user::get_user($event->userid, '*', MUST_EXIST);

    $time = userdate($event->timecreated);
    $username = fullname($user);
    $action = $event->get_name();
    $details = $event->get_description();

    $table->data[] = [
        $time,
        $username,
        $action,
        $details,
    ];
}

$options = [
    '' => 'All',
    'snapshot_refreshed' => 'Refreshed',
    'snapshot_rebuilt' => 'Rebuilt',
    'snapshot_reset' => 'Reset',
];

if ($download === 'csvall') {

    // Get ALL events (no paging, no filter override)
    $allwhere = "component = :component";
    $allparams = ['component' => 'local_ugassessment'];

    $allevents = $reader->get_events_select(
        $allwhere,
        $allparams,
        "timecreated DESC",
        0,
        0,
    );

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ugassessment_logs_all.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['Time', 'User', 'Action', 'Details']);

    foreach ($allevents as $event) {
        $user = \core_user::get_user($event->userid);

        $details = $event->get_description();

        // Replace line breaks with space.
        $details = str_replace(["\r", "\n"], ' ', $details);

        // Collapse multiple spaces into one.
        $details = preg_replace('/\s+/', ' ', $details);

        // Trim leading/trailing spaces.
        $details = trim($details);

        fputcsv($output, [
            userdate($event->timecreated),
            $user ? fullname($user) : 'Unknown',
            $event->get_name(),
            $details,
        ]);
    }

    fclose($output);
    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Snapshot Activity Logs');

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => new moodle_url('/local/ugassessment/logs.php')
]);

echo html_writer::select(
    $options,
    'filter',
    $filter,
    false,
    ['onchange' => 'this.form.submit()']
);


echo html_writer::end_tag('form');


echo html_writer::table($table);

$baseurl = new moodle_url('/local/ugassessment/logs.php', ['filter' => $filter]);

echo $OUTPUT->paging_bar($total, $page, $perpage, $baseurl);

echo $OUTPUT->single_button(
    new moodle_url('/local/ugassessment/logs.php', ['download' => 'csvall', 'filter' => '']),
    'Download All Logs (CSV)'
);

echo $OUTPUT->footer();
