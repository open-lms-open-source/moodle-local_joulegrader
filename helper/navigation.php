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
     * @var local_joulegrader_helper_users
     */
    protected $usershelper;

    /**
     * @var local_joulegrader_helper_gradingareas
     */
    protected $gareahelper;

    /**
     * @var local_joulegrader_renderer
     */
    protected $renderer;

    /**
     * @var int
     */
    protected $navcurrentuser;


    /**
     * Main entry point
     *
     * @param $usershelper local_joulegrader_helper_users - users helper
     * @param $gareahelper local_joulegrader_helper_gradingareas - grading areas helper
     */
    public function direct($usershelper, $gareahelper) {
        global $PAGE;

        $this->gareahelper = $gareahelper;
        $this->usershelper = $usershelper;
        $this->navcurrentuser = $usershelper->get_currentuser();
        $this->renderer = $PAGE->get_renderer('local_joulegrader');
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

        $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);
        $gradingareas = $this->gareahelper->get_gradingareas();

        //activity navigation
        if (!empty($gradingareas)) {
            //find the current, next, and previous areas
            $currentarea  = $this->gareahelper->get_currentarea();
            $nextarea     = $this->gareahelper->get_nextarea();
            $prevarea     = $this->gareahelper->get_prevarea();

            $gareaurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'guser' => $this->navcurrentuser));
            if (!empty($needsgrading)) {
                $gareaurl->param('needsgrading', 1);
            }
            $activity_navwidget = new local_joulegrader_lib_navigation_widget('activity', $gareaurl, $gradingareas, 'garea', $currentarea, $nextarea, $prevarea);
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

        $users = $this->usershelper->get_users();
        $currentarea = $this->gareahelper->get_currentarea();
        $currentuser = $this->usershelper->get_currentuser();

        //groups navigation
        $groupnav = '';
        $groups = $this->usershelper->get_groups();

        if (!empty($groups)) {
            //check number of groups
            if (count($groups) == 1) {
                //just a single group, so just use a label
                $groupname = reset($groups);
                $groupnav = $this->usershelper->get_grouplabel().': '.$groupname;
            } else {
                //else need a groups navigation widget
                //groupnav url
                $groupurl = new moodle_url('/local/joulegrader/view.php'
                    , array('courseid' => $COURSE->id, 'garea' => $currentarea, 'guser' => $currentuser));

                //if needs grading button selected at that param
                if (!empty($needsgrading)) {
                    $groupurl->param('needsgrading', 1);
                }

                $currentgroup = $this->usershelper->get_currentgroup();
                $nextgroup = $this->usershelper->get_nextgroup();
                $prevgroup = $this->usershelper->get_prevgroup();

                //create the widget and render it
                $groupnavwidget = new local_joulegrader_lib_navigation_widget('group', $groupurl, $groups, 'group', $currentgroup, $nextgroup, $prevgroup);
                $groupnavwidget->set_label(get_string('group', 'local_joulegrader'));
                $groupnav = $this->renderer->render($groupnavwidget);
            }
        }

        $this->usernav = $groupnav;

        //user navigation
        if (!empty($users)) {
            $guserurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentarea));
            if (!empty($needsgrading)) {
                $guserurl->param('needsgrading', 1);
            }

            $prevuser = $this->usershelper->get_prevuser();
            $nextuser = $this->usershelper->get_nextuser();

            $user_navwidget = new local_joulegrader_lib_navigation_widget('user', $guserurl, $users, 'guser', $currentuser, $nextuser, $prevuser);
            $user_navwidget->set_label(get_string('user', 'local_joulegrader'));
            $this->usernav .= $this->renderer->render($user_navwidget);
        } else if (!empty($currentuser) and $currentuser != $USER->id) {
            $this->usernav .= '<h4>' . get_string('nogradeableusers', 'local_joulegrader') . '</h4>';
        }

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

}