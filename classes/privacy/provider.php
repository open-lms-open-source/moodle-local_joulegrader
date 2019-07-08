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
 * Privacy Subsystem implementation for local_joulegrader.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\privacy;

use \core_privacy\local\request\writer;
use \core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the Open Grader.
 *
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has user preferences.
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider
{

    /**
     * Returns meta data about this plugin.
     *
     * @param  collection $collection.
     * @return collection $collection after adding a list of user data stored through this plugin.
     */
    public static function get_metadata(collection $collection) : collection {
        // There are several user preferences.
        $collection->add_user_preference('local_joulegrader_fullscreen', 'privacy:metadata:preference:fullscreen');
        $collection->add_user_preference('local_joulegrader_mod_hsuforum_posts_showposts_grouped',
            'privacy:metadata:preference:showpostsgrouped');

        return $collection;
    }

    /**
     * Store all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $fullscreenpref = get_user_preferences('local_joulegrader_fullscreen', null, $userid);
        $postpref = get_user_preferences('local_joulegrader_mod_hsuforum_posts_showposts_grouped', null, $userid);
        $description = null;
        if (!is_null($postpref)) {
            if ($postpref == 0) {
                $description = get_string('privacy:request:preference:hsupostsgroupedno', 'local_joulegrader');
            } else {
                $description = get_string('privacy:request:preference:hsupostsgroupedyes', 'local_joulegrader');
            }
            writer::export_user_preference('local_joulegrader', 'local_joulegrader_mod_hsuforum_posts_showposts_grouped',
                $postpref, $description);
        }
        if (!is_null($fullscreenpref)) {
            if ($fullscreenpref == 0) {
                $description = get_string('privacy:request:preference:fullscreenno', 'local_joulegrader');
            } else {
                $description = get_string('privacy:request:preference:fullscreenyes', 'local_joulegrader');
            }
            writer::export_user_preference('local_joulegrader', 'local_joulegrader_fullscreen', $fullscreenpref, $description);
        }
    }
}
