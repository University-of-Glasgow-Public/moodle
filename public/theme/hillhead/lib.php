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

defined('MOODLE_INTERNAL') || die();

/**
 * Main lib file. This will be deprecated eventually.
 *
 * @package    theme_hillhead
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Theme functions.
 *
 * @package    theme_hillhead
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function theme_hillhead_get_main_scss_content($theme) {

    global $CFG;
    $scss = '';
    $sheets = ['config'];

    // These scss files should declare default values for "variables" that will be used by Moodle...
    foreach ($sheets as $sheet) {
        $scss .= file_get_contents($CFG->dirroot . '/theme/hillhead/scss/'.$sheet.'.scss');
    }

    // ...now append the main scss file style rules...
    $scss .= theme_boost_get_main_scss_content($theme);

    $sheets = ['hillhead', 'accessibility', 'login'];

    // ...these scss files should declare more specific css "rules"...
    foreach ($sheets as $sheet) {
        $scss .= file_get_contents($CFG->dirroot . '/theme/hillhead/scss/'.$sheet.'.scss');
    }

    // ...finally append the "preset" scss "vars" and "rules" from the settings,
    // which will override the ones used in the Moodle and Bootstrap SCSS files...
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : 'blue.scss';
    $scss .= file_get_contents($CFG->dirroot . '/theme/hillhead/scss/'.$filename);

    return $scss;
}

/**
 * Returns whether the course selector plugin /local/template is present.
 *
 * @return boolean Whether the plugin is available.
 */
function theme_hillhead_exists_template_plugin() {
    global $CFG;

    if (file_exists("{$CFG->dirroot}/local/template/version.php")) {
        return is_readable("{$CFG->dirroot}/local/template/version.php");
    }
    return false;
}

if (theme_hillhead_exists_template_plugin()) {
    global $CFG;

    // Moodle codechecker incorrectly asserts require_once must use parenthesis.
    // @codingStandardsIgnoreLine
    require_once $CFG->dirroot . '/local/template/locallib.php';
    local_template_add_new_course_hook();
}
