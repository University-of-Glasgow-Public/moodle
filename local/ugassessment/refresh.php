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
 * Refresh the snapshot table, inserting new records and updating existing ones.
 * Soft-deletes records that are no longer eligible. Does not rebuild the table from scratch.
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use local_ugassessment\snapshot_builder;

global $PAGE, $OUTPUT, $DB, $SITE;

require_login();

$url = new moodle_url('/local/ugassessment/refresh.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

$confirm = optional_param('confirm', 0, PARAM_BOOL);

if ($confirm) {
    \core\notification::add(get_string('refreshsnapshot', 'local_ugassessment'), \core\output\notification::NOTIFY_INFO);

    $start = microtime(true);
    $startmemory = memory_get_usage(true);
    $peakstart = memory_get_peak_usage(true);
    $startqueries = $DB->perf_get_queries();

    $result = \local_ugassessment\snapshot_builder::rebuild_snapshot(false);

    $duration = microtime(true) - $start;
    $endmemory = memory_get_usage(true);
    $peakend = memory_get_peak_usage(true);
    $endqueries = $DB->perf_get_queries();

    mtrace('Snapshot rebuild completed in ' . round($duration, 2) . ' seconds.');
    mtrace(' - Memory start: ' . round($startmemory / 1024 / 1024, 2) . ' MB');
    mtrace(' - Memory end: ' . round($endmemory / 1024 / 1024, 2) . ' MB');
    mtrace(' - Peak memory: ' . round($peakend / 1024 / 1024, 2) . ' MB');
    mtrace(' - DB queries: ' . ($endqueries - $startqueries));

    echo $OUTPUT->notification(get_string('snapshotrefreshsuccess', 'local_ugassessment'), 'notifysuccess');

    echo $OUTPUT->continue_button(new moodle_url('/local/ugassessment/manage.php', []));

    // Trigger event.
    $event = \local_ugassessment\event\snapshot_refreshed::create([
        'context' => context_system::instance(),
        'other' => [
                'mode' => 'incremental refresh',
                'processed' => $result['processed'],
                'inserted' => $result['inserted'],
                'updated' => $result['updated'],
                'unchanged' => $result['unchanged'],
                'deleted' => $result['deleted'],
                'multiplecodecourses' => $result['multiplecodecourses'],
            ],
    ]);
    $event->trigger();
} else {
    $confirmurl = new moodle_url('/local/ugassessment/refresh.php', ['confirm' => 1]);
    $cancelurl = new moodle_url('/local/ugassessment/manage.php', [
        'section' => 'local_ugassessment',
    ]);

    echo $OUTPUT->heading(get_string('refreshsnapshot', 'local_ugassessment'));

    echo $OUTPUT->notification(
        get_string('refreshwarning', 'local_ugassessment'),
        'info'
    );

    echo $OUTPUT->confirm(
        get_string('refreshconfirm', 'local_ugassessment'),
        $confirmurl,
        $cancelurl
    );
}

echo $OUTPUT->footer();
