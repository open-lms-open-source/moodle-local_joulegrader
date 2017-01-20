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

    public function setUp() {
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
}