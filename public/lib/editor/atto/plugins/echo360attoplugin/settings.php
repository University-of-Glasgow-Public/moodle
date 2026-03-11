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
 * Atto echo360attoplugin settings file.
 *
 * @package   atto_echo360attoplugin
 * @copyright 2020 Echo360 Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('editoratto', new admin_category('atto_echo360attoplugin', new lang_string('pluginname', 'atto_echo360attoplugin')));

$settings = new admin_settingpage('atto_echo360attoplugin_settings', new lang_string('settings', 'atto_echo360attoplugin'));
if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/lti/lib.php');
    require_once($CFG->dirroot . '/mod/lti/locallib.php');
    // LTI 1.3 Configuration Settings.
    $lti1p3configurationoptions = array();
    $lti1p3configurationoptions[0] = "";
    foreach (lti_get_lti_types() as $key => $val) {
        if ($val->ltiversion == "1.3.0" && stripos($val->tooldomain, "echo360") !== false) {
            $lti1p3configurationoptions[$key] = $val->name;
        }
    }
    $settings->add(
        new admin_setting_heading(
            'atto_echo360attoplugin/lti1p3configurationsettings',
            new lang_string('lti1p3configurationsettings', 'atto_echo360attoplugin'), ''
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'atto_echo360attoplugin/lti1p3configurationenabled',
            new lang_string('lti1p3configurationenabled', 'atto_echo360attoplugin'),
            new lang_string('lti1p3configurationenabled_desc', 'atto_echo360attoplugin'),
            0
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'atto_echo360attoplugin/lti1p3configurationselection',
            new lang_string('lti1p3configurationselection', 'atto_echo360attoplugin'),
            new lang_string('lti1p3configurationselection_desc', 'atto_echo360attoplugin'),
            0,
            $lti1p3configurationoptions
        )
    );
    // LTI 1.1 Configuration Settings.
    $settings->add(
        new admin_setting_heading(
            'atto_echo360attoplugin/lti1p1configurationsettings',
            new lang_string('lti1p1configurationsettings', 'atto_echo360attoplugin'), ''
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'atto_echo360attoplugin/consumerkey',
            get_string('consumerkey', 'atto_echo360attoplugin'), 'The Public Key provided by Echo360', '', PARAM_TEXT
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'atto_echo360attoplugin/sharedsecret',
            get_string('sharedsecret', 'atto_echo360attoplugin'), 'The Secret Key provided by Echo360', '', PARAM_TEXT
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'atto_echo360attoplugin/hosturl',
            get_string('hosturl', 'atto_echo360attoplugin'), 'The Host URL provided by Echo360', '', PARAM_TEXT
        )
    );
}
