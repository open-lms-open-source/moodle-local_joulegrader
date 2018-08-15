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
 * Groups utility class.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  2014 Blackboard Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\utility;


class groups extends loopable_abstract {

    /**
     * @var \context_course
     */
    protected $coursecontext;

    /**
     * @var string
     */
    protected $grouplabel;

    /**
     * @var
     */
    protected $loggedinuser;

    /**
     * @param \context_course $context
     * @param int $loggedinuser
     */
    public function __construct(\context_course $context, $loggedinuser = null) {
        global $USER;
        $this->coursecontext = $context;
        if (is_null($loggedinuser)) {
            $loggedinuser = $USER->id;
        }
        $this->loggedinuser = $loggedinuser;
        $this->load_items();
    }

    /**
     * @return string
     */
    public function get_grouplabel() {
        return $this->grouplabel;
    }

    /**
     * Get necessary info to create the group selector navigation
     * This uses code modified from lib/grouplib.php's groups_print_course_menu
     *
     * @return array - array containing current group, groups menu array,group label, previous and next ids
     */
    public function load_items() {
        $context = $this->coursecontext;
        $course  = get_course($context->instanceid);

        //first, make sure that the course is using a groupmode
        if (!$groupmode = $course->groupmode) {
            //not using a group mode, so return
            return;
        }

        $aag = has_capability('moodle/site:accessallgroups', $context);

        if ($groupmode == VISIBLEGROUPS or $aag) {
            $allowedgroups = groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($course->id, $this->loggedinuser, $course->defaultgroupingid);
        }

        $activegroup = groups_get_course_group($course, true, $allowedgroups);

        $groupsmenu = array();
        if (!$allowedgroups or $groupmode == VISIBLEGROUPS or $aag) {
            $groupsmenu[0] = get_string('allparticipants');
        }

        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                $groupsmenu[$group->id] = shorten_text(format_string($group->name), 20, true);
            }
        }

        if ($groupmode == VISIBLEGROUPS) {
            $grouplabel = get_string('groupsvisible');
        } else {
            $grouplabel = get_string('groupsseparate');
        }

        if ($aag and $course->defaultgroupingid) {
            if ($grouping = groups_get_grouping($course->defaultgroupingid)) {
                $grouplabel = $grouplabel . ' (' . format_string($grouping->name) . ')';
            }
        }

        $this->items = $groupsmenu;
        $this->grouplabel = $grouplabel;
        $this->current = $activegroup;
    }
} 
