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

namespace local_ugassessment\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Class update_snapshot
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_snapshot extends \core\task\scheduled_task {

    /**
     * Task name shown in admin screens
     * @return string
     */
    public function get_name() {
        return get_string('task_updatesnapshot', 'local_ugassessment');
    }

    /**
     * Execute the task
     */
    public function execute() {
        mtrace('UGAssessment: Scheduled snapshot update START');

        \local_ugassessment\snapshot_builder::rebuild_snapshot(false);

        mtrace('UGAssessment: Scheduled snapshot update END');
    }
}
