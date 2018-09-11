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
 * Joule Grader mod_assign_submission gradingarea testcase.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_joulegrader\gradingarea\mod_assign_submissions;

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');

/**
 * Joule Grader mod_assign_submission gradingarea testcase.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_joulegrader_gradingarea_mod_assign_submissions_testcase extends advanced_testcase {
    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_hidden_gradeitem_grader() {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));
        $context = context_module::instance($assign->cmid);

        $teacher = $this->getDataGenerator()->create_user();
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $this->setUser($teacher);

        $courseinfo = get_fast_modinfo($course->id);
        $gradingmanager = get_grading_manager($context);

        $this->assertTrue(mod_assign_submissions::include_area($courseinfo, $gradingmanager));

        $gradeitemparams = [
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'iteminstance' => $assign->id,
            'courseid' => $course->id,
            'itemnumber' => 0,
        ];

        $gradeitem = \grade_item::fetch($gradeitemparams);
        $gradeitem->set_hidden(true);

        $this->assertTrue(mod_assign_submissions::include_area($courseinfo, $gradingmanager));
    }

    public function test_hidden_gradeitem_nongrader() {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', array('course' => $course->id));
        $context = context_module::instance($assign->cmid);

        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);

        $this->setUser($student);

        $courseinfo = get_fast_modinfo($course->id);
        $gradingmanager = get_grading_manager($context);

        $this->assertTrue(mod_assign_submissions::include_area($courseinfo, $gradingmanager));

        $gradeitemparams = [
            'itemtype' => 'mod',
            'itemmodule' => 'assign',
            'iteminstance' => $assign->id,
            'courseid' => $course->id,
            'itemnumber' => 0,
        ];

        $gradeitem = \grade_item::fetch($gradeitemparams);
        $gradeitem->set_hidden(true);

        $this->assertFalse(mod_assign_submissions::include_area($courseinfo, $gradingmanager));
    }

    public function test_get_submission_extension() {
        global $DB;

        $this->setAdminUser();

        // Setup and get the various course info.
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id,
                         'assignsubmission_onlinetext_enabled' => 1,
                         'duedate' => time() - 4 * 24 * 60 * 60);

        $assign = $this->getDataGenerator()->create_module('assign', $options);
        $context = context_module::instance($assign->cmid);

        // Get the testable assign object.
        $cm = get_coursemodule_from_instance('assign', $assign->id);
        $assign = new testable_assign($context, $cm, $course);

        $student1 = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $this->getDataGenerator()->enrol_user($student1->id, $course->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id);

        // Set an extension for user 2.
        $extendedtime = time() + 2 * 24 * 60 * 60;
        $assign->testable_save_user_extension($student2->id, $extendedtime);

        // Get the grade areas for the two users.
        $gradingmanager = get_grading_manager($context);
        $gradingarea1 = new mod_assign_submissions($gradingmanager, 0, $student1->id);
        $gradingarea2 = new mod_assign_submissions($gradingmanager, 0, $student2->id);

        // Check that the extension is correct without a submission.
        $extension = $gradingarea1->get_submission_extension();
        $this->assertEquals(0, $extension);
        $extension = $gradingarea2->get_submission_extension();
        $this->assertEquals($extendedtime, $extension);

        // Make a submission as each user.
        $this->setUser($student1);
        $submission = $assign->get_user_submission($student1->id, true);
        $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
        $assign->testable_update_submission($submission, $student1->id, true, false);
        $data = new stdClass();
        $data->onlinetext_editor = array('itemid'=>file_get_unused_draft_itemid(),
                                         'text'=>'Submission text',
                                         'format'=>FORMAT_MOODLE);
        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $plugin->save($submission, $data);

        $this->setUser($student2);
        $submission = $assign->get_user_submission($student2->id, true);
        $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
        $assign->testable_update_submission($submission, $student2->id, true, false);
        $data = new stdClass();
        $data->onlinetext_editor = array('itemid'=>file_get_unused_draft_itemid(),
                                         'text'=>'Submission text',
                                         'format'=>FORMAT_MOODLE);
        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $plugin->save($submission, $data);

        // Check that we still get the right extensions after submission.
        $gradingarea1 = new mod_assign_submissions($gradingmanager, 0, $student1->id);
        $gradingarea2 = new mod_assign_submissions($gradingmanager, 0, $student2->id);

        $extension = $gradingarea1->get_submission_extension();
        $this->assertEquals(0, $extension);
        $extension = $gradingarea2->get_submission_extension();
        $this->assertEquals($extendedtime, $extension);
    }
}
