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

namespace local_joulegrader;

/**
 * Joule Grader default controller testcase.
 *
 * @package    local_joulegrader
 * @category   test
 * @copyright  Copyright (c) 2026 Open LMS (https://www.openlms.net)
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\local_joulegrader_controller_default::class)]
final class controller_default_test extends \advanced_testcase {
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_view_action_output(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/mr/bootstrap.php');
        require_once($CFG->dirroot . '/local/joulegrader/controller/default.php');

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        get_grading_manager(\context_module::instance($assign->cmid), 'mod_assign', 'submissions')->set_active_method('');
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->setUser($teacher);

        $_GET['courseid'] = $course->id;
        set_config('theme', 'boost');
        $controller = new \local_joulegrader_controller_default('local/joulegrader', 'pluginname', 'local_joulegrader', 'view');

        $this->assertStringContainsString(
            '<div id="local-joulegrader-gradepane" class="yui3-u-1-2">',
            $controller->view_action()
        );
    }
}
