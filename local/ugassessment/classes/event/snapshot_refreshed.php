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
 * Event class for when the UG Assessment snapshot is refreshed.
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_ugassessment\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event class for when the UG Assessment snapshot is refreshed.
 */
class snapshot_refreshed extends \core\event\base {
    /**
     * Initialize the event data.
     */
    protected function init() {
        $this->data['crud'] = 'u'; // Update.
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns the name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_snapshot_refreshed', 'local_ugassessment');
    }

    /**
     * Returns the description of the event.
     *
     * @return string
     */
    public function get_description() {
        $mode = isset($this->other['mode']) ? $this->other['mode'] : 'unknown action';
        $processed = isset($this->other['processed']) ? $this->other['processed'] : 'unknown';
        $inserted = isset($this->other['inserted']) ? $this->other['inserted'] : 'unknown';
        $updated = isset($this->other['updated']) ? $this->other['updated'] : 'unknown';
        $unchanged = isset($this->other['unchanged']) ? $this->other['unchanged'] : 'unknown';
        $deleted = isset($this->other['deleted']) ? $this->other['deleted'] : 'unknown';
        $multiplecodecourses = isset($this->other['multiplecodecourses']) ? count($this->other['multiplecodecourses']) : 'unknown';
        return "User with id '{$this->userid}' performed {$mode} on the UG Assessment snapshot:
        processed={$processed},
        inserted={$inserted},
        updated={$updated},
        unchanged={$unchanged},
        deleted={$deleted},
        courses with multiple codes={$multiplecodecourses}.";
    }
}
