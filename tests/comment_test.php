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
 * Joule Grader commenting tests.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Joule Grader commenting tests.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_joulegrader_comment_testcase extends advanced_testcase {

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

        $event = \local_joulegrader\event\comment_deleted::create(array(
            'other' => array(
                'areaid' => $gareaid
            ),
            'relateduserid' => $student->id,
            'context' => $context
        ));

        // If we have a string length that is less than 100 characters, the course id parameter should be present.
        $this->assertContains('courseid', $event->get_url()->out(false));
        $this->assertLessThan(100, \core_text::strlen($event->get_url()->out(false)));

        $garea->areaname = 'discussion';
        $gareaid2 = $DB->insert_record('grading_areas', $garea);
        // Increase the URL length.
        $CFG->wwwroot .= '_test';
        $url = new \moodle_url('/local/joulegrader/view.php?', array(
            'guser'    => $student->id,
            'garea'    => $gareaid2,
            'courseid' => $course->id,
        ));
        // A normal URL construction should be larger than 100 characters and blocks a log record insertion.
        $this->assertGreaterThan(100, \core_text::strlen($url->out(false)));

        $event2 = \local_joulegrader\event\comment_deleted::create(array(
            'other' => array(
                'areaid' => $gareaid2
            ),
            'relateduserid' => $student->id,
            'context' => $context
        ));
        // Validations kicks in and removes the courseid parameter, it is ok since we have the areaid.
        $this->assertNotContains('courseid', $event2->get_url()->out(false));
        $this->assertLessThan(100, \core_text::strlen($event2->get_url()->out(false)));

        $event3 = \local_joulegrader\event\comment_added::create(array(
            'other' => array(
                'areaid' => $gareaid2
            ),
            'relateduserid' => $student->id,
            'context' => $context
        ));
        $this->assertNotContains('courseid', $event3->get_url()->out(false));
        $this->assertLessThan(100, \core_text::strlen($event3->get_url()->out(false)));
    }
}