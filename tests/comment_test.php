<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Joule Grader commenting tests.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2016 Open LMS (https://www.openlms.net)
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader;
use context_module;
use stdClass;
global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/comment/lib.php');

/**
 * Joule Grader commenting tests.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2016 Open LMS (https://www.openlms.net)
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment_test extends \advanced_testcase {

    public function setUp(): void {
        $this->resetAfterTest();
    }

    public function test_advforums_comment_message() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/hsuforum/lib.php');

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('hsuforum',
            ['course' => $course->id, 'gradetype' => 1, 'scale' => 100]);

        $context = context_module::instance($forum->cmid);

        $teacher = $this->getDataGenerator()->create_user();
        $teacher2 = $this->getDataGenerator()->create_user();
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($teacher2->id, $course->id, $teacherrole->id);

        $student = $this->getDataGenerator()->create_user();
        $student2 = $this->getDataGenerator()->create_user();
        $student3 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($student3->id, $course->id, $studentrole->id);

        // Comment from a teacher.
        $this->setUser($teacher);

        $options = new stdClass();
        $options->context = $context;
        $options->itemid = $student->id;
        $options->commentarea = 'userposts_comments';

        $comment = new stdClass();
        $comment->content = 'Hi, that was a good forum post.';
        $comment->userid = $teacher->id;
        $comment->contextid = $context->id;

        $sink = $this->redirectMessages();
        mod_hsuforum_comment_message($comment, $options);
        $messages = $sink->get_messages();
        $sink->close();

        // Only send to teachers (excluding sender) and student whom comment is sent.
        $this->assertCount(2, $messages);
        foreach ($messages as $message) {
            $this->assertNotEquals($student2->id, $message->useridto);
            $this->assertNotEquals($student3->id, $message->useridto);
        }

        // Comment from a student.
        $this->setUser($student);

        $options = new stdClass();
        $options->context = $context;
        $options->itemid = $teacher->id;
        $options->commentarea = 'userposts_comments';

        $comment = new stdClass();
        $comment->content = 'Thank you.';
        $comment->userid = $student->id;
        $comment->contextid = $context->id;

        $sink = $this->redirectMessages();
        mod_hsuforum_comment_message($comment, $options);
        $messages = $sink->get_messages();
        $sink->close();

        // Only send to teachers.
        $this->assertCount(2, $messages);
        foreach ($messages as $message) {
            $this->assertNotEquals($student2->id, $message->useridto);
            $this->assertNotEquals($student3->id, $message->useridto);
        }
    }

    public function test_event_url() {
        global $DB, $CFG;

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('hsuforum',
            ['course' => $course->id, 'gradetype' => 1, 'scale' => 100]);

        $context = context_module::instance($forum->cmid);

        $teacher = $this->getDataGenerator()->create_user();
        $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);

        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $teacherrole->id);

        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);

        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);

        $this->setUser($teacher);

        $garea = new stdClass();
        $garea->contextid = $context->id;
        $garea->component = 'mod_hsuforum';
        $garea->areaname = 'posts';
        $gareaid = $DB->insert_record('grading_areas', $garea);

        $event = \local_joulegrader\event\comment_deleted::create([
            'other' => [
                'areaid' => $gareaid,
            ],
            'relateduserid' => $student->id,
            'context' => $context,
        ]);

        // If we have a string length that is less than 100 characters, the course id parameter should be present.
        $this->assertStringContainsString('courseid', $event->get_url()->out(false));
        $this->assertLessThan(100, \core_text::strlen($event->get_url()->out(false)));

        $garea->areaname = 'discussion';
        $gareaid2 = $DB->insert_record('grading_areas', $garea);

        /*
         * Ensure the URL length is at least 101 characters:
         * - Default URL length (excluding parameter values): 81.
         *   I.e. https://www.example.com/moodle/local/joulegrader/view.php?guser=&garea=&courseid=
         * - Additional characters needed: 20.
         */
        $numofcharsfromparams = strlen($student->id . $gareaid2 . $course->id);
        if ($numofcharsfromparams < 20) {
            $CFG->wwwroot .= str_repeat('_', 20 - $numofcharsfromparams);
        }

        $url = new \core\url('/local/joulegrader/view.php?', [
            'guser'    => $student->id,
            'garea'    => $gareaid2,
            'courseid' => $course->id,
        ]);
        // A normal URL construction should be larger than 100 characters and blocks a log record insertion.
        $this->assertGreaterThan(100, \core_text::strlen($url->out(false)));

        $event2 = \local_joulegrader\event\comment_deleted::create([
            'other' => [
                'areaid' => $gareaid2,
            ],
            'relateduserid' => $student->id,
            'context' => $context,
        ]);
        // Validations kicks in and removes the courseid parameter, it is ok since we have the areaid.
        $this->assertStringNotContainsString('courseid', $event2->get_url()->out(false));
        $this->assertLessThan(100, \core_text::strlen($event2->get_url()->out(false)));

        $event3 = \local_joulegrader\event\comment_added::create([
            'other' => [
                'areaid' => $gareaid2,
            ],
            'relateduserid' => $student->id,
            'context' => $context,
        ]);
        $this->assertStringNotContainsString('courseid', $event3->get_url()->out(false));
        $this->assertLessThan(100, \core_text::strlen($event3->get_url()->out(false)));
    }

    public function test_assignsubmission_comment() {
        global $DB, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $CFG->messaging = true; // Enable messaging system.

        // Create a course.
        $course = $this->getDataGenerator()->create_course();

        // Create and enrol two teachers.
        $teacher1 = $this->getDataGenerator()->create_user();
        $teacher2 = $this->getDataGenerator()->create_user();
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->getDataGenerator()->enrol_user($teacher1->id, $course->id, $teacherrole->id);
        $this->getDataGenerator()->enrol_user($teacher2->id, $course->id, $teacherrole->id);

        // Create and enrol a student.
        $student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);

        // Create an Assignment with Online Text Submission type.
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $params = [
            'course' => $course->id,
            'assignsubmission_onlinetext_enabled' => 1,
        ];
        $assignment = $generator->create_instance($params);

        // Create a Submission from the student.
        $this->setUser($student);
        $cm = get_coursemodule_from_instance('assign', $assignment->id);
        $contextmodule = context_module::instance($assignment->cmid);
        $assign = new \assign($contextmodule, $cm, $course);
        $submission = $assign->get_user_submission($student->id, true);

        // Add online text to the submission.
        $data = new stdClass();
        $data->onlinetext_editor = [
            'text' => 'This is my submission text',
            'format' => FORMAT_HTML,
        ];
        $plugin = $assign->get_submission_plugin_by_type('onlinetext');
        $plugin->save($submission, $data);

        // Test 1: All teachers are active - they should receive email notifications.
        $commentoptions = new stdClass();
        $commentoptions->context = $contextmodule;
        $commentoptions->course = $course;
        $commentoptions->area = 'submission_comments';
        $commentoptions->itemid = $submission->id;
        $commentoptions->component = 'assignsubmission_comments';
        $newcomment = new \comment($commentoptions);
        $newcomment->set_post_permission(true);

        $sink = $this->redirectMessages();
        $commenttext = 'This is my first comment as a student';
        $newcomment->add($commenttext);
        $messages = $sink->get_messages();
        $sink->close();

        $this->assertCount(2, $messages,
            'Two email messages should be sent as all teachers are active in the course');
        $this->assertEquals($teacher1->id, $messages[0]->useridto);
        $this->assertEquals($teacher2->id, $messages[1]->useridto);
        $this->assertEquals($commenttext, $messages[0]->fullmessage);
        $this->assertEquals($commenttext, $messages[1]->fullmessage);

        // Test 2: Teacher1 is suspended - he should NOT receive the email notification.
        // Now suspend the teacher1 from the course.
        $enrol = $DB->get_record('enrol', [
            'courseid' => $course->id,
            'enrol' => 'manual'
        ], '*', MUST_EXIST);

        $userenrolment = $DB->get_record('user_enrolments', [
            'enrolid' => $enrol->id,
            'userid' => $teacher1->id
        ], '*', MUST_EXIST);

        $userenrolment->status = ENROL_USER_SUSPENDED;
        $DB->update_record('user_enrolments', $userenrolment);

        // Verify the teacher1 is actually suspended.
        $contextcourse = \context_course::instance($course->id);
        $this->assertFalse(is_enrolled($contextcourse, $teacher1->id, '', true),
            'Teacher1 should be suspended from the course');

        $commentoptions = new stdClass();
        $commentoptions->context = $contextmodule;
        $commentoptions->course = $course;
        $commentoptions->area = 'submission_comments';
        $commentoptions->itemid = $submission->id;
        $commentoptions->component = 'assignsubmission_comments';
        $newcomment = new \comment($commentoptions);
        $newcomment->set_post_permission(true);

        $sink = $this->redirectMessages();
        $commenttext = 'This is my second comment after teacher1 suspension';
        $newcomment->add($commenttext);
        $messages = $sink->get_messages();
        $sink->close();
        $this->assertCount(1, $messages,
            'Only one message should be sent to teacher2 as teacher1 is suspended from the course');
        $this->assertEquals($teacher2->id, $messages[0]->useridto);
        $this->assertEquals($commenttext, $messages[0]->fullmessage);

        // Test 3: All teachers are suspended - no email notifications should be sent.
        // Now suspend the teacher2 from the course.
        $enrol = $DB->get_record('enrol', [
            'courseid' => $course->id,
            'enrol' => 'manual'
        ], '*', MUST_EXIST);

        $userenrolment = $DB->get_record('user_enrolments', [
            'enrolid' => $enrol->id,
            'userid' => $teacher2->id
        ], '*', MUST_EXIST);

        $userenrolment->status = ENROL_USER_SUSPENDED;
        $DB->update_record('user_enrolments', $userenrolment);

        // Verify the teacher2 is actually suspended.
        $contextcourse = \context_course::instance($course->id);
        $this->assertFalse(is_enrolled($contextcourse, $teacher2->id, '', true),
            'Teacher2 should be suspended from the course');

        $commentoptions = new stdClass();
        $commentoptions->context = $contextmodule;
        $commentoptions->course = $course;
        $commentoptions->area = 'submission_comments';
        $commentoptions->itemid = $submission->id;
        $commentoptions->component = 'assignsubmission_comments';
        $newcomment = new \comment($commentoptions);
        $newcomment->set_post_permission(true);

        $sink = $this->redirectMessages();
        $commenttext = 'This is my third comment after all teacher are suspended';
        $newcomment->add($commenttext);
        $messages = $sink->get_messages();
        $sink->close();
        $this->assertCount(0, $messages,
            'No messages should be sent when all teachers are suspended from the course');
    }
}
