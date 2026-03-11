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
 * Handle sending a user to a tool provider to initiate a content-item selection.
 *
 * @package   atto_echo360attoplugin
 * @copyright 2022 Echo360 Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Echo360;

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(__FILE__) . '/../../../../../mod/lti/lib.php');
require_once(dirname(__FILE__) . '/../../../../../mod/lti/locallib.php');

global $SESSION;

$contextcourseid = required_param('contextcourseid', PARAM_INT);    // The LTI ContextId.
$pagetype = required_param('pagetype', PARAM_TEXT);
$sesskey = required_param('sesskey', PARAM_TEXT);
$callback = required_param('callback', PARAM_ALPHANUMEXT);
$title = "Echo360 Embed Media";
$text = "Echo360 Embed Media";

const ECHO360ATTOPLUGIN_NAME = 'atto_echo360attoplugin';
const ECHO360ATTOPLUGIN_VERSION = '2.0.13';

// Check access and capabilities.
list($context, $course, $cm) = get_context_info_array($contextcourseid);
require_login($course, false, $cm);
require_sesskey();
require_capability('moodle/course:manageactivities', $context);
require_capability('mod/lti:addcoursetool', $context);

// Read Echo360 Atto Plugin optional LTI 1.3 settings.
$lti1p3configurationenabled = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationenabled');
// Selected Echo360 LTI 1.3 Configuration's deployment id.
$toolid = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationselection');

if ((!$lti1p3configurationenabled) || ($toolid == null || $toolid == 0) || ($sesskey != sesskey())) {
    echo "Unauthorized Access";
    exit;
}

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
    public function lti_initiate_login($courseid, $id, $instance, $config, $messagetype = 'basic-lti-launch-request', $title = '',
            $text = '', $customparams = []) {
        global $SESSION;

        $stdparams = lti_build_login_request($courseid, $id, $instance, $config, $messagetype);
        $params = array_merge($stdparams, $customparams);
        $SESSION->lti_message_hint = "{$courseid},{$config->typeid},{$id}," . base64_encode($title) . ',' .
            base64_encode($text);

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
$customdeeplink = new CustomDeepLink();

// Set the return URL. We send the launch container along to help us avoid frames-within-frames when the user returns.
$returnurlparams = [
    'course' => $course->id,
    'id' => $toolid,
    'sesskey' => sesskey(),
    'callback' => $callback
];
$returnurl = parse_url(new \moodle_url('/lib/editor/atto/plugins/echo360attoplugin/contentitem_return.php', $returnurlparams));
// The svc-lti expects only path and query.
$customparams["custom_deep_link_return_path"] = $returnurl['path'] . '?' . $returnurl['query'];

$config = lti_get_type_type_config($toolid);    // Retrieve LTI 1.3 external tool configuration.

if ($config->lti_ltiversion === LTI_VERSION_1P3) {
    if (!isset($SESSION->lti_initiatelogin_status)) {
        echo $customdeeplink->lti_initiate_login($course->id, 0, $customdeeplink, $config, 'ContentItemSelectionRequest',
            $title, $text, $customparams);
        exit;
    } else {
        unset($SESSION->lti_initiatelogin_status);
    }
}

// Prepare the request.
$request = lti_build_content_item_selection_request($toolid, $course, $returnurl, $title, $text, [], []);

// Get the launch HTML.
echo lti_post_launch_html($request->params, $request->url, false);

