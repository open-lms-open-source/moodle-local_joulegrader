<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/mr/framework/helper/abstract.php');
/**
 * joule Grader users helper
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

class local_joulegrader_helper_users extends mr_helper_abstract {

    /**
     * @var int - id of the current user
     */
    protected $currentuser;

    /**
     * @var int - id of the next user
     */
    protected $nextuser;

    /**
     * @var int - id of the prev user
     */
    protected $prevuser;

    /**
     * @var array - list of users that can be used in select menu
     */
    protected $users;

    /**
     * @var int
     */
    protected $currentgroup;

    /**
     * @var int
     */
    protected $nextgroup;

    /**
     * @var int
     */
    protected $prevgroup;

    /**
     * @var array
     */
    protected $groups;

    /**
     * @var string
     */
    protected $grouplabel;

    /**
     * Main method for the users helper
     *
     */
    public function direct(local_joulegrader_helper_gradingareas $gareahelper, context $context) {
        global $USER;
        if (has_capability('local/joulegrader:grade', $context)) {
            $this->load_groups();
            $this->load_users($gareahelper);
        } else {
            // This is being viewed as a student, logged in user is current user.
            $this->currentuser = $USER->id;
        }
    }

    protected function load_users($gareahelper) {
        global $COURSE, $CFG;

        if (is_null($this->users)) {
            //is there a current grading area set?
            $currentarea = $gareahelper->get_currentarea();

            $requiredcap = 'local/joulegrader:view';
            if (!empty($currentarea)) {
                //need to load the class for the grading area
                //determine classname based on the grading area
                $gradingareamgr = get_grading_manager($currentarea);

                $component = $gradingareamgr->get_component();
                $area = $gradingareamgr->get_area();

                $classname = "local_joulegrader_lib_gradingarea_{$component}_{$area}_class";

                //include the class
                include_once("$CFG->dirroot/local/joulegrader/lib/gradingarea/{$component}_{$area}/class.php");

                $method = 'get_studentcapability';
                //check to be sure the class was loaded
                if (class_exists($classname) && is_callable("{$classname}::{$method}")) {
                    //find the grading area's required capability for students to appear in menu
                    $requiredcap = $classname::$method();
                }
            }

            //get the enrolled users with the required capability
            $users = get_enrolled_users(context_course::instance($COURSE->id), $requiredcap, $this->get_currentgroup(), 'u.id, u.firstname, u.lastname');

            //make menu from the users
            $this->users = array();

            // allow the plugin to narrow down the users
            $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);
            $includemethod = 'include_users';
            if (!empty($currentarea) && is_callable("{$classname}::{$includemethod}")) {
                // check with the grading area class to make sure to include the current user
                $users = $classname::$includemethod($users, $gradingareamgr, $needsgrading);
            }

            // make sure that the plugin gave us an array back
            if (!is_array($users)) {
                return array();
            }
            foreach ($users as $userid => $user) {
                $this->users[$userid] = fullname($user);
            }
        }
    }

    /**
     * Get the users menu
     *
     *
     * @return array - users that can be graded for the current area
     */
    public function get_users() {
        return $this->users;
    }

    /**
     * Get the current user id
     *
     * @return int - id of the current user
     */
    public function get_currentuser() {
        //if property is null currently then try to set it
        if (is_null($this->currentuser)) {
            //first check to see if there was a param passed
            $guser = optional_param('guser', 0, PARAM_INT);

            //if no param passed take the first user in the course (in the menu)
            if (empty($guser) && !empty($this->users)) {
                reset($this->users);
                $guser = key($this->users);
            } else if (!array_key_exists($guser, $this->users) && !empty($this->users)) {
                reset($this->users);
                $guser = key($this->users);
            }

            //special case where needs grading has excluded all grading areas
            if (empty($this->users) && optional_param('needsgrading', 0, PARAM_BOOL)) {
                $guser = null;
            }

            $this->currentuser = $guser;
        }

        return $this->currentuser;
    }

    /**
     * @param $currentuser - user id for the currentuser
     */
    public function set_currentuser($currentuser) {
        $this->currentuser = $currentuser;
    }

    /**
     * Get the id of the next user
     *
     * @return int - id of the next user
     */
    public function get_nextuser() {
        if (is_null($this->nextuser) && !empty($this->users) && count($this->users) > 1) {
            list($this->prevuser, $this->nextuser) = $this->find_previous_and_next($this->users, $this->get_currentuser());
        }

        return $this->nextuser;
    }

    /**
     * @param int $nextuser
     */
    public function set_nextuser($nextuser) {
        $this->nextuser = $nextuser;
    }

    /**
     * Get the id of the previous user
     *
     * @return int - id of the prev user
     */
    public function get_prevuser() {
        if (is_null($this->prevuser) && !empty($this->users) && count($this->users) > 1) {
            list($this->prevuser, $this->nextuser) = $this->find_previous_and_next($this->users, $this->get_currentuser());
        }

        return $this->prevuser;
    }

    /**
     * @param int $prevuser
     */
    public function set_prevuser($prevuser) {
        $this->prevuser = $prevuser;
    }

    /**
     * @return array
     */
    public function get_groups() {
        return $this->groups;
    }

    /**
     * @return int
     */
    public function get_currentgroup() {
        return $this->currentgroup;
    }

    /**
     * @return int
     */
    public function get_prevgroup() {
        if (is_null($this->prevgroup) && !empty($this->groups) && count($this->groups) > 1) {
            list($this->prevgroup, $this->nextgroup) = $this->find_previous_and_next($this->groups, $this->get_currentgroup());
        }

        return $this->prevgroup;
    }

    /**
     * @return int
     */
    public function get_nextgroup() {
        if (is_null($this->nextgroup) && !empty($this->groups) && count($this->groups) > 1) {
            list($this->prevgroup, $this->nextgroup) = $this->find_previous_and_next($this->groups, $this->get_currentgroup());
        }

        return $this->nextgroup;
    }

    /**
     * @return string
     */
    public function get_grouplabel() {
        return $this->grouplabel;
    }

    /**
     * Find the previous and next user ids
     */
    protected function find_previous_and_next($list, $currentid) {
        $ids     = array_keys($list);
        $previd      = null;
        $nextid      = null;

        //try to get the id before the current id
        while (list($unused, $id) = each($ids)) {
            if ($id == $currentid) {
                break;
            }
            $previd = $id;
        }

        //if we haven't reached the end of the array, current should give "nextid"
        $nextid = current($ids);

        reset($ids);
        if ($nextid === false) {
            //the current category is the last so start at the beginning
            $nextid = $ids[0];
        } else if ($previd === null) {
            //the current category is the first so get the last
            $previd = end($ids);
        }

        return array($previd, $nextid);
    }

    /**
     * Get necessary info to create the group selector navigation
     * This uses code modified from lib/grouplib.php's groups_print_course_menu
     *
     * @return array - array containing current group, groups menu array,group label, previous and next ids
     */
    protected function load_groups() {
        global $COURSE, $USER;

        //first, make sure that the course is using a groupmode
        if (!$groupmode = $COURSE->groupmode) {
            //not using a group mode, so return
            return;
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

        $this->groups = $groupsmenu;
        $this->grouplabel = $grouplabel;
        $this->currentgroup = $activegroup;
    }
}