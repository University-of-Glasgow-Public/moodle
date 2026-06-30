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
 * English language pack for UofG Assessment Extract
 *
 * @package    local_ugassessment
 * @category   string
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitytypes'] = 'Included activity types';
$string['activitytypes_desc'] = 'Select which activity types should be included in the snapshot. Use Ctrl+Click to select multiple activity types.';
$string['deletesnapshot'] = 'Delete snapshot';
$string['event_snapshot_rebuilt'] = 'UG Assessment snapshot rebuilt';
$string['event_snapshot_refreshed'] = 'UG Assessment snapshot refreshed';
$string['event_snapshot_reset'] = 'UG Assessment snapshot reset';
$string['exportsnapshot'] = 'Export snapshot as CSV';
$string['fieldshortname'] = 'Course custom field';
$string['fieldshortname_desc'] = 'Select which course custom field to filter by and save it.';
$string['fieldvalue'] = 'Field value to match';
$string['fieldvalue_desc'] = 'Only courses with this value will be included in the snapshot.';
$string['gradecategorykeyword'] = 'Grade category keyword';
$string['gradecategorykeyword_desc'] = 'Only activities whose level-2 grade category contains this keyword will be included (case-insensitive).';
$string['ignorestudentmygrades'] = 'Ignore StudentMyGrades';
$string['ignorestudentmygrades_desc'] = 'If enabled, courses will be included in the snapshot regardless of the value of the StudentMyGrades course custom field.';
$string['logs'] = 'Logs';
$string['managepagename'] = 'Manage snapshot';
$string['paginationlimit'] = 'Pagination limit';
$string['paginationlimit_desc'] = 'Maximum number of records to return per page in the API.';
$string['pluginname'] = 'UofG Assessment Extract';
$string['privacy:metadata'] = 'The UofG Assessment Extract plugin doesn\'t store any personal data.';
$string['rebuildconfirm'] = 'Are you sure you want to rebuild the snapshot? This will overwrite existing data.
 Do not use it on Production, the snapshot table is large and it will take a long time to complete. Use the Cron job instead.';
$string['rebuildingsnapshot'] = 'Rebuilding snapshot...';
$string['rebuildsnapshot'] = 'Rebuild snapshot';
$string['rebuildwarning'] = 'WARNING: Rebuilding will overwrite the existing snapshot table. Do not use it on Production, the snapshot table is large and it will take a long time to complete. Use the Cron job instead.';
$string['refreshconfirm'] = 'Are you sure you want to refresh the snapshot? This will apply changes to existing records and add new records, but it will not delete any records.
 If you want to do a full rebuild, please use the Rebuild Snapshot button instead.
 Do not use it on Production, the snapshot table is large and it will take a long time to complete. Use the Cron job instead.';
$string['refreshsnapshot'] = 'Refresh snapshot';
$string['refreshwarning'] = 'This will refresh the snapshot table with any changes to existing activities and add any new activities, but it will not delete any records.
 If you want to do a full rebuild, please use the Rebuild Snapshot button instead.
 Do not use it on Production, the snapshot table is large and it will take a long time to complete. Use the Cron job instead.';
$string['resetconfirm'] = 'Are you sure you want to continue? This will delete all records in the snapshot table. Only use this if you want to completely reset the snapshot and start over.';
$string['resetsnapshot'] = 'Reset snapshot';
$string['resetsuccess'] = 'Snapshot reset was successful.';
$string['resetwarning'] = 'WARNING: This will delete the snapshot table and you will lose all existing data. The Cron job will then rebuild the snapshot table from scratch.
 Be very careful when using this option on Production, as you will lose all the soft deleted records.';
$string['settingpagename'] = 'Extract settings';
$string['snapshotrebuildsuccess'] = 'Snapshot rebuild was successful.';
$string['snapshotrefreshsuccess'] = 'Snapshot refresh was successful.';
$string['task_updatesnapshot'] = 'Update UofG assessment snapshot';
$string['ugassessment:view'] = 'View the extract';
