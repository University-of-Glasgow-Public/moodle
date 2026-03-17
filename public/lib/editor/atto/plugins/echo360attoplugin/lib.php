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
 * Atto text editor integration version file.
 *
 * @package   atto_echo360attoplugin
 * @copyright 2020 Echo360 Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/locallib.php');

const ECHO360ATTOPLUGIN_NAME = 'atto_echo360attoplugin';
const FILTER_PATH = "/filter/echo360/lti_launch.php";

/**
 * Initialize this plugin
 */
function atto_echo360attoplugin_strings_for_js() {
    global $CFG, $COURSE, $PAGE;

    $PAGE->requires->strings_for_js(array('dialogtitle', 'ltiConfiguration'), ECHO360ATTOPLUGIN_NAME);
    $PAGE->requires->js_init_code("moodle_page_type = \"". $PAGE->pagetype . "\";");

    // Pass current Context Course Id.
    if (isset($COURSE)) {
        $contextcourse = context_course::instance($COURSE->id);
        $PAGE->requires->js_init_code("echo360_context_course_id = " . $contextcourse->id . ";");

        // Check LTI 1.3 configuration enabled in Echo360 Atto Plugin.
        $lti1p3configurationenabled = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationenabled');
        // Selected Echo360 LTI 1.3 Configuration's deployment id.
        $lti1p3toolid = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationselection');
        if ($lti1p3configurationenabled == true && ($lti1p3toolid != null && $lti1p3toolid != 0)) {
            $lti1p3configured = "true";
        } else {
            $lti1p3configured = "false";
        }
        $PAGE->requires->js_init_code("lti1p3configured = " . $lti1p3configured . ";");
    }
    // Pass current Context Module Id and Filter LTI Launch URL to Echo360 Atto Plugin button to set in homework embedded URL.
    if (isset($PAGE->cm->id)) {
        $PAGE->requires->js_init_code("echo360_context_module_id = " . $PAGE->cm->id . ";");
    } else {
        $PAGE->requires->js_init_code("echo360_context_module_id = 0;");
    }
    $PAGE->requires->js_init_code("echo360_filter_lti_launch_url = \"" . $CFG->wwwroot . FILTER_PATH . "\";");
}

/**
 * Return the JavaScript params required for this module.
 *
 * @param  $elementid
 * @param  $options
 * @param  $fpoptions
 * @return mixed
 */
function atto_echo360attoplugin_params_for_js($elementid, $options, $fpoptions) {
    global $PAGE, $COURSE;

    // Config our array of data.
    $params = array();
    $params['disabled'] = true; // Default to hidden until context visibility can be confirmed.
    $params['course'] = $COURSE;
    // Used for callback reference.
    $params['resourcebase'] = sha1($PAGE->url . '&' . $PAGE->course->sortorder . '&' . $PAGE->course->timecreated);

    if (!empty($COURSE)) {
        $context = context_course::instance($COURSE->id);
        if (!empty($context)) {
            $params['disabled'] = (!has_capability('atto/echo360attoplugin:visible', $context));
        }
    }

    return $params;
}

