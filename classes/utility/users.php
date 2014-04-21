<?php
namespace local_joulegrader\utility;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader users utility
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

class users {

    /**
     * @var gradingareas
     */
    protected $gareautility;

    /**
     * @var int|null
     */
    protected $loggedinuser;

    /**
     * @var int The passed guser
     */
    protected $guserparam;

    /**
     * @var \context_course
     */
    protected $coursecontext;

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
     * @param gradingareas $gareautility
     * @param \context_course $context
     * @param int $guserparam
     * @param null|int $loggedinuser
     */
    public function __construct(gradingareas $gareautility, \context_course $context, $guserparam, $loggedinuser = null) {
        global $USER;
        $this->coursecontext = $context;
        if (is_null($loggedinuser)) {
            $loggedinuser = $USER->id;
        }
        $this->loggedinuser  = $loggedinuser;
        $this->gareautility  = $gareautility;
        $this->guserparam    = $guserparam;

        $this->load_users();
    }

    /**
     * @return array
     */
    public function get_users() {
        return $this->users;
    }

    /**
     * @param string $capability
     * @param int $currentgroup
     * @return array
     */
    public function get_enrolled_users($capability, $currentgroup = 0) {
        // Get the enrolled users with the required capability.
        $users = get_enrolled_users($this->coursecontext, $capability, $currentgroup,
            'u.id, '.get_all_user_name_fields(true, 'u'));

        return $users;
    }

    /**
     * Get the users menu
     *
     *
     * @return array - users that can be graded for the current area
     */
    public function load_users() {
        $this->users = array();

        $this->load_groups();
        //is there a current grading area set?
        $currentarea = $this->gareautility->get_currentarea();

        $requiredcap = 'local/joulegrader:view';
        if (!empty($currentarea)) {
            //need to load the class for the grading area
            //determine classname based on the grading area
            $gradingareamgr = get_grading_manager($currentarea);

            $component = $gradingareamgr->get_component();
            $area = $gradingareamgr->get_area();

            $classname = "\\local_joulegrader\\gradingarea\\{$component}_{$area}";

            $method = 'get_studentcapability';
            //check to be sure the class was loaded
            if (class_exists($classname) && is_callable("{$classname}::{$method}")) {
                //find the grading area's required capability for students to appear in menu
                $requiredcap = $classname::$method();
            }
        }
        $users = $this->get_enrolled_users($requiredcap, $this->get_currentgroup());

        // allow the plugin to narrow down the users
        $needsgrading = $this->gareautility->get_needsgrading();
        $includemethod = 'include_users';
        if (!empty($currentarea) && !empty($classname) && is_callable("{$classname}::{$includemethod}")) {
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

        return $this->users;
    }

    /**
     * Get the current user id
     *
     * @return int - id of the current user
     */
    public function get_currentuser() {
        if (count($this->users) === 1) {
            $user = reset($$this->users);
            if ($user->id == $this->guserparam) {
                $this->currentuser = $this->guserparam;
            }
        }

        //if property is null currently then try to set it
        if (is_null($this->currentuser)) {
            //first check to see if there was a param passed
            $guser = $this->guserparam;

            //if no param passed take the first user in the course (in the menu)
            if (empty($guser) && !empty($this->users)) {
                reset($this->users);
                $guser = key($this->users);
            } else if (!array_key_exists($guser, $this->users) && !empty($this->users)) {
                reset($this->users);
                $guser = key($this->users);
            }

            //special case where needs grading has excluded all grading areas
            $needsgrading = $this->gareautility->get_needsgrading();
            if (empty($this->users) && !empty($needsgrading)) {
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

        $this->groups = $groupsmenu;
        $this->grouplabel = $grouplabel;
        $this->currentgroup = $activegroup;
    }
}