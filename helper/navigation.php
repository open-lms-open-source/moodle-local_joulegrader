<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/navigation_widget.php');
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
            $users = $usershelper->get_users($gareahelper);

            //get the current user
            $currentuser = $usershelper->get_currentuser();
            $nextuser    = $usershelper->get_nextuser();
            $prevuser    = $usershelper->get_prevuser();

            //activity navigation
            if (!empty($gradingareas)) {
                $activity_navwidget = new local_joulegrader_lib_navigation_widget('activity', new moodle_url('/local/joulegrader/view.php'
                        , array('courseid' => $COURSE->id, 'guser' => $currentuser)), $gradingareas, 'garea', $currentarea, $nextarea, $prevarea);

                $this->activitynav = $renderer->render($activity_navwidget);
            } else {
                $this->activitynav = '<h4>' . get_string('nogradeableareas', 'local_joulegrader') . '</h4>';
            }

            //user navigation
            if (!empty($users)) {
                $user_navwidget = new local_joulegrader_lib_navigation_widget('user', new moodle_url('/local/joulegrader/view.php'
                        , array('courseid' => $COURSE->id, 'garea' => $currentarea)), $users, 'guser', $currentuser, $nextuser, $prevuser);

                $this->usernav = $renderer->render($user_navwidget);
            } else {
                $this->usernav = '<h4>' . get_string('nogradeableusers', 'local_joulegrader') . '</h4>';
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
}