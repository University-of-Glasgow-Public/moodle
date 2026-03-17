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
 * Additional accessibility classes.
 *
 * The size and fonts get applied to the page based on which one is selected.
 *
 * @package    theme_hillhead51
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$extrascripts = '';
$themehillhead51stripstyles = '';
$themehillhead51font = get_user_preferences('theme_hillhead51_font');

switch($themehillhead51font) {
    case 'modern':
        $extraclasses[] = 'hillhead51-font-modern';
        break;
    case 'classic':
        $extraclasses[] = 'hillhead51-font-classic';
        break;
    case 'comic':
        $extraclasses[] = 'hillhead51-font-comic';
        break;
    case 'mono':
        $extraclasses[] = 'hillhead51-font-mono';
        break;
    case 'dyslexic':
        $extraclasses[] = 'hillhead51-font-dyslexic';
        break;
}

$themehillhead51size = get_user_preferences('theme_hillhead51_size');

switch($themehillhead51size) {
    case '120':
        $extraclasses[] = 'hillhead51-size-120';
        break;
    case '140':
        $extraclasses[] = 'hillhead51-size-140';
        break;
    case '160':
        $extraclasses[] = 'hillhead51-size-160';
        break;
    case '180':
        $extraclasses[] = 'hillhead51-size-180';
        break;
}

$themehillhead51contrast = get_user_preferences('theme_hillhead51_contrast');

switch($themehillhead51contrast) {
    case 'night':
        $extraclasses[] = 'hillhead51-night';
        break;
    case 'by':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-by';
        break;
    case 'yb':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-yb';
        break;
    case 'wg':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-wg';
        break;
    case 'bb':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-bb';
        break;
    case 'br':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-br';
        break;
    case 'bw':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-bw';
        break;
    case 'wb':
        $extraclasses[] = 'hillhead51-contrast';
        $extraclasses[] = 'hillhead51-contrast-wb';
        break;
}

$themehillhead51bold = get_user_preferences('theme_hillhead51_bold');

switch($themehillhead51bold) {
    case 'on':
        $extraclasses[] = 'hillhead51-bold';
        break;
}

$themehillhead51spacing = get_user_preferences('theme_hillhead51_spacing');

switch($themehillhead51spacing) {
    case 'on':
        $extraclasses[] = 'hillhead51-spacing';
        break;
}

$themehillhead51readhighlight = get_user_preferences('theme_hillhead51_readtome');

switch($themehillhead51readhighlight) {
    case 'on':
        $extrascripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead51/js/readtome.js"></script>';
        break;
}

$themehillhead51readalert = get_user_preferences('theme_hillhead51_readalert');

switch ($themehillhead51readalert) {
    case 'on':
        $extraclasses[] = 'hillhead51-readalert';
        break;
}

if ($themehillhead51stripstyles != 'on') {
    $themehillhead51stripstyles = get_user_preferences('theme_hillhead51_stripstyles');
}

switch ($themehillhead51stripstyles) {
    case 'on':
        $extrascripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead51/js/stripstyles.js"></script>';
        $extraclasses[] = 'hillhead51-stripstyles';
        break;
}
