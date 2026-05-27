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
 * External functions and service declaration for UofG Assessment Extract
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    local_ugassessment
 * @category   webservice
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$functions = [
    'local_ugassessment_get_data' => [
        'classname'   => 'local_ugassessment\\external\\get_data',
        'methodname'  => 'execute',
        'description' => 'Return course and assessment metadata (no user data)',
        'type'        => 'read',
        'ajax'        => false,
    ],
];

$services = [
    'Assessment metadata service' => [
        'functions' => [
            'local_ugassessment_get_data',
        ],
        'restrictedusers' => true,
        'enabled' => true,
        'shortname' => 'assessmentmeta',
    ],
];
