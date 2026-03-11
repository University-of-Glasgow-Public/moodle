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
 * Echo360 view file for LTI content.
 *
 * @package    filter_echo360
 * @copyright  2020 Echo360 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/lib/editor/atto/plugins/echo360attoplugin/LtiConfiguration.php');

global $USER, $PAGE, $COURSE, $SESSION;

// Query string parameters.
$url    = required_param('url', PARAM_URL);             // LTI url.
$cmid   = required_param('cmid', PARAM_INT);            // Course module id.
$width  = optional_param('width', null, PARAM_INT);     // IFrame width (optional to support lti_launch_url links).
$height = optional_param('height', null, PARAM_INT);    // IFrame height (optional to support lti_launch_url links).
$resourcelinkid = optional_param('resourcelinkid', "0", PARAM_TEXT);    // LTI resourcelinkidparam added by button and filter.

const ECHO360_LABEL_MOD_NAME = 'label';
const ECHO360ATTOPLUGIN_NAME = 'atto_echo360attoplugin';
const ECHO360ATTOPLUGIN_VERSION = '2.0.13';

if ($width != null && $height != null) {
    if ($width < 50 || $width > 3000) {
        $width = 600;
    }
    if ($height < 50 || $height > 3000) {
        $height = 400;
    }
}

try {
    // There are some scenarios where we set $cmid to 0 on filter.php. For those cases,
    // the exception block is called, where a course is instantiated by using the courseId
    // value read from the URL.
    $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $context = context_course::instance($course->id);
    // Set identified Course Module context for cmid value in authenticated embedded Echo360 media link.
    $PAGE->set_cm($cm, $course);
} catch (Exception $e) {
    // Do not know the Course by the Course Module specified in the embedded Echo360 media link,
    // use HTTP referer to determine Course context.
    if (isset($_SERVER['HTTP_REFERER'])) {
        // Check HTTP_REFERER is a view.php page to extract the id / course query value.
        if (substr(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH), -strlen('view.php')) === 'view.php') {
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $refererparams);

            if (isset($refererparams['id']) && is_numeric($refererparams['id'])) {
                $courseid = $refererparams['id'];
            } else if (isset($refererparams['course']) && is_numeric($refererparams['course'])) {
                $courseid = $refererparams['course'];
            } else {
                $courseid = null;
            }
        } else if (substr(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH), -strlen('section.php')) === 'section.php') {
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $refererparams);

            if (isset($refererparams['id']) && is_numeric($refererparams['id'])) {
                $sectionid = $refererparams['id'];
                $section = $DB->get_record('course_sections', ['id' => $sectionid], 'course');
                $courseid = $section->course;
            } else {
                $courseid = null;
            }
        } else {
            $courseid = null;
        }
    }

    if (isset($courseid)) {
        $course = $DB->get_record('course', array('id' => $courseid));
        $context = context_course::instance($course->id);
    } else {
        $context = context_system::instance(); // Unable to determine Course context, default to Site context.
    }
    // Set identified Course / Site context for cmid value in authenticated embedded Echo360 media link.
    $PAGE->set_context($context);
}

// Verify user access.
if (isset($course)) {
    require_login($course, true);
} else {
    require_login();
}

// Remote LTI call.
const ATTO_PLUGIN_NAME = 'atto_echo360attoplugin';
$lti        = new Echo360\LtiConfiguration($context, ATTO_PLUGIN_NAME);
$params     = array();
if ($width != null && $height != null) {
    $params = array(
        'launch_presentation_document_target' => 'iframe',
        'launch_presentation_width'           => $width,
        'launch_presentation_height'          => $height
    );
}

$lticonfig = $lti->generate_lti_configuration($params);

class CustomDeepLink {
    /**
     * Generate the form for initiating a login request for an LTI 1.3 message
     *
     * @param int            $courseid  Course ID
     * @param int            $id        LTI instance ID
     * @param stdClass|null  $instance  LTI instance
     * @param stdClass       $config    Tool type configuration
     * @param string         $messagetype   LTI message type
     * @param string         $title     Title of content item
     * @param string         $text      Description of content item
     * @return string
     */
    public function lti_initiate_login($courseid, $id, $instance, $config, $messagetype, $title, $text,
            $deeplinkurl, $customparams) {
        global $SESSION;

        $stdparams = lti_build_login_request($courseid, $id, $instance, $config, $messagetype);
        $params = array_merge($stdparams, $customparams);

        // To prevent multiple embeds in pages/forums inadvertently overriding
        // or clearing each other's session checks in the launch stage in the
        // auth.php file.
        $SESSION->lti_message_hint_arr["{$id}"] = "{$courseid},{$config->typeid},{$id}," . base64_encode($title) . ',' .
            base64_encode($text) . ',' . base64_encode($deeplinkurl);

        $r = "<form action=\"" . $config->lti_initiatelogin .
            "\" name=\"ltiInitiateLoginForm\" id=\"ltiInitiateLoginForm\" method=\"post\" " .
            "encType=\"application/x-www-form-urlencoded\">\n";

        foreach ($params as $key => $value) {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars($value);
            $r .= "  <input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>\n";
        }
        $r .= "</form>\n";

        $r .= "<script type=\"text/javascript\">\n" .
            "//<![CDATA[\n" .
            "document.ltiInitiateLoginForm.submit();\n" .
            "//]]>\n" .
            "</script>\n";

        return $r;
    }
}

class DeepLink {
    public $toolurl;
}

// Read Echo360 Atto Plugin optional LTI 1.3 settings.
$lti1p3configurationenabled = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationenabled');
// Selected Echo360 LTI 1.3 Configuration's deployment id.
$toolid = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationselection');

if (($lti1p3configurationenabled) && (!is_null($toolid)) && ($toolid != 0)) {
    // Retrieve LTI 1.3 external tool configuration.
    $config = lti_get_type_type_config($toolid);
    // Check if deep link url is same domain as LTI 1.3 config tool url.
    $configtoolurlparts = parse_url($config->lti_toolurl);
    $deeplinkurlparts = parse_url($url);
    if ($config->lti_ltiversion === LTI_VERSION_1P3 && $deeplinkurlparts['host'] === $configtoolurlparts['host']) {
        $deeplink = new DeepLink();
        $deeplink->toolurl = $url;
        // The svc-lti service expects only path.
        $customparams["custom_auth_request_path"] = '/lib/editor/atto/plugins/echo360attoplugin/auth.php';
        if (isset($SESSION->lti_initiatelogin_status)) {
            unset($SESSION->lti_initiatelogin_status);
        }
        $customdeeplink = new CustomDeepLink();
        echo $customdeeplink->lti_initiate_login($course->id, $resourcelinkid, $deeplink, $config, 'basic-lti-launch-request', '',
            '', $deeplink->toolurl, $customparams);
        exit;
    }
}

// Generate LTI launch form and post details.
$formid = 'form-' . rand(1000, 9999);
// XSS Protection, only launch to the configured URL.
$url = $lticonfig['launch_url'] . '?' . parse_url($url, PHP_URL_QUERY);
echo html_writer::start_tag('html');
echo html_writer::start_tag('body');
echo html_writer::start_tag('form', array('id' => $formid, 'action' => $url, 'method' => 'post'));
foreach ($lticonfig as $key => $value) {
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $key, 'value' => $value));
}
echo html_writer::end_tag('form');
echo html_writer::tag('script', 'document.getElementById("' . $formid . '").submit();', null);
echo html_writer::end_tag('body');
echo html_writer::end_tag('html');
