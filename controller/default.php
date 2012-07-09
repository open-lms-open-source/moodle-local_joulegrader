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

        // set layout to include blocks
        switch ($this->action) {
            case 'view':
            case 'process':
                $PAGE->set_pagelayout('standard');
                break;
            case 'viewcommentloop':
                $PAGE->set_pagelayout('embedded');
                break;
        }
    }

    /**
     * Main view action
     *
     * @return string - the html for the view action
     */
    public function view_action() {
        global $OUTPUT, $COURSE, $PAGE;

        //check for mobile browsers (currently not supported)
        if (get_device_type() == 'mobile') {
            //just return a message that mobile devices are not currently supported
            return $OUTPUT->container(html_writer::tag('h2', get_string('mobilenotsupported', 'local_joulegrader')), null, 'local-joulegrader-mobilenotsupportedmsg');
        }

        //get the joule grader header info
        //link nav
        $linknav = $OUTPUT->action_link(new moodle_url('/course/view.php', array('id' => $COURSE->id)), get_string('course'));
        $linknav .= ' | ' . $OUTPUT->action_link(new moodle_url('/grade/report/index.php', array('id' => $COURSE->id))
                , get_string('gradebook', 'local_joulegrader'));
        $linknav = $OUTPUT->container($linknav, null, 'local-joulegrader-linknav');
        $linknav = $OUTPUT->container($linknav, 'content');

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

        $currentareaid = $gareashelper->get_currentarea();
        $currentuserid = $usershelper->get_currentuser();

        //needs grading button
        //button nav
        $buttonnav = '';
        if (has_capability('local/joulegrader:grade', $this->get_context())) {
            $buttonurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentareaid
                    , 'guser' => $currentuserid));

            $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);
            if (empty($needsgrading)) {
                $buttonstring = get_string('needsgrading', 'local_joulegrader');
                $buttonurl->param('needsgrading', 1);
            } else {
                $buttonstring = get_string('allactivities', 'local_joulegrader');
            }
            $buttonnav = html_writer::tag('div', $OUTPUT->single_button($buttonurl, $buttonstring, 'get'), array('class' => 'content'));
        }

        $menunav = $OUTPUT->container($activitynav . $usernav, 'content');

        $usernavcon = $OUTPUT->container($linknav, 'yui3-u-1-3', 'local-joulegrader-linknav');
        $buttonnavcon = $OUTPUT->container($buttonnav, 'yui3-u-1-3', 'local-joulegrader-buttonnav');
        $activitynavcon = $OUTPUT->container($menunav, 'yui3-u-1-3', 'local-joulegrader-menunav');

        //if the current user id and the current area id are not empty, load the class and get the pane contents
        if (!empty($currentareaid) && !empty($currentuserid)) {
            $renderer = $PAGE->get_renderer('local_joulegrader');

            //load the current area instance
            if (!isset($this->gradeareainstance)) {
                $gradeareainstance = $gareashelper::get_gradingarea_instance($currentareaid, $currentuserid);
            } else {
                $gradeareainstance = $this->gradeareainstance;
            }

            //set user id for "save and next" button
            $gradeareainstance->set_nextuserid($usershelper->get_nextuser());

            $viewhtml = $renderer->render($gradeareainstance->get_viewpane());
            $gradehtml = $renderer->render($gradeareainstance->get_gradepane());


            //get the comment loop for the gradingarea
            $commentloophtml = $renderer->render($gradeareainstance->get_commentloop());

            //get the view pane contents
            $viewpane = '<div class="content">' . $viewhtml . '</div>';


            //get the grade pane contents
            $gradepane = '<div class="content">' . $gradehtml . $commentloophtml . '</div>';

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

        //set next userid
        $gradeareainstance->set_nextuserid($usershelper->get_nextuser());

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

    /**
     * Delete a comment
     *
     * @throws Exception
     */
    public function deletecomment_action() {
        global $CFG, $DB, $COURSE, $PAGE;

        require_once($CFG->dirroot . '/local/joulegrader/lib/comment/class.php');
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        //ajax request?
        $isajaxrequest = optional_param('ajax', false, PARAM_BOOL);

        try {
            //required param for commentid
            $commentid = required_param('commentid', PARAM_INT);

            //require sesskey
            require_sesskey();

            $commentrecord = $DB->get_record('local_joulegrader_comments', array('id' => $commentid), '*', MUST_EXIST);
            $comment = new local_joulegrader_lib_comment_class($commentrecord);

            //check to make sure that the logged in user can delete
            if ($comment->user_can_delete()) {

                //yes we can delete, delete the comment
                $comment->delete();
            }

            if (!$isajaxrequest) {
                redirect(new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $comment->get_gareaid(), 'guser' => $comment->get_guserid())));
            } else {
                $renderer = $PAGE->get_renderer('local_joulegrader');

                // Need to set the context
                $gradingmgr = get_grading_manager($comment->get_gareaid());
                $comment->set_context($gradingmgr->get_context());

                $commenthtml = $renderer->render($comment);

                $commentinfo = new stdClass();
                $commentinfo->html = $commenthtml;

                //send response
                echo json_encode($commentinfo);
                die;
            }
        } catch (Exception $e) {
            if (!$isajaxrequest) {
                //rethrow the exception, let moodle handle it
                throw $e;
            } else {
                //need more delicate handling since it's an ajax request
                $error = new stdClass();
                $error->error = $e->getMessage();

                echo json_encode($error);
                die;
            }
        }
    }

    /**
     * Add a comment
     */
    public function addcomment_action() {
        global $COURSE, $PAGE;

        //ajax request
        $isajaxrequest = optional_param('ajax', false, PARAM_BOOL);

        try {
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

            //commentloop
            $commentloop = $gradeareainstance->get_commentloop();

            if (!$commentloop->user_can_comment()) {
                throw new moodle_exception('nopermissiontocomment', 'local_joulegrader');
            }

            //get the form
            $mform = $commentloop->get_mform();

            //check to see that form was submitted
            if ($data = $mform->get_data()) {
                //add the comment to the comment loop
                $comment = $commentloop->add_comment($data);
            }

            if (!$isajaxrequest) {
                redirect(new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentareaid, 'guser' => $currentuserid)));
            } else {
                $renderer = $PAGE->get_renderer('local_joulegrader');
                $commenthtml = $renderer->render($comment);

                $commentinfo = new stdClass();
                $commentinfo->html = $commenthtml;

                echo json_encode($commentinfo);
                die;
            }

        } catch (Exception $e) {
            if (!$isajaxrequest) {
                //rethrow the exception, let moodle handle it
                throw $e;
            } else {
                //need more delicate handling since it's an ajax request
                $error = new stdClass();
                $error->error = $e->getMessage();

                echo json_encode($error);
                die;
            }
        }
    }

    /**
     * Action used by joule gradebook to load comment loop modal
     */
    public function viewcommentloop_action() {
        global $PAGE;

        $PAGE->set_heading('');

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

        //commentloop
        $commentloop = $gradeareainstance->get_commentloop();

        $renderer = $PAGE->get_renderer('local_joulegrader');
        //generate loop html
        $commentloophtml = $renderer->render($commentloop);

        return $commentloophtml;

    }
}