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
 * Joule Grader mod_hsuforum_posts gradingarea testcase.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_joulegrader\gradingarea\mod_hsuforum_posts;

/**
 * Joule Grader mod_hsuforum_posts gradingarea testcase.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_joulegrader_gradingarea_mod_hsuforum_posts_test extends advanced_testcase {
    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_hidden_gradeitem_grader() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('hsuforum',
                ['course' => $course->id, 'gradetype' => 1, 'scale' => 100]);
        
        $context = context_module::instance($forum->cmid);

        $teacher = $this->getDataGenerator()->create_user();
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $this->setUser($teacher);

        $courseinfo = get_fast_modinfo($course->id);
        $gradingmanager = get_grading_manager($context);

        $this->assertTrue(mod_hsuforum_posts::include_area($courseinfo, $gradingmanager));

        $gradeitemparams = [
            'itemtype' => 'mod',
            'itemmodule' => 'hsuforum',
            'iteminstance' => $forum->id,
            'courseid' => $course->id,
            'itemnumber' => 0,
        ];

        $gradeitem = \grade_item::fetch($gradeitemparams);
        $gradeitem->set_hidden(true);

        $this->assertTrue(mod_hsuforum_posts::include_area($courseinfo, $gradingmanager));
    }

    public function test_hidden_gradeitem_nongrader() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('hsuforum',
            ['course' => $course->id, 'gradetype' => 1, 'scale' => 100]);

        $context = context_module::instance($forum->cmid);

        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);

        $this->setUser($student);

        $courseinfo = get_fast_modinfo($course->id);
        $gradingmanager = get_grading_manager($context);

        $this->assertTrue(mod_hsuforum_posts::include_area($courseinfo, $gradingmanager));

        $gradeitemparams = [
            'itemtype' => 'mod',
            'itemmodule' => 'hsuforum',
            'iteminstance' => $forum->id,
            'courseid' => $course->id,
            'itemnumber' => 0,
        ];

        $gradeitem = \grade_item::fetch($gradeitemparams);
        $gradeitem->set_hidden(true);

        $this->assertFalse(mod_hsuforum_posts::include_area($courseinfo, $gradingmanager));
    }
}
