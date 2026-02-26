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
 * Links for the footer template.
 *
 * Provide a list of links that need to appear in the footer template.
 *
 * @package    theme_hillhead51
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Not massively happy at having to do this.
global $DB;

$footerlinks = [
    'University Website' => 'https://www.gla.ac.uk',
    'Accessibility' => 'https://www.gla.ac.uk/legal/accessibility/statements/moodle',
    'Privacy' => new moodle_url('/local/guprivacy/privacy.php'),
    'Cookies' => new moodle_url('/local/guprivacy/cookies.php'),
];

$footerlinktext = '';

foreach ($footerlinks as $name => $link) {
    $footerlinktext .= '<li><a href="'.$link.'">'.$name.'</a></li>';
}

if (isloggedin()) {
    $footerlinktext .= '<li class="tool_usertours-resettourcontainer"></li>';
}

$roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
$isteacheranywhere = $DB->record_exists('role_assignments', ['userid' => $USER->id, 'roleid' => $roleid]);

if ($isteacheranywhere || is_siteadmin()) {
    $helplink = page_doc_link('Help with this page');
    if (!empty($helplink)) {
        $footerlinktext .= '<li>' . $helplink . '</li>';

    }
}