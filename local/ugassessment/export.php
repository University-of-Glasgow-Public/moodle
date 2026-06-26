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
 * Export the snapshot table as Excel compatible CSV.
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

use csv_export_writer;

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

global $DB;

// Create CSV writer.
$csv = new csv_export_writer('comma', '"', 'application/download', true);
$csv->set_filename('ugassessment_snapshot');

// Fetch records.
$records = $DB->get_records('local_ugassessment_snapshot');

// Add header row.
if (!empty($records)) {
    $first = reset($records);
    $csv->add_data(array_keys((array)$first));
}

// Add data rows.
foreach ($records as $record) {
    $csv->add_data((array)$record);
}

// Output file and exit.
$csv->download_file();
