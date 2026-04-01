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
 * Echo360 filter phpunit tests
 *
 * @package    filter_echo360tiny
 * @category   test
 * @copyright  2023 Echo360 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_echo360tiny;

use filter_echo360tiny;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/echo360tiny/filter.php'); // Include the code to test.

/**
 * Echo360 filter testcase.
 * @package filter_echo360tiny
 */
class filter_test extends \advanced_testcase {

    /**
     * Tests the filter doesn't affect text when no echo360 links are present.
     *
     * @dataProvider filter_echo360tiny_provider
     * @covers \filter_echo360tiny
     */
    public function test_filter_echo360tiny($input, $expected) {
        global $PAGE;
        $this->resetAfterTest(true);

        // Create a test course.
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $filteredtext = (new testable_filter_echo360tiny($context))->filter($input, [
            'originalformat' => FORMAT_HTML,
        ]);

        $this->assertEquals($expected, $filteredtext);
    }

    /**
     * The data provider for filter echo360 tests.
     *
     * @return array
     */
    public function filter_echo360tiny_provider() {
        global $CFG;

        $text = '<p>Demo text with <a href="'.$CFG->wwwroot.'">link</a> and special chars '.
                   '~!@#$%^&*()_+-={}[]\|/<>,.;:"`?'."'". // phpcs:ignore
                   'àáâäæãåāÀÁÂÄÆÃÅĀ'.
                   'èéêëēėęÈÉÊËĒĖĘ'.
                   'îïíīįìÎÏÍĪĮÌ'.
                   'ôöòóœøōõÔÖÒÓŒØŌÕ'.
                   'ûüùúūÛÜÙÚŪ'.
                   'ñńÑŃ'.
                   'ß∂ƒ©®∑`´¥¨ˆπ“”‘’«˙∆˚¬…Ω≈ç√∫˜µ≤≥¯˘™£¢∞§¶•ªº–≠¡⁄€‹›ﬁ‡°·‚—±∏„´¸'. // phpcs:ignore
                   'abc%7E%21%40%23%24%25%5E%26*%28%29_%2B-%60%7B%7D%5B%5D%5C%7C%3A%3B%27%3C%3E%2C.%3F%2F'.
                '</p>';

        $link = '<p>'.
                  '<a href="'.$CFG->wwwroot.
                    '/filter/echo360tiny/lti_launch.php'.
                    '?url=https%3A%2F%2Flocalhost%2Flti%2F68c9110d-3523-4a16-ab20-10c34354cd6e'.
                    '%3FmediaId%3Dbb7187e6-15a2-44b7-8243-26fe81a525f3'.
                    '%26autoplay%3Dfalse'.
                    '%26automute%3Dfalse'.
                    '&amp;cmid=124" target="_blank">'.
                    'abc%7E%21%40%23%24%25%5E%26*%28%29_%2B-%60%7B%7D%5B%5D%5C%7C%3A%3B%27%3C%3E%2C.%3F%2F'.
                  '</a>'.
                '</p>';

        $embed = '<p>'.
                   '<a href="'.$CFG->wwwroot.
                     '/filter/echo360tiny/lti_launch.php'.
                     '?url=https%3A%2F%2Flocalhost%2Flti%2F68c9110d-3523-4a16-ab20-10c34354cd6e'.
                     '%3FmediaId%3D173f7150-7455-431c-8e99-9c85d7b3b09c'.
                     '%26autoplay%3Dfalse'.
                     '%26automute%3Dfalse'.
                     '&amp;cmid=0'.
                     '&amp;width=640'.
                     '&amp;height=360" target="_blank">'.
                     'abc%7E%21%40%23%24%25%5E%26*%28%29_%2B-%60%7B%7D%5B%5D%5C%7C%3A%3B%27%3C%3E%2C.%3F%2F'.
                   '</a>'.
                 '</p>';

        $filteredembed = '<p>'.
                            '<div class="echo360-iframe">'.
                              '<iframe src="'.$CFG->wwwroot.
                                 '/filter/echo360tiny/lti_launch.php'.
                                 '?url=https%3A%2F%2Flocalhost%2Flti%2F68c9110d-3523-4a16-ab20-10c34354cd6e'.
                                 '%3FmediaId%3D173f7150-7455-431c-8e99-9c85d7b3b09c'.
                                 '%26autoplay%3Dfalse'.
                                 '%26automute%3Dfalse'.
                                 '&cmid=0'.
                                 '&width=640'.
                                 '&height=360" '.
                                 'width="640" '.
                                 'height="360" '.
                                 'frameborder="0" '.
                                 'allowfullscreen="allowfullscreen" '.
                                 'webkitallowfullscreen="webkitallowfullscreen" '.
                                 'loading="lazy" '.
                                 'mozallowfullscreen="mozallowfullscreen">'.
                              '</iframe>'.
                            '</div>'.
                          '</p>';

        return [
            'Texts with no echo360 links/embed are not filtered' => [
                'input'    => $text,
                'expected' => $text,
            ],
            'One link' => [
                'input'    => $link,
                'expected' => $link,
            ],
            'One embed' => [
                'input'    => $embed,
                'expected' => $filteredembed,
            ],
            'Multiple links and embeds' => [
                'input'    => $link.$embed.$link.$embed.$link,
                'expected' => $link.$filteredembed.$link.$filteredembed.$link,
            ],
            'Text and links' => [
                'input'    => $text.$link.$text,
                'expected' => $text.$link.$text,
            ],
            'Text and embeds' => [
                'input'    => $text.$embed.$text,
                'expected' => $text.$filteredembed.$text,
            ],
            'Text, links and embeds' => [
                'input'    => $text.$link.$embed.$text.$embed.$link,
                'expected' => $text.$link.$filteredembed.$text.$filteredembed.$link,
            ],
            'Text, links and embeds v2' => [
                'input'    => $link.$text.$embed.$text.$embed.$link,
                'expected' => $link.$text.$filteredembed.$text.$filteredembed.$link,
            ],
            'Text, links and embeds v3' => [
                'input'    => $embed.$text.$embed.$text.$link,
                'expected' => $filteredembed.$text.$filteredembed.$text.$link,
            ],
        ];
    }

}

/**
 * Subclass for easier testing.
 */
class testable_filter_echo360tiny extends filter_echo360tiny {
    public function __construct($context) {
        // Use this context for filtering.
        $this->context = $context;
        // Define FORMAT_HTML as only one filtering in DB.
        set_config('formats', implode(',', [FORMAT_HTML]), 'filter_echo360tiny');
    }
}
