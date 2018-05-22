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
 * Unit tests for the local_joulegrader implementation of the privacy API.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\writer;
use \local_joulegrader\privacy\provider;

/**
 * Unit tests for the local_joulegrader implementation of the privacy API.
 *
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_joulegrader_privacy_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Ensure that get_metadata exports valid content.
     */
    public function test_get_metadata() {
        $items = new collection('local_joulegrader');
        $result = provider::get_metadata($items);
        $this->assertSame($items, $result);
        $this->assertInstanceOf(collection::class, $result);
    }

    /**
     * Ensure that export_user_preferences returns no data if the user has no preferences stored.
     */
    public function test_export_user_preferences_no_data() {
        $user = \core_user::get_user_by_username('admin');
        provider::export_user_preferences($user->id);

        $writer = writer::with_context(\context_system::instance());

        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Ensure that export_user_preferences returns some data when there are preferences stored.
     */
    public function test_export_user_preferences_with_data() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = \core_user::get_user_by_username('admin');
        set_user_preference('local_joulegrader_fullscreen', 0);
        set_user_preference('local_joulegrader_mod_hsuforum_posts_showposts_grouped', 1);

        provider::export_user_preferences($user->id);
        $writer = writer::with_context(\context_system::instance());

        $this->assertTrue($writer->has_any_data());
        $preferences = $writer->get_user_preferences('local_joulegrader');

        $this->assertCount(2, (array) $preferences);
    }
}
