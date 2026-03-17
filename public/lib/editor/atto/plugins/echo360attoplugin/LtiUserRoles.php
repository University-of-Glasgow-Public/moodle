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
 * Defines the LTI User Roles.
 *
 * @package   atto_echo360attoplugin
 * @copyright 2022 Echo360 Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Echo360;

const LTI_ROLE_REQUEST_ADMINISTRATOR = 'urn:lti:sysrole:ims/lis/Administrator';
const LTI_ROLE_REQUEST_INSTRUCTOR = 'urn:lti:role:ims/lis/Instructor';
const LTI_ROLE_REQUEST_LEARNER = 'urn:lti:role:ims/lis/Learner';

const LTI_ADMIN = 'admin';
const LTI_ADMINISTRATOR = 'administrator';
const LTI_FACULTY = 'faculty';
const LIS_SYSTEM_ADMIN = 'urn:lti:sysrole:ims/lis/administrator';
const LIS_INSTITUTION_ADMIN = 'urn:lti:instrole:ims/lis/administrator';

const LTI_INSTRUCTOR = 'instructor';
const LTI_TEACHER = 'teacher';
const LTI_EDITING_TEACHER = 'editingteacher';
const LTI_NON_EDITING_TEACHER = 'non-editing teacher';
const LTI_COURSE_CREATOR = 'coursecreator';
const LTI_MANAGER = 'manager';
const LTI_MENTOR = 'urn:lti:role:ims/lis/mentor';
const LTI_CONTENT_DEVELOPER = 'urn:lti:role:ims/lis/contentdeveloper';
const LTI_TEACHING_ASSISTANT = 'urn:lti:role:ims/lis/teachingassistant';
const LTI_GRADER = "urn:lti:role:ims/lis/teachingassistant/grader";

const LTI_STUDENT = 'student';

const LTI_ADMIN_ROLES = array(
  LTI_ADMIN,
  LTI_ADMINISTRATOR,
  LIS_SYSTEM_ADMIN,
  LIS_INSTITUTION_ADMIN
);

const LTI_INSTRUCTOR_ROLES = array(
  LTI_FACULTY,
  LTI_INSTRUCTOR,
  LTI_TEACHER,
  LTI_EDITING_TEACHER,
  LTI_NON_EDITING_TEACHER,
  LTI_MANAGER,
  LTI_MENTOR,
  LTI_CONTENT_DEVELOPER,
  LTI_TEACHING_ASSISTANT,
  LTI_GRADER,
  LTI_COURSE_CREATOR
);

const LTI_STUDENT_ROLES = array(
  LTI_STUDENT
);
