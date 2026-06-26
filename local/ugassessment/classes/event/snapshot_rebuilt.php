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

namespace local_ugassessment\event;

/**
 * Event snapshot_rebuilt
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snapshot_rebuilt extends \core\event\base {
    /**
     * Set basic properties for the event.
     */
    protected function init() {
        $this->data['crud'] = 'c'; // Created (full rebuild).
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get the name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_snapshot_rebuilt', 'local_ugassessment');
    }

    /**
     * Get the description of the event.
     *
     * @return string
     */
    public function get_description() {
        $mode = isset($this->other['mode']) ? $this->other['mode'] : 'unknown action';
        $processed = isset($this->other['processed']) ? $this->other['processed'] : 'unknown';
        $inserted = isset($this->other['inserted']) ? $this->other['inserted'] : 'unknown';
        $existed = isset($this->other['existed']) ? $this->other['existed'] : 'unknown';
        $multiplecodecourses = isset($this->other['multiplecodecourses']) ? (empty($this->other['multiplecodecourses']) ? 'none' :
            implode(', ', $this->other['multiplecodecourses'])) : 'unknown';
        return "User with id '{$this->userid}' performed {$mode} on the UG Assessment snapshot:
        {$existed} records already existed before the rebuild, they have been erased.
        The new snapshot has been built with
        processed={$processed},
        inserted={$inserted},
        course ids with multiple codes={$multiplecodecourses}.";
    }
}
