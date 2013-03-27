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
                break;
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

        $fullscreen = get_user_preferences('local_joulegrader_fullscreen', 1);
        $fullscreenchg = optional_param('fullscreen', -1, PARAM_INT);
        if ($fullscreenchg != -1) {
            set_user_preference('local_joulegrader_fullscreen', $fullscreenchg);
            $fullscreen = $fullscreenchg;
        }

        $layout = 'standard';
        if (!empty($fullscreen)) {
            $layout = 'embedded';
        }

        // set layout to include blocks
        switch ($this->action) {
            case 'view':
            case 'process':
                $PAGE->set_pagelayout($layout);
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
        global $OUTPUT, $PAGE;

        //check for mobile browsers (currently not supported)
        if (get_device_type() == 'mobile') {
            //just return a message that mobile devices are not currently supported
            return $OUTPUT->container(html_writer::tag('h2', get_string('mobilenotsupported', 'local_joulegrader')), null, 'local-joulegrader-mobilenotsupportedmsg');
        }

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

        $buttons = $this->helper->navigation->get_navigation_buttons($this->url, $this->get_context());

        $menunav = $OUTPUT->container($activitynav . $usernav, 'content');
        $buttonnavcon = $OUTPUT->container($buttons, 'yui3-u-1-3', 'local-joulegrader-buttonnav');
        $activitynavcon = $OUTPUT->container($menunav, 'yui3-u-2-3', 'local-joulegrader-menunav');

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
        $output = $OUTPUT->container($buttonnavcon . $activitynavcon, 'yui3-u-1', 'local-joulegrader-navigation');

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
        global $CFG;
        require_once($CFG->dirroot . '/local/joulegrader/form/grademodalform.php');
        require_once($CFG->dirroot . '/local/joulegrader/form/gradepaneform.php');

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

        $gradepane = $gradeareainstance->get_gradepane();
        $modalform = null;
        if ($gradepane->has_modal()) {
            $modalform = $gradepane->get_modalform();
        }

        $paneform = null;
        if ($gradepane->has_paneform()) {
            $paneform = $gradepane->get_paneform();
        }

        if ((!empty($modalform) && $modalform->is_submitted() && !$modalform->is_validated())
                || (!empty($paneform) && $paneform->is_submitted() && !$paneform->is_validated())) {
            $this->gradeareainstance = $gradeareainstance;
            $this->print_header();
            echo $this->view_action();
            $this->print_footer();
            die;
        }

        if (!empty($modalform) && $modalform->is_submitted()) {
            $formdata = $modalform->get_data();
        } else if (!empty($paneform) && $paneform->is_submitted()) {
            $formdata = $paneform->get_data();
        }
        //fire off the process method of the grade pane, it should redirect or throw error
        $gradeareainstance->get_gradepane()->process($formdata, $this->notify);

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

                // @var local_joulegrader_helper_gradingareas $gareashelper
                $gareashelper = $this->helper->gradingareas;
                $gareainstance = $gareashelper::get_gradingarea_instance($comment->get_gareaid(), $comment->get_guserid());

                // get the comment loop comments and render comments
                $comments = $gareainstance->get_commentloop()->get_comments();
                $commenthtml = '';
                foreach ($comments as $comment) {
                    $commenthtml .= $renderer->render($comment);
                }

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
                $newcomment = $commentloop->add_comment($data);
            }

            if (!$isajaxrequest) {
                redirect(new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentareaid, 'guser' => $currentuserid)));
            } else {
                $renderer = $PAGE->get_renderer('local_joulegrader');

                // render just the comments again
                $commenthtml = '';
                $comments = $commentloop->get_comments();
                foreach ($comments as $comment) {
                    $commenthtml .= $renderer->render($comment);
                }

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

    /**
     * Downloads all the files for file submission plugin in assignment 2.3
     *
     * @return void
     */
    public function downloadall_action() {
        global $CFG, $USER, $DB, $COURSE;

        require_once($CFG->libdir . '/filelib.php');

        $submissionid = required_param('s', PARAM_INT);

        $submission = $DB->get_record('assign_submission', array('id' => $submissionid), '*', MUST_EXIST);
        $assign = $DB->get_record('assign', array('id' => $submission->assignment), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('assign', $assign->id, 0, false, MUST_EXIST);

        $context = context_module::instance($cm->id);

        // Enforce permissions.
        $this->enforce_submission_access($assign, $submission, $context, $cm);

        if (!empty($assign->teamsubmission) && $submission->userid == 0) {
            // Team submissions is being used, find out what the group is called.
            if ($submission->groupid == 0) {
                // This is the "Default" group.
                $uniquename = get_string('defaultteam', 'assign');
            } else {
                $group = groups_get_group($submission->groupid, 'name');
                $uniquename = $group->name;
            }
            $uniqueid = $submission->groupid;
        } else {

            // No team submissions being used.
            if ($USER->id === $submission->userid) {
                $user = $USER;
            } else {
                $user = $DB->get_record('user', array('id' => $submission->userid), 'id, firstname, lastname', MUST_EXIST);
            }

            if (!empty($assign->blindmarking) && empty($assign->revealidentities)) {
                require_once($CFG->dirroot . '/mod/assign/locallib.php');
                $uniqueid = assign::get_uniqueid_for_user_static($assign->id, $user->id);
                $uniquename = get_string('hiddenuser', 'assign') . $uniqueid;

            } else {
                $uniqueid = $user->id;
                $uniquename = fullname($user);
            }
        }

        // Make the filename.
        $filename = str_replace(' ', '_', clean_filename($COURSE->shortname.'-'.$assign->name.'-'.$cm->id.'-' . $uniquename .'-'.$uniqueid .".zip"));

        // Get the files
        $filestozip = array();

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'assignsubmission_file', 'submission_files', $submission->id, "timemodified", false);

        foreach ($files as $file) {
            $filestozip[$file->get_filename()] = $file;
        }

        $zipsuccess = false;
        if (!empty($filestozip)) {
            //create path for new zip file.
            $tempzip = tempnam($CFG->tempdir.'/', 'local_joulegrader_');
            //zip files
            $zipper = new zip_packer();
            $zipsuccess = $zipper->archive_to_pathname($filestozip, $tempzip);
        }

        if ($zipsuccess) {
            send_temp_file($tempzip, $filename);
        }
    }

    public function inlinefile_action() {
        global $CFG, $DB, $PAGE;
        require_once($CFG->libdir . '/filelib.php');

        $result = array();
        $filepathhash = required_param('f', PARAM_BASE64);

        try {
            // Try to get the file.
            $fs = get_file_storage();
            if (!$file = $fs->get_file_by_hash($filepathhash)) {
                throw new Exception();
            }

            // Get the item id - this should be the assign_submission id
            $itemid = $file->get_itemid();

            if (empty($itemid) || (!$submission = $DB->get_record('assign_submission', array('id' => $itemid)))) {
                throw new Exception();
            }

            // Get the assign record and course_module record
            $assign = $DB->get_record('assign', array('id' => $submission->assignment), '*', MUST_EXIST);
            $cm = get_coursemodule_from_instance('assign', $assign->id, 0, false, MUST_EXIST);

            $context = context_module::instance($cm->id);

            // Enforce permissions.
            $this->enforce_submission_access($assign, $submission, $context, $cm);

            $renderer = $PAGE->get_renderer('local_joulegrader');
            $result['html'] = $renderer->help_render_assign23_file_inline($file);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (empty($msg)) {
                $msg = get_string('couldnotviewinline', 'local_joulegrader');
            }
            $result['error'] = $msg;
        }

        echo json_encode($result);
    }

    /**
     * @param $assign
     * @param $submission
     * @param $context
     * @param $cm
     * @throws moodle_exception
     */
    protected function enforce_submission_access($assign, $submission, $context, $cm) {
        global $CFG, $COURSE, $USER;

        if (!empty($assign->teamsubmission) && $submission->userid == 0) {
            if (has_capability('mod/assign:grade', $context)) {
                // No further checks necessary, return.
                return;
            }

            require_once($CFG->dirroot . '/mod/assign/locallib.php');

            // Enforce team submission permissions.
            // Need to see if the $USER is a member of the group.
            $assignobj = new assign($context, $cm, $COURSE);
            $assignobj->set_instance($assign);

            $groupmembers = $assignobj->get_submission_group_members($submission->groupid, true);
            $ismember = false;
            foreach ($groupmembers as $member) {
                if ($member->id == $USER->id) {
                    $ismember = true;
                    break;
                }
            }

            if ($ismember) {
                // Make sure they do have the proper capability.
                require_capability('mod/assign:submit', $context);
            } else {
                // Should not have access.
                throw new moodle_exception('nopermissions');
            }
        } else {
            // No team submissions being used.
            // Check permissions.
            if ($USER->id === $submission->userid) {
                require_capability('mod/assign:submit', $context);
            } else {
                require_capability('mod/assign:grade', $context);
            }
        }
    }
}