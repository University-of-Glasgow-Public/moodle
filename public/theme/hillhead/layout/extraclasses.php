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
 * @package    theme_hillhead
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$extrascripts = '';
$themehillheadstripstyles = '';
$themehillheadfont = get_user_preferences('theme_hillhead_font');

switch($themehillheadfont) {
    case 'modern':
        $extraclasses[] = 'hillhead-font-modern';
        break;
    case 'classic':
        $extraclasses[] = 'hillhead-font-classic';
        break;
    case 'comic':
        $extraclasses[] = 'hillhead-font-comic';
        break;
    case 'mono':
        $extraclasses[] = 'hillhead-font-mono';
        break;
    case 'dyslexic':
        $extraclasses[] = 'hillhead-font-dyslexic';
        break;
}

$themehillheadsize = get_user_preferences('theme_hillhead_size');

switch($themehillheadsize) {
    case '120':
        $extraclasses[] = 'hillhead-size-120';
        break;
    case '140':
        $extraclasses[] = 'hillhead-size-140';
        break;
    case '160':
        $extraclasses[] = 'hillhead-size-160';
        break;
    case '180':
        $extraclasses[] = 'hillhead-size-180';
        break;
}

$themehillheadcontrast = get_user_preferences('theme_hillhead_contrast');

switch($themehillheadcontrast) {
    case 'night':
        $extraclasses[] = 'hillhead-night';
        break;
    case 'by':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-by';
        break;
    case 'yb':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-yb';
        break;
    case 'wg':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-wg';
        break;
    case 'bb':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-bb';
        break;
    case 'br':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-br';
        break;
    case 'bw':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-bw';
        break;
    case 'wb':
        $extraclasses[] = 'hillhead-contrast';
        $extraclasses[] = 'hillhead-contrast-wb';
        break;
}

$themehillheadbold = get_user_preferences('theme_hillhead_bold');

switch($themehillheadbold) {
    case 'on':
        $extraclasses[] = 'hillhead-bold';
        break;
}

$themehillheadspacing = get_user_preferences('theme_hillhead_spacing');

switch($themehillheadspacing) {
    case 'on':
        $extraclasses[] = 'hillhead-spacing';
        break;
}

$themehillheadreadhighlight = get_user_preferences('theme_hillhead_readtome');

switch($themehillheadreadhighlight) {
    case 'on':
        $extrascripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead/js/readtome.js"></script>';
        break;
}

$themehillheadreadalert = get_user_preferences('theme_hillhead_readalert');

switch ($themehillheadreadalert) {
    case 'on':
        $extraclasses[] = 'hillhead-readalert';
        break;
}

if ($themehillheadstripstyles != 'on') {
    $themehillheadstripstyles = get_user_preferences('theme_hillhead_stripstyles');
}

switch ($themehillheadstripstyles) {
    case 'on':
        $extrascripts .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/theme/hillhead/js/stripstyles.js"></script>';
        $extraclasses[] = 'hillhead-stripstyles';
        break;
}
