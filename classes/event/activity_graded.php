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
 * Define the Activity Graded event
 *
 * This event is fired when an activity is graded using the
 * Joule Grader.
 *
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The class for the Activity Graded event
 *
 * @property-read array $other {
 *     - $userid   the user id being viewed by the user
 *     - $areaid   the grading area viewed by the user
 * }
 *
 * @package local_joulegrader
 * @subpackage event
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_graded extends \core\event\base {

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Build the description of this event
     *
     * @return string the description of the event
     */
    public function get_description() {
        return "The user with id '{$this->userid}' graded an activity using Open Grader in the course with id '{$this->courseid}' " .
            "with grading area id '{$this->other['areaid']}' and user id '{$this->relateduserid}'.";
    }

    /**
     * Get the name of this event
     *
     * @return string the name of this event
     */
    public static function get_name() {
        return get_string('eventactivitygraded', 'local_joulegrader');
    }

    /**
     * Get the URL related to this action
     *
     * @return \core\url
     */
    public function get_url() {
        return new \core\url('/local/joulegrader/view.php?', array(
            'guser'    => $this->relateduserid,
            'garea'    => $this->other['areaid'],
            'courseid' => $this->courseid,
        ));
    }

    /**
     * @return array
     */
    public static function get_other_mapping() {
        return ['areaid' => ['db' => 'grading_areas', 'restore' => 'grading_area']];
    }
}
