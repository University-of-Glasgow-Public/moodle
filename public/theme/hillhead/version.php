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
 * UofG version file for the plugin.
 *
 * @package    theme_hillhead
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026040100;
$plugin->requires  = 2026032700;         // Requires Moodle version 5.2 or greater.
$plugin->component = 'theme_hillhead';
$plugin->dependencies = [
    'theme_boost' => '2025100600',
];
$plugin->maturity = MATURITY_ALPHA;
$plugin->release  = '0.1 Alpha';
