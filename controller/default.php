<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader Default Controller
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_controller_default extends mr_controller {

    /**
     * Require capabilities
     */
    public function require_capability() {
        switch ($this->action) {
            case 'process':
                require_capability('local/joulegrader:grade', $this->get_context());
            case 'view':
            default:
                if (!has_capability('local/joulegrader:grade', $this->get_context())) {
                    require_capability('local/joulegrader:view', $this->get_context());
                }
        }
    }

    /**
     * Controller Initialization
     *
     */
    public function init() {
        global $PAGE;

        //no heading
        $this->heading->text = '';

        //add 'joule Grader' to bread crumb
        $PAGE->navbar->add(get_string('pluginname', 'local_joulegrader'));
    }

    /**
     * Main view action
     *
     * @return string - the html for the view action
     */
    public function view_action() {
        global $OUTPUT, $COURSE, $PAGE;

        //Not sure if this is supposed to be a popup
        //$PAGE->set_pagelayout('popup');

        //get the joule grader header info
        //link nav
        $linknav = $OUTPUT->action_link(new moodle_url('/course/view.php', array('id' => $COURSE->id)), get_string('course'));
        $linknav .= ' | ' . $OUTPUT->action_link(new moodle_url('/grade/report/index.php', array('id' => $COURSE->id))
                , get_string('gradebook', 'local_joulegrader'));
        $linknav = $OUTPUT->container($linknav, null, 'local-joulegrader-linknav');
        $linknav = $OUTPUT->container($linknav, 'content');

        //button nav
        $buttonnav = '';

        //pull out the users helper and gradingareas helper
        $usershelper = $this->helper->users;

        //@var local_joulegrader_helper_gradingareas $gareashelper
        $gareashelper = $this->helper->gradingareas;

        //initialize the navigation
        $this->helper->navigation($usershelper, $gareashelper, $this->get_context());

        //activity navigation
        $activitynav = $this->helper->navigation->get_activity_navigation();
        $activitynav = $OUTPUT->container($activitynav, null, 'local-joulegrader-activitynav');

        //user navigation
        $usernav = $this->helper->navigation->get_users_navigation();
        $usernav = $OUTPUT->container($usernav, null, 'local-joulegrader-usernav');

        $menunav = $OUTPUT->container($activitynav . $usernav, 'content');

        $usernavcon = $OUTPUT->container($linknav, 'yui3-u-1-3', 'local-joulegrader-linknav');
        $buttonnavcon = $OUTPUT->container($buttonnav, 'yui3-u-1-3', 'local-joulegrader-buttonnav');
        $activitynavcon = $OUTPUT->container($menunav, 'yui3-u-1-3', 'local-joulegrader-menunav');

        $currentareaid = $gareashelper->get_currentarea();
        $currentuserid = $usershelper->get_currentuser();

        //if the current user id and the current area id are not empty, load the class and get the pane contents
        if (!empty($currentareaid) && !empty($currentuserid)) {
            $renderer = $PAGE->get_renderer('local_joulegrader');

            //load the current area instance
            if (!isset($this->gradeareainstance)) {
                $gradeareainstance = $gareashelper::get_gradingarea_instance($currentareaid, $currentuserid);
            } else {
                $gradeareainstance = $this->gradeareainstance;
            }
            $viewhtml = $renderer->render($gradeareainstance->get_viewpane());
            $gradehtml = $renderer->render($gradeareainstance->get_gradepane());

            //get the view pane contents
            $viewpane = '<div class="content">' . $viewhtml . '</div>';


            //get the grade pane contents
            $gradepane = '<div class="content">' . $gradehtml . '</div>';

            $panescontainer = $OUTPUT->container($viewpane, 'yui3-u-4-5', 'local-joulegrader-viewpane');
            $panescontainer .= $OUTPUT->container($gradepane, 'yui3-u-1-5', 'local-joulegrader-gradepane');
        } else {
            $panescontainer = $OUTPUT->container(html_writer::tag('h1', get_string('nothingtodisplay', 'local_joulegrader')), 'content');
        }

        //navigation container
        $output = $OUTPUT->container($usernavcon . $buttonnavcon . $activitynavcon, 'yui3-u-1', 'local-joulegrader-navigation');

        //panes container
        $output .= $OUTPUT->container($panescontainer, 'yui3-u-1', 'local-joulegrader-panes');

        //wrap it all up
        $output = $OUTPUT->container($output, 'yui3-g', 'local-joulegrader');

        //return all of that
        return $output;
    }

    /**
     * Process action - processes grade form and redirects
     *
     * @return void
     */
    public function process_action() {
        //get current area id and current user parameters for the gradingarea instance
        $currentareaid = required_param('garea', PARAM_INT);
        $currentuserid = required_param('guser', PARAM_INT);

        //@var local_joulegrader_helper_gradingareas $gareashelper
        $gareashelper = $this->helper->gradingareas;

        //need to prime the helper with the grading areas for the get_currentarea()
        $gareashelper->get_gradingareas();

        //make sure that the area passed from the form matches what is determined by the areas helper
        if ($currentareaid != $gareashelper->get_currentarea()) {
            //should not get here unless ppl are messing with form data
            throw new moodle_exception('areaidpassednotvalid', 'local_joulegrader');
        }

        //pull out the users helper and gradingareas helper
        $usershelper = $this->helper->users;

        //just need prime the helper for the currentuser() and nextuser() calls
        $usershelper->get_users($gareashelper);

        //make sure the passed user and passed area match what is available
        if ($currentuserid != $usershelper->get_currentuser()) {
            //there is some funny business going on here
            throw new moodle_exception('useridpassednotvalid', 'local_joulegrader');
        }

        //load the current area instance
        $gradeareainstance = $gareashelper::get_gradingarea_instance($currentareaid, $currentuserid);

        if (!$gradeareainstance->get_gradepane()->is_validated()) {
            $this->gradeareainstance = $gradeareainstance;
            echo $this->print_header();
            echo $this->view_action();
            echo $this->print_footer();
            die;
        }
        //fire off the process method of the grade pane, it should redirect or throw error
        $gradeareainstance->get_gradepane()->process($this->notify);

    }
}