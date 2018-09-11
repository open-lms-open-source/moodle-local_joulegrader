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
 * Navigation utility
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\utility;
use local_joulegrader\renderable\navigation_widget;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader navigation utility
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class navigation {

    /**
     * @var string - html for the user navigation
     */
    protected $usernav = '';

    /**
     * @var string - html for the gradeable activity navigation
     */
    protected $activitynav = '';

    /**
     * @var users
     */
    protected $usersutility;

    /**
     * @var gradingareas
     */
    protected $gareautility;

    /**
     * @var \local_joulegrader_renderer
     */
    protected $renderer;

    /**
     * @var int
     */
    protected $navcurrentuser;


    /**
     *
     * @param users $usersutility - users utility
     * @param gradingareas $gareautility - grading areas utility
     * @param \local_joulegrader_renderer $renderer
     */
    public function __construct($usersutility, $gareautility, $renderer = null) {
        global $PAGE;

        $this->gareautility = $gareautility;
        $this->usersutility = $usersutility;
        $this->navcurrentuser = $usersutility->get_current();

        if (is_null($renderer)) {
            $renderer = $PAGE->get_renderer('local_joulegrader');
        }
        $this->renderer = $renderer;
    }

    /**
     * @param int $navuser
     */
    public function set_navcurrentuser($navuser) {
        $this->navcurrentuser = $navuser;
    }

    /**
     * @return string - the activity navigation widget html
     */
    public function get_activity_navigation() {
        global $COURSE;

        $needsgrading = $this->gareautility->get_needsgrading();
        $gradingareas = $this->gareautility->get_items();

        //activity navigation
        if (!empty($gradingareas)) {
            //find the current, next, and previous areas
            $currentarea  = $this->gareautility->get_current();
            $nextarea     = $this->gareautility->get_next();
            $prevarea     = $this->gareautility->get_previous();

            $gareaurl = new \moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'guser' => $this->navcurrentuser));
            if (!empty($needsgrading)) {
                $gareaurl->param('needsgrading', 1);
            }
            $activity_navwidget = new navigation_widget('activity', $gareaurl, $gradingareas, 'garea', $currentarea, $nextarea, $prevarea);
            $activity_navwidget->set_label(get_string('activity', 'local_joulegrader'));
            $this->activitynav = $this->renderer->render($activity_navwidget);
        } else {
            $this->activitynav = '<h4>' . get_string('nogradeableareas', 'local_joulegrader') . '</h4>';
        }
        return $this->activitynav;
    }

    /**
     * @return string - the user navigation widget html
     */
    public function get_users_navigation() {
        global $COURSE, $USER;

        $users = $this->usersutility->get_items();
        $currentarea = $this->gareautility->get_current();
        $currentuser = $this->usersutility->get_current();
        $needsgrading = $this->gareautility->get_needsgrading();

        //groups navigation
        $groupnav = '';
        $groups = $this->usersutility->get_groupsutility()->get_items();

        if (!empty($groups)) {
            //check number of groups
            if (count($groups) == 1) {
                //just a single group, so just use a label
                $groupname = reset($groups);
                $groupnav = $this->usersutility->get_groupsutility()->get_grouplabel().': '.$groupname;
            } else {
                //else need a groups navigation widget
                //groupnav url
                $groupurl = new \moodle_url('/local/joulegrader/view.php'
                    , array('courseid' => $COURSE->id, 'garea' => $currentarea, 'guser' => $currentuser));

                //if needs grading button selected at that param
                if (!empty($needsgrading)) {
                    $groupurl->param('needsgrading', 1);
                }

                $currentgroup = $this->usersutility->get_groupsutility()->get_current();
                $nextgroup = $this->usersutility->get_groupsutility()->get_next();
                $prevgroup = $this->usersutility->get_groupsutility()->get_previous();

                //create the widget and render it
                $groupnavwidget = new navigation_widget('group', $groupurl, $groups, 'group', $currentgroup, $nextgroup, $prevgroup);
                $groupnavwidget->set_label(get_string('group', 'local_joulegrader'));
                $groupnav = $this->renderer->render($groupnavwidget);
            }
        }

        $this->usernav = $groupnav;

        //user navigation
        if (!empty($users)) {
            $guserurl = new \moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentarea));
            if (!empty($needsgrading)) {
                $guserurl->param('needsgrading', 1);
            }

            $prevuser = $this->usersutility->get_previous();
            $nextuser = $this->usersutility->get_next();

            $user_navwidget = new navigation_widget('user', $guserurl, $users, 'guser', $currentuser, $nextuser, $prevuser);
            $user_navwidget->set_label(get_string('user', 'local_joulegrader'));
            $this->usernav .= $this->renderer->render($user_navwidget);
        } else if (!empty($currentuser) and $currentuser != $USER->id) {
            $this->usernav .= '<h4>' . get_string('nogradeableusers', 'local_joulegrader') . '</h4>';
        }

        return $this->usernav;
    }

    /**
     * @param \moodle_url $controllerurl
     * @param \context $context
     * @return string
     */
    public function get_navigation_buttons($controllerurl, $context) {
        global $COURSE, $OUTPUT;

        $fullscreenurl = clone $controllerurl;
        $fullscreenparam = get_user_preferences('local_joulegrader_fullscreen', 1);
        $fullscreenurl->param('fullscreen', !$fullscreenparam);

        if (!empty($fullscreenparam)) {
            $fullscreenstring = get_string('exitfullscreen', 'local_joulegrader');
        } else {
            $fullscreenstring = get_string('fullscreen', 'local_joulegrader');
        }

        $fullscreenbutton = $OUTPUT->single_button($fullscreenurl, $fullscreenstring, 'get');

        $returncoursebutton = '';
        if (!empty($fullscreenparam)) {
            $returncourseurl = new \moodle_url('/course/view.php', array('id' => $COURSE->id));
            $returncoursebutton = $OUTPUT->single_button($returncourseurl, get_string('returncourse', 'local_joulegrader'), 'get');
        }

        //needs grading button
        //button nav
        $buttonnav = '';
        if (has_capability('local/joulegrader:grade', $context)) {
            $buttonurl = clone $controllerurl;

            $needsgrading = $this->gareautility->get_needsgrading();
            if (empty($needsgrading)) {
                $buttonstring = get_string('needsgrading', 'local_joulegrader');
                $buttonurl->param('needsgrading', 1);
            } else {
                $buttonstring = get_string('allactivities', 'local_joulegrader');
            }
            $buttonnav = $OUTPUT->single_button($buttonurl, $buttonstring, 'get');
        }

        return $fullscreenbutton . $returncoursebutton . $buttonnav;
    }

}
