<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/navigation_widget.php');
require_once($CFG->dirroot . '/local/mr/framework/helper/abstract.php');
/**
 * joule Grader navigation helper
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_helper_navigation extends mr_helper_abstract {

    /**
     * @var string - html for the user navigation
     */
    protected $usernav = '';

    /**
     * @var string - html for the gradeable activity navigation
     */
    protected $activitynav = '';

    /**
     * Main entry point
     *
     * @param $usershelper local_joulegrader_helper_users - users helper
     * @param $gareahelper local_joulegrader_helper_gradingareas - grading areas helper
     * @param $coursecontext - course context object
     */
    public function direct($usershelper, $gareahelper, $coursecontext) {
        global $COURSE, $USER, $PAGE;

        $renderer = $PAGE->get_renderer('local_joulegrader');

        //if logged in user has local/joulegrader:grade then assume teacher role
        if (has_capability('local/joulegrader:grade', $coursecontext)) {
            //get all grading areas supported by joule Grader
            $gradingareas = $gareahelper->get_gradingareas();

            //find the current, next, and previous areas
            $currentarea  = $gareahelper->get_currentarea();
            $nextarea     = $gareahelper->get_nextarea();
            $prevarea     = $gareahelper->get_prevarea();

            //find users with capability provided by the grading area class
            list($currentgroup, $groups, $grouplabel, $prevgroup, $nextgroup) = $this->get_groupnav_info();
            $users = $usershelper->get_users($gareahelper, $currentgroup);

            //get the current user
            $currentuser = $usershelper->get_currentuser();
            $nextuser    = $usershelper->get_nextuser();
            $prevuser    = $usershelper->get_prevuser();

            $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);

            //activity navigation
            if (!empty($gradingareas)) {
                $gareaurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'guser' => $currentuser));
                if (!empty($needsgrading)) {
                    $gareaurl->param('needsgrading', 1);
                }
                $activity_navwidget = new local_joulegrader_lib_navigation_widget('activity', $gareaurl, $gradingareas, 'garea', $currentarea, $nextarea, $prevarea);
                $activity_navwidget->set_label(get_string('activity', 'local_joulegrader'));
                $this->activitynav = $renderer->render($activity_navwidget);
            } else {
                $this->activitynav = '<h4>' . get_string('nogradeableareas', 'local_joulegrader') . '</h4>';
            }

            //groups navigation
            $groupnav = '';
            if (!empty($groups)) {
                //check number of groups
                if (count($groups) == 1) {
                    //just a single group, so just use a label
                    $groupname = reset($groups);
                    $groupnav = $grouplabel.': '.$groupname;
                } else {
                    //else need a groups navigation widget
                    //groupnav url
                    $groupurl = new moodle_url('/local/joulegrader/view.php'
                            , array('courseid' => $COURSE->id, 'garea' => $currentarea, 'guser' => $currentuser));

                    //if needs grading button selected at that param
                    if (!empty($needsgrading)) {
                        $groupurl->param('needsgrading', 1);
                    }

                    //create the widget and render it
                    $groupnavwidget = new local_joulegrader_lib_navigation_widget('group', $groupurl, $groups, 'group', $currentgroup, $nextgroup, $prevgroup);
                    $groupnavwidget->set_label(get_string('group', 'local_joulegrader'));
                    $groupnav = $renderer->render($groupnavwidget);
                }
            }

            $this->usernav = $groupnav;

            //user navigation
            if (!empty($users)) {
                $guserurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentarea));
                if (!empty($needsgrading)) {
                    $guserurl->param('needsgrading', 1);
                }
                $user_navwidget = new local_joulegrader_lib_navigation_widget('user', $guserurl, $users, 'guser', $currentuser, $nextuser, $prevuser);
                $user_navwidget->set_label(get_string('user', 'local_joulegrader'));
                $this->usernav .= $renderer->render($user_navwidget);
            } else {
                $this->usernav .= '<h4>' . get_string('nogradeableusers', 'local_joulegrader') . '</h4>';
            }

        } else {
            //get grading areas that user has proper capabilites in
            $gradingareas = $gareahelper->get_gradingareas(true);

            //find the current, next, and previous areas
            $currentarea  = $gareahelper->get_currentarea();
            $nextarea     = $gareahelper->get_nextarea();
            $prevarea     = $gareahelper->get_prevarea();

            //activity navigation
            if (!empty($gradingareas)) {
                $activity_navwidget = new local_joulegrader_lib_navigation_widget('activity', new moodle_url('/local/joulegrader/view.php'
                    , array('courseid' => $COURSE->id, 'guser' => $USER->id)), $gradingareas, 'garea', $currentarea, $nextarea, $prevarea);
                $activity_navwidget->set_label(get_string('activity', 'local_joulegrader'));
                $this->activitynav = $renderer->render($activity_navwidget);
            } else {
                $this->activitynav = '<h4>' . get_string('nogradeableareas', 'local_joulegrader') . '</h4>';
            }

            //set the userhelper current user id to logged-in $USER
            $usershelper->set_currentuser($USER->id);
        }
    }

    /**
     * @return string - the activity navigation widget html
     */
    public function get_activity_navigation() {
        return $this->activitynav;
    }

    /**
     * @return string - the user navigation widget html
     */
    public function get_users_navigation() {
        return $this->usernav;
    }

    /**
     * @param moodle_url $controllerurl
     * @param stdClass $context
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
            $returncourseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
            $returncoursebutton = $OUTPUT->single_button($returncourseurl, get_string('returncourse', 'local_joulegrader'), 'get');
        }

        //needs grading button
        //button nav
        $buttonnav = '';
        if (has_capability('local/joulegrader:grade', $context)) {
            $buttonurl = clone $controllerurl;

            $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);
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

    /**
     * Get necessary info to create the group selector navigation
     * This uses code modified from lib/grouplib.php's groups_print_course_menu
     *
     * @return array - array containing current group, groups menu array,group label, previous and next ids
     */
    protected function get_groupnav_info() {
        global $COURSE, $USER;

        //first, make sure that the course is using a groupmode
        if (!$groupmode = $COURSE->groupmode) {
            //not using a group mode, so return
            return array(0, array(), '', null, null);
        }

        $context = context_course::instance($COURSE->id);
        $aag = has_capability('moodle/site:accessallgroups', $context);

        if ($groupmode == VISIBLEGROUPS or $aag) {
            $allowedgroups = groups_get_all_groups($COURSE->id, 0, $COURSE->defaultgroupingid);
        } else {
            $allowedgroups = groups_get_all_groups($COURSE->id, $USER->id, $COURSE->defaultgroupingid);
        }

        $activegroup = groups_get_course_group($COURSE, true, $allowedgroups);

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

        if ($aag and $COURSE->defaultgroupingid) {
            if ($grouping = groups_get_grouping($COURSE->defaultgroupingid)) {
                $grouplabel = $grouplabel . ' (' . format_string($grouping->name) . ')';
            }
        }

        //find previous and next groups
        $previd = null;
        $nextid = null;
        if (!empty($groupsmenu) && count($groupsmenu) > 1) {
            $groupids = array_keys($groupsmenu);

            //try to get the group before the current group
            while (list($unused, $groupid) = each($groupids)) {
                if ($groupid == $activegroup) {
                    break;
                }
                $previd = $groupid;
            }

            //if we haven't reached the end of the array, current should give "nextid"
            $nextid = current($groupids);

            reset($groupids);
            if ($nextid === false) {
                //the current group is the last so start at the beginning
                $nextid = $groupids[0];
            } else if ($previd === null) {
                //the current group is the first so get the last
                $previd = end($groupids);
            }
        }
        //return the current (active) group, groups menu array, and group label
        return array($activegroup, $groupsmenu, $grouplabel, $previd, $nextid);
    }
}