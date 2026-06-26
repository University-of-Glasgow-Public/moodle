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
 * Settings for UG Assessment plugin.
 *
 * @package    local_ugassessment
 * @copyright  2026 Ferenc 'Frank' Fengyel, ferenc.lengyel@glasgow.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB, $ADMIN;

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_ugassessment',
        get_string('settingpagename', 'local_ugassessment')
    );

    $valuesoptions = [];
    $fieldoptions = [];

    if (!during_initial_install()) {
        /*
        * Step 1 Dropdown: select custom field (type = select)
        */
        $fields = $DB->get_records_sql("
            SELECT f.id, f.shortname, f.name
            FROM {customfield_field} f
            JOIN {customfield_category} c ON c.id = f.categoryid
            WHERE c.component = :component
            AND f.type = :type
            ORDER BY f.name ASC
        ", [
            'component' => 'core_course',
            'type'      => 'select',
        ]);

        foreach ($fields as $field) {
            // Store shortname, display readable name.
            $fieldoptions[$field->shortname] = $field->name;
        }

        if (empty($fieldoptions)) {
            $settings->add(new admin_setting_heading(
                'local_ugassessment/nofields',
                '',
                '<div class="alert alert-warning">
                    No course <strong>dropdown custom fields</strong> exist.<br>
                    Please create one first in:<br>
                    <em>Site administration → Courses → Custom fields</em>.
                </div>'
            ));
        } else {
            $settings->add(new admin_setting_configselect(
                'local_ugassessment/fieldshortname',
                get_string('fieldshortname', 'local_ugassessment'),
                get_string('fieldshortname_desc', 'local_ugassessment'),
                '',
                $fieldoptions
            ));
        }

        /*
        * Step 2 Dropdown: values for selected field
        */

        $selectedfield = get_config('local_ugassessment', 'fieldshortname');

        if (!empty($selectedfield)) {
            $field = $DB->get_record_sql("
                SELECT f.id, f.configdata
                FROM {customfield_field} f
                JOIN {customfield_category} c ON c.id = f.categoryid
                WHERE f.shortname = :shortname
                AND c.component = :component
            ", [
                'shortname' => $selectedfield,
                'component' => 'core_course',
            ]);

            if ($field && !empty($field->configdata)) {

                $config = json_decode($field->configdata);

                if (!empty($config->options)) {

                    // Options are newline separated.
                    $options = preg_split('/\r\n|\r|\n/', $config->options);

                    $options = array_map('trim', $options);
                    $options = array_values(array_filter($options));

                    foreach ($options as $index => $option) {
                        $valuesoptions[(string)$index + 1] = $option;
                    }
                }
            }
        }

        if (empty($valuesoptions)) {
            $settings->add(new admin_setting_heading(
                'local_ugassessment/novalues',
                '',
                '<div class="alert alert-warning">
                    No options found for the selected custom field. Please add some first.
                </div>'
            ));
        } else {
            // Expected value. Only courses with this value in the specified custom field will be included in the snapshot.
            $settings->add(new admin_setting_configselect(
                'local_ugassessment/fieldvalue',
                get_string('fieldvalue', 'local_ugassessment'),
                get_string('fieldvalue_desc', 'local_ugassessment'),
                '',
                $valuesoptions
            ));
        }
    }

    // Keyword for identifying summative grade categories.
    $settings->add(new admin_setting_configtext(
        'local_ugassessment/gradecategorykeyword',
        get_string('gradecategorykeyword', 'local_ugassessment'),
        get_string('gradecategorykeyword_desc', 'local_ugassessment'),
        'summative'
    ));

    // Should we ignore StudentMyGrades when building the snapshot and API responses?
    $settings->add(new admin_setting_configcheckbox(
        'local_ugassessment/ignorestudentmygrades',
        get_string('ignorestudentmygrades', 'local_ugassessment'),
        get_string('ignorestudentmygrades_desc', 'local_ugassessment'),
        0
    ));

    // Select which activity modules to include in the snapshot and API responses.
    $modoptions = [];

    foreach (core_component::get_plugin_list('mod') as $modname => $path) {

        if (plugin_supports('mod', $modname, FEATURE_GRADE_HAS_GRADE)) {

            $label = get_string('pluginname', 'mod_' . $modname);

            // Add clarification for duplicates. In practice, only H5P and HVP, but this makes it future-proof.
            if ($modname === 'hvp') {
                $label .= ' (plugin)';
            } else if ($modname === 'h5pactivity') {
                $label .= ' (core)';
            }

            $modoptions[$modname] = $label;
        }
    }

    asort($modoptions);

    $settings->add(new admin_setting_configmultiselect(
        'local_ugassessment/activitytypes',
        get_string('activitytypes', 'local_ugassessment'),
        get_string('activitytypes_desc', 'local_ugassessment'),
        ['assign', 'quiz'],
        $modoptions
    ));

    $settings->add(new admin_setting_configselect(
        'local_ugassessment/paginationlimit',
        get_string('paginationlimit', 'local_ugassessment'),
        get_string('paginationlimit_desc', 'local_ugassessment'),
        1000,
        [
            1000 => '1000',
            2500 => '2500',
            5000 => '5000',
            10000 => '10000',
        ]
    ));

    if (!$ADMIN->locate('local_ugassessment_root')) {
        $ADMIN->add('localplugins', new admin_category(
            'local_ugassessment_root',
            get_string('pluginname', 'local_ugassessment')
        ));
    }

    $ADMIN->add('local_ugassessment_root', $settings);

    $ADMIN->add('local_ugassessment_root', new admin_externalpage(
        'local_ugassessment_manage',
        get_string('managepagename', 'local_ugassessment'),
        new moodle_url('/local/ugassessment/manage.php'),
        'moodle/site:config'
    ));

    $ADMIN->add('local_ugassessment_root', new admin_externalpage(
        'local_ugassessment_logs',
        get_string('logs', 'local_ugassessment'),
        new moodle_url('/local/ugassessment/logs.php'),
        'moodle/site:config'
));
}
