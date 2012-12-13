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
     * Main method for the users helper
     *
     */
    public function direct() {}

    /**
     * Get the users menu
     *
     * @param $gareahelper- the current
     * @param $currentgroup = id of currently selected group
     *
     * @return array - users that can be graded for the current area
     */
    public function get_users($gareahelper, $currentgroup = 0) {
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
            $users = get_enrolled_users(context_course::instance($COURSE->id), $requiredcap, $currentgroup, 'u.id, u.firstname, u.lastname');

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
                $guser = array_shift(array_keys($this->users));
            } else if (!array_key_exists($guser, $this->users) && !empty($this->users)) {
                $guser = array_shift(array_keys($this->users));
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
            $this->find_previous_and_next();
        }

        return $this->nextuser;
    }

    /**
     * Get the id of the previous user
     *
     * @return int - id of the prev user
     */
    public function get_prevuser() {
        if (is_null($this->prevuser) && !empty($this->users) && count($this->users) > 1) {
            $this->find_previous_and_next();
        }

        return $this->prevuser;
    }

    /**
     * Find the previous and next user ids
     */
    protected function find_previous_and_next() {
        $currentuser = $this->get_currentuser();
        $userids     = array_keys($this->users);
        $previd      = null;
        $nextid      = null;

        //try to get the user before the current user
        while (list($unused, $userid) = each($userids)) {
            if ($userid == $currentuser) {
                break;
            }
            $previd = $userid;
        }

        //if we haven't reached the end of the array, current should give "nextid"
        $nextid = current($userids);

        reset($userids);
        if ($nextid === false) {
            //the current category is the last so start at the beginning
            $nextid = $userids[0];
        } else if ($previd === null) {
            //the current category is the first so get the last
            $previd = end($userids);
        }

        $this->prevuser = $previd;
        $this->nextuser = $nextid;
    }
}