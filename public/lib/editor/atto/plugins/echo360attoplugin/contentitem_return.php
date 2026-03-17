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
 * Handles the selected Echo360 content items returned to Moodle.
 *
 * @package   atto_echo360attoplugin
 * @copyright 2022 Echo360 Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(__FILE__) . '/../../../../../mod/lti/lib.php');
require_once(dirname(__FILE__) . '/../../../../../mod/lti/locallib.php');

$courseid = required_param('course', PARAM_INT);
$id = required_param('id', PARAM_INT);  // Is the toolid.
$sesskey = required_param('sesskey', PARAM_TEXT);
$callback = required_param('callback', PARAM_ALPHANUMEXT);

$jwt = $_POST['JWT'];

const ECHO360ATTOPLUGIN_NAME = 'atto_echo360attoplugin';
const ECHO360ATTOPLUGIN_VERSION = '2.0.13';

// Check access and capabilities.
$context = context_course::instance($courseid);
list($context, $course, $cm) = get_context_info_array($context->id);
require_login($course, false, $cm);
require_sesskey();

// Read Echo360 Atto Plugin optional LTI 1.3 settings.
$lti1p3configurationenabled = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationenabled');
// Selected Echo360 LTI 1.3 Configuration's deployment id.
$toolid = get_config(ECHO360ATTOPLUGIN_NAME, 'lti1p3configurationselection');

if ((!$lti1p3configurationenabled) || ($toolid != $id) || ($sesskey != sesskey())) {
    echo "Unauthorized Access";
    exit;
}

// Extract LtiDeepLinkingResponse JWT.
$contentitems = lti_convert_from_jwt($id, $jwt);
?>
<script type="text/javascript">
    parent.document.CALLBACKS.<?php echo $callback ?>(<?php echo json_encode($contentitems['content_items']); ?>);
</script>

