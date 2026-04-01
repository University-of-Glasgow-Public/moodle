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
 * End users have asked that course indexes be collapsed by default.
 * Because of the mechanisms involved, this script accepts an argument
 * from the drawers.mustache template that determines if the course
 * indexes should be collapsed initially, or expanded by what has been
 * stored in mdl_user_preferences and local storage.
 *
 * @module     theme_hillhead/courseindex
 * @author     Greg Pedder <greg.pedder@glasgow.ac.uk>
 * @copyright  2026 University of Glasgow
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const Selectors = {
    COURSE_INDEX: '[aria-controls^="courseindexcollapse"]:not([aria-controls="courseindexcollapse0"])'
};

const CourseIndex = (coursindexcollapsed) => {
    let params = (new URL(location.href)).searchParams;
    let courseindexcollapsed = coursindexcollapsed;

    if (params.get('id') > 1 && courseindexcollapsed) {
        waitForElement(Selectors.COURSE_INDEX);
    }

    return;
};

/**
 * @param {HTMLElement} selector
 * @param {int} timeout
 * @returns {Promise<*|null>}
 */
async function waitForElement(selector, timeout = 15000) {
    const start = Date.now();

    while (Date.now() - start < timeout) {
        const el = document.querySelector(selector);
        if (el) {
            return setCourseIndexState(selector);
        }
        await new Promise(resolve => setTimeout(resolve, 1000));
    }

    return null;
}

/**
 * This function collapses the course indexes by default. This is only ever
 * called if the user hasn't visited this course page before. Otherwise, we
 * let Moodle's JavaScript take care of things.
 * @param {HTMLElement} element
*/
const setCourseIndexState = (element) => {

    if (document.querySelectorAll(element).length == 0) {
        return false;
    }

    const courseIndexSectionItems = document.querySelectorAll(Selectors.COURSE_INDEX);
    courseIndexSectionItems.forEach((indexSectionItem) => {
        indexSectionItem.click();
    });
};

export const init = (coursindexcollapsed) => {
    CourseIndex(coursindexcollapsed);
};