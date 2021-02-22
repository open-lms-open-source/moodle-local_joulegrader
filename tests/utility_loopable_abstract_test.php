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
 * Joule Grader utility testcase.
 *
 * @package    local_joulegrader
 * @author     Oscar Nadjar <oscar.nadjar@blackboard.com>
 * @copyright  Copyright (c) 2019 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_joulegrader\utility;

global $CFG;
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');

/**
 * Joule Grader find previous and next ID's.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2019 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_joulegrader_untility_testcase extends advanced_testcase {
    public function setUp(): void {
        $this->resetAfterTest();
    }

    public function test_find_previous_and_next() {

        $course = $this->getDataGenerator()->create_course();

        $mods = $this->getDataGenerator()->create_module('assign', array('course' => $course, 'section' => 1));

        $users = [];
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'student',['firstname' => 'user1']);
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'student',['firstname' => 'user2']);
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'student',['firstname' => 'user3']);
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'student',['firstname' => 'user4']);
        $users[] = $this->getDataGenerator()->create_and_enrol($course, 'student',['firstname' => 'user5']);
        $context = context_course::instance($course->id);
        $groupsutility = new local_joulegrader\utility\groups($context);
        $groupsutility->load_items();
        $gradeareaid = local_joulegrader_area_from_context($context, $mods->name);
        $gareasutility = new local_joulegrader\utility\gradingareas($context, $gradeareaid, 0, $groupsutility);
        $gareasutility->load_items();

        $usersutility = new local_joulegrader\utility\users($gareasutility, $context, (int)$users[0]->id, $groupsutility);
        $usersutility->load_items();
        $usersutility->set_currentuser((int)$users[0]->id);
        $this->assertEquals($usersutility->get_current(), (int)$users[0]->id);
        $this->assertEquals($usersutility->get_previous(), (int)$users[4]->id);
        $this->assertEquals($usersutility->get_next(), (int)$users[1]->id);

        $usersutility = new local_joulegrader\utility\users($gareasutility, $context, (int)$users[1]->id, $groupsutility);
        $usersutility->load_items();
        $usersutility->set_currentuser((int)$users[1]->id);
        $this->assertEquals($usersutility->get_current(), (int)$users[1]->id);
        $this->assertEquals($usersutility->get_previous(), (int)$users[0]->id);
        $this->assertEquals($usersutility->get_next(), (int)$users[2]->id);

        $usersutility = new local_joulegrader\utility\users($gareasutility, $context, (int)$users[0]->id, $groupsutility);
        $usersutility->load_items();
        $usersutility->set_currentuser((int)$users[2]->id);
        $this->assertEquals($usersutility->get_current(), (int)$users[2]->id);
        $this->assertEquals($usersutility->get_previous(), (int)$users[1]->id);
        $this->assertEquals($usersutility->get_next(), (int)$users[3]->id);

        $usersutility = new local_joulegrader\utility\users($gareasutility, $context, (int)$users[1]->id, $groupsutility);
        $usersutility->load_items();
        $usersutility->set_currentuser((int)$users[3]->id);
        $this->assertEquals($usersutility->get_current(), (int)$users[3]->id);
        $this->assertEquals($usersutility->get_previous(), (int)$users[2]->id);
        $this->assertEquals($usersutility->get_next(), (int)$users[4]->id);

        $usersutility = new local_joulegrader\utility\users($gareasutility, $context, (int)$users[0]->id, $groupsutility);
        $usersutility->load_items();
        $usersutility->set_currentuser((int)$users[4]->id);
        $this->assertEquals($usersutility->get_current(), (int)$users[4]->id);
        $this->assertEquals($usersutility->get_previous(), (int)$users[3]->id);
        $this->assertEquals($usersutility->get_next(), (int)$users[0]->id);
    }
}
