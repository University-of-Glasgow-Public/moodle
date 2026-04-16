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

namespace theme_hillhead51;

/**
 * This class is responsible for rendering the Hillhead theme scss files.
 *
 * @package    theme_hillhead51
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_hillhead51_get_main_scss_content {

    /**
     * @var string Contains the compiled css for output.
     */
    public $compiledcss = '';

    /**
     * @var array Contains a list of scss files that should be processed.
     */
    protected $sheets = ['config'];

    /**
     * @var string The file that will be used.
     */
    protected $filename = '';

    /**
     * Constructor.
     */
    public function __construct($theme) {
        global $CFG;

        // These scss files should declare default values for "variables" that will be used by Moodle...
        foreach ($this->sheets as $sheet) {
            $this->compiledcss .= file_get_contents($CFG->dirroot . '/theme/hillhead51/scss/'.$sheet.'.scss');
        }

        // ...now append the main scss file style rules...
        $this->compiledcss .= theme_boost_get_main_scss_content($theme);

        $this->sheets = ['hillhead51', 'accessibility', 'login'];

        // ...these scss files should declare more specific css "rules"...
        foreach ($this->sheets as $sheet) {
            $this->compiledcss .= file_get_contents($CFG->dirroot . '/theme/hillhead51/scss/'.$sheet.'.scss');
        }

        // ...finally append the "preset" scss "vars" and "rules" from the settings,
        // which will override the ones used in the Moodle and Bootstrap SCSS files...
        $this->filename = !empty($theme->settings->preset) ? $theme->settings->preset : 'blue.scss';
        $this->compiledcss .= file_get_contents($CFG->dirroot . '/theme/hillhead51/scss/'.$this->filename);

        return $this->compiledcss;
    }
}
