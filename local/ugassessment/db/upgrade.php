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
 * Upgrade steps for UofG Assessment Extract
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    local_ugassessment
 * @category   upgrade
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_ugassessment_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026052701) {

        $table = new xmldb_table('local_ugassessment_snapshot');

        // New field: coursecode.
        $coursecode = new xmldb_field('coursecode', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'coursefullname');
        if (!$dbman->field_exists($table, $coursecode)) {
            $dbman->add_field($table, $coursecode);
        }

        // New field: coursesubject.
        $coursesubject = new xmldb_field('coursesubject', XMLDB_TYPE_CHAR, '15', null, null, null, null, 'coursecode');
        if (!$dbman->field_exists($table, $coursesubject)) {
            $dbman->add_field($table, $coursesubject);
        }

        // New field: timelimit (bigint).
        $timelimit = new xmldb_field('timelimit', XMLDB_TYPE_INTEGER, '19', null, null, null, 0, 'timecloseordue');
        if (!$dbman->field_exists($table, $timelimit)) {
            $dbman->add_field($table, $timelimit);
        }

        upgrade_plugin_savepoint(true, 2026052701, 'local', 'ugassessment');
    }

    if ($oldversion < 2026052801) {

        $table = new xmldb_table('local_ugassessment_snapshot');

        // New field: timestartorfrom.
        $timestartorfrom = new xmldb_field('timeopenorfrom', XMLDB_TYPE_INTEGER, '19', null, null, null, 0, 'activityvisible');
        if (!$dbman->field_exists($table, $timestartorfrom)) {
            $dbman->add_field($table, $timestartorfrom);
        }

        upgrade_plugin_savepoint(true, 2026052801, 'local', 'ugassessment');
    }

    if ($oldversion < 2026052900) {

        $table = new xmldb_table('local_ugassessment_snapshot');

        // Update time fields with default values.

        $timecloseordue = new xmldb_field('timecloseordue', XMLDB_TYPE_INTEGER, '19', null, null, null, 0, 'timeopenorfrom');
        if (!$dbman->field_exists($table, $timecloseordue)) {
                $dbman->change_field_default($table, $timecloseordue);
        }

        upgrade_plugin_savepoint(true, 2026052900, 'local', 'ugassessment');
    }

    return true;
}
