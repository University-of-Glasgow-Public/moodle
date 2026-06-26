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
 * TODO describe file rebuild
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

use local_ugassessment\snapshot_builder;

global $PAGE, $OUTPUT, $DB, $SITE;

require_login();

$url = new moodle_url('/local/ugassessment/reset.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

$confirm = optional_param('confirm', 0, PARAM_BOOL);

if ($confirm) {
    \core\notification::add(get_string('resetsnapshot', 'local_ugassessment'), \core\output\notification::NOTIFY_INFO);

    $count = \local_ugassessment\snapshot_builder::delete_snapshot();

    echo $OUTPUT->notification(get_string('resetsuccess', 'local_ugassessment'), 'notifysuccess');

    echo $OUTPUT->continue_button(new moodle_url('/local/ugassessment/manage.php', []));

    // Trigger event.
    $event = \local_ugassessment\event\snapshot_reset::create([
        'context' => \context_system::instance(),
        'other' => [
            'mode' => 'reset',
            'deleted' => $count,
        ],
    ]);
    $event->trigger();
} else {
    $confirmurl = new moodle_url('/local/ugassessment/reset.php', ['confirm' => 1]);
    $cancelurl = new moodle_url('/local/ugassessment/reset.php', [
        'section' => 'local_ugassessment',
    ]);

    echo $OUTPUT->heading(get_string('resetsnapshot', 'local_ugassessment'));

    echo $OUTPUT->notification(
        get_string('resetwarning', 'local_ugassessment'),
        'notifyproblem'
    );

    echo $OUTPUT->confirm(
        get_string('resetconfirm', 'local_ugassessment'),
        $confirmurl,
        $cancelurl
    );
}



echo $OUTPUT->footer();
