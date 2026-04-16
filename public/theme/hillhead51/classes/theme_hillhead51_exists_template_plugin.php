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

namespace theme_hillhead51;

/**
 * This class determines if the Course Template Wizard is available
 *
 * @package    theme_hillhead51
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_hillhead51_exists_template_plugin {

    /**
     * Implementing the logic of the previous approach, but within a class.
     */
    public static function template_plugin_exists() {
        global $CFG;

        if (file_exists("{$CFG->dirroot}/local/template/version.php")) {
            if (is_readable("{$CFG->dirroot}/local/template/version.php")) {
                // Moodle codechecker incorrectly asserts require_once must use parenthesis.
                // @codingStandardsIgnoreLine
                require_once $CFG->dirroot . '/local/template/locallib.php';
                local_template_add_new_course_hook();
                return true;
            }
            return false;
        }
        return false;
    }

}
