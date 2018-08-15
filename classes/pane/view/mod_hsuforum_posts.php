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
 * View pane for mod_hsuforum_posts
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\pane\view;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * View Pane class for mod_hsuforum_posts
 *
 * @author Mark Nielsen
 * @package local/joulegrader
 */
class mod_hsuforum_posts extends view_abstract {

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');
    }
}
