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
 * Controller
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_joulegrader\utility\gradingareas;
use local_joulegrader\utility\users;
use local_joulegrader\utility\groups;
use local_joulegrader\utility\navigation;

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
        if (!has_capability('local/joulegrader:grade', $this->get_context())) {
            require_capability('local/joulegrader:view', $this->get_context());
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
        $PAGE->navbar->add('Open Grader');

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
        global $OUTPUT, $PAGE, $COURSE, $USER, $DB;

        //check for mobile browsers (currently not supported)
        if (core_useragent::get_device_type() == 'mobile') {
            //just return a message that mobile devices are not currently supported
            return $OUTPUT->container(html_writer::tag('h2', get_string('mobilenotsupported', 'local_joulegrader')), null, 'local-joulegrader-mobilenotsupportedmsg');
        }

        $gareaparam = optional_param('garea', 0, PARAM_INT);
        $guserparam = optional_param('guser', 0, PARAM_INT);
        $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);

        $groupsutility = new groups($this->get_context());
        /** @var gradingareas $gareasutility */
        $gareasutility = new gradingareas($this->get_context(), $gareaparam, $needsgrading, $groupsutility);

        /** @var users $usersutility */
        $usersutility = new users($gareasutility, $this->get_context(), $guserparam, $groupsutility);

        $currentareaid = $gareasutility->get_current();
        $currentuserid = $usersutility->get_current();

        //initialize the navigation
        $navutil = new navigation($usersutility, $gareasutility);

        // If the current user id and the current area id are not empty, load the class and get the pane contents.
        /** @var local_joulegrader_renderer $renderer */
        $renderer = $PAGE->get_renderer('local_joulegrader');
        if (!empty($currentareaid) && !empty($currentuserid)) {
            // Load the current area instance.
            if (!isset($this->gradeareainstance)) {
                $gradeareainstance = $gareasutility::get_gradingarea_instance($currentareaid, $currentuserid);
                $gradeareainstance->current_user($usersutility);
            } else {
                $gradeareainstance = $this->gradeareainstance;
            }

            $context = $gradeareainstance->get_gradingmanager()->get_context();
            $context = context::instance_by_id($context->id);
            $cm = $context->instanceid;

            $preferences = new mr_preferences($COURSE->id, 'local_joulegrader');
            $preferences->set('previousarea', $currentareaid);

            //set user id for "save and next" button
            $gradeareainstance->set_nextuserid($usersutility->get_next());

            $viewhtml = $renderer->render($gradeareainstance->get_viewpane());
            $gradehtml = $renderer->render($gradeareainstance->get_gradepane());

            $gradinglegend = html_writer::tag('legend', get_string('grading', 'local_joulegrader'));
            $gradehtml = html_writer::tag('fieldset', $gradinglegend . $gradehtml, array('class' => 'fieldset'));

            // Get the comment loop for the gradingarea
            $commentloophtml = '';
            if ($gradeareainstance->has_comments()) {
                $commentloophtml = $renderer->render($gradeareainstance->get_commentloop());
            }

            // Hook into grading area to allow it to change current user.
            $gradeareainstance->current_navuser($navutil);

            //get the view pane contents
            $viewpane = '<div class="content">' . $viewhtml . '</div>';

            // Resize bar used by drag and drop.
            $rs = html_writer::tag('div', "\t", array('id' => 'local-joulegrader-resize'));

            //get the grade pane contents
            $gradepane = '<div class="content">' . $rs . $gradehtml . $commentloophtml . '</div>';

            $panescontainer = $OUTPUT->container($viewpane, 'yui3-u-2-3', 'local-joulegrader-viewpane');
            $panescontainer .= $OUTPUT->container($gradepane, 'yui3-u-1-3', 'local-joulegrader-gradepane');

            $PAGE->requires->js_init_call('M.local_joulegrader.init_resize', null, true, $renderer->get_js_module());
        } else {
            $panescontainer = $OUTPUT->container(html_writer::tag('h1', get_string('nothingtodisplay', 'local_joulegrader')), 'content');
        }

        //activity navigation
        $activitynav = $navutil->get_activity_navigation();
        $activitynav = $OUTPUT->container($activitynav, null, 'local-joulegrader-activitynav');

        //user navigation
        $usernav = $navutil->get_users_navigation();
        $usernav = $OUTPUT->container($usernav, null, 'local-joulegrader-usernav');

        $buttonbaseurl = clone $this->url;
        $buttonbaseurl->params(array('guser' => $usersutility->get_current(), 'garea' => $currentareaid));
        $buttons = $navutil->get_navigation_buttons($buttonbaseurl, $this->get_context());

        $menunav = $OUTPUT->container($activitynav . $usernav, 'content');
        $buttonnavcon = $OUTPUT->container($buttons, 'yui3-u-1-3', 'local-joulegrader-buttonnav');
        $activitynavcon = $OUTPUT->container($menunav, 'yui3-u-2-3', 'local-joulegrader-menunav');

        //navigation container
        $output = $OUTPUT->container($buttonnavcon . $activitynavcon, 'yui3-u-1', 'local-joulegrader-navigation');

        //panes container
        $output .= $OUTPUT->container($panescontainer, 'yui3-u-1', 'local-joulegrader-panes');

        // Dummy panes used for calculations.
        $output .= $renderer->help_render_dummygrids();

        //wrap it all up
        $output = $OUTPUT->container($output, 'yui3-g', 'local-joulegrader');

        // Log an event.
        if (empty($context)) {
            $context = context_course::instance($COURSE->id);
        }
        $event = \local_joulegrader\event\grader_viewed::create(array(
            'other' => array(
                'areaid' => $currentareaid
            ),
            'relateduserid' => $currentuserid,
            'context' => $context
        ));
        $event->trigger();

        //return all of that
        return $output;
    }

    /**
     * Process action - processes grade form and redirects
     *
     * @throws moodle_exception
     * @return void
     */
    public function process_action() {
        global $CFG, $COURSE;

        //get current area id and current user parameters for the gradingarea instance
        $currentareaid = required_param('garea', PARAM_INT);
        $currentuserid = required_param('guser', PARAM_INT);

        $groupsutility = new groups($this->get_context());
        /** @var gradingareas $gareasutility */
        $gareasutility = new gradingareas($this->get_context(), $currentareaid, 0, $groupsutility);

        //make sure that the area passed from the form matches what is determined by the areas utility
        if ($currentareaid != $gareasutility->get_current()) {
            //should not get here unless ppl are messing with form data
            throw new moodle_exception('areaidpassednotvalid', 'local_joulegrader');
        }

        //just need prime the utility for the currentuser() and nextuser() calls
        $usersutility = new users($gareasutility, $this->get_context(), $currentuserid, $groupsutility);

        //make sure the passed user and passed area match what is available
        if ($currentuserid != $usersutility->get_current()) {
            //there is some funny business going on here
            throw new moodle_exception('useridpassednotvalid', 'local_joulegrader');
        }

        //load the current area instance
        $gradeareainstance = $gareasutility::get_gradingarea_instance($currentareaid, $currentuserid);

        //set next userid
        $gradeareainstance->set_nextuserid($usersutility->get_next());

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

        $context = $gradeareainstance->get_gradingmanager()->get_context();
        $context = context::instance_by_id($context->id);
        $cm = $context->instanceid;

        // Log an event.
        $event = \local_joulegrader\event\activity_graded::create(array(
            'other' => array(
                'areaid' => $currentareaid
            ),
            'relateduserid' => $currentuserid,
            'context' => $context
        ));
        $event->trigger();

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
        global $CFG, $COURSE, $PAGE;

        require_once($CFG->dirroot . '/grade/grading/lib.php');

        // Ajax request?
        $isajaxrequest = optional_param('ajax', false, PARAM_BOOL);

        try {
            // Required param for commentid.
            $commentid = required_param('commentid', PARAM_INT);

            // Get current area id and current user parameters for the gradingarea instance.
            $currentareaid = required_param('garea', PARAM_INT);
            $currentuserid = required_param('guser', PARAM_INT);

            // Require sesskey.
            require_sesskey();

            // Load the current area instance.
            $gradeareainstance = gradingareas::get_gradingarea_instance($currentareaid, $currentuserid);

            /**
             * @var local_joulegrader\comment_loop $commentloop
             */
            $commentloop = $gradeareainstance->get_commentloop();
            $commentloop->init();

            //check to make sure that the logged in user can delete
            if ($commentloop->user_can_delete($commentid)) {

                //yes we can delete, delete the comment
                $commentloop->delete_comment($commentid);

                $context = $gradeareainstance->get_gradingmanager()->get_context();
                $context = context::instance_by_id($context->id);
                $cm = $context->instanceid;

                // Log an event.
                $event = \local_joulegrader\event\comment_deleted::create(array(
                    'other' => array(
                        'areaid' => $currentareaid
                    ),
                    'relateduserid' => $currentuserid,
                    'context' => $context
                ));
                $event->trigger();
            }

            if (!$isajaxrequest) {
                redirect(new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'garea' => $currentareaid, 'guser' => $currentuserid)));
            } else {
                $renderer = $PAGE->get_renderer('local_joulegrader');


                // get the comment loop comments and render comments
                $comments = $commentloop->get_comments();
                $commenthtml = '<div>';
                foreach ($comments as $comment) {
                    $commenthtml .= $renderer->render($comment);
                }
                $commenthtml .= '</div>';
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

            //load the current area instance
            $gradeareainstance = gradingareas::get_gradingarea_instance($currentareaid, $currentuserid);

            /**
             * @var \local_joulegrader\comment_loop $commentloop
             */
            $commentloop = $gradeareainstance->get_commentloop();
            $commentloop->init();

            if (!$commentloop->user_can_comment()) {
                throw new moodle_exception('nopermissiontocomment', 'local_joulegrader');
            }

            //get the form
            $mform = $commentloop->get_mform();

            //check to see that form was submitted
            if ($data = $mform->get_data()) {
                //add the comment to the comment loop
                $commentloop->add_comment($data);

                $context = $gradeareainstance->get_gradingmanager()->get_context();
                $context = context::instance_by_id($context->id);
                $cm = $context->instanceid;

                // Log an event.
                $event = \local_joulegrader\event\comment_added::create(array(
                    'other' => array(
                        'areaid' => $currentareaid
                    ),
                    'relateduserid' => $currentuserid,
                    'context' => $context
                ));
                $event->trigger();
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

        $groupsutility = new groups($this->get_context());
        /** @var gradingareas $gareasutility */
        $gareasutility = new gradingareas($this->get_context(), $currentareaid, 0, $groupsutility);

        // Make sure that the area passed from the form matches what is determined by the areas utility.
        if ($currentareaid != $gareasutility->get_current()) {
            //should not get here unless ppl are messing with form data
            throw new moodle_exception('areaidpassednotvalid', 'local_joulegrader');
        }

        // Pull out the users utility and gradingareas utility.
        $usersutility = new users($gareasutility, $this->get_context(), $currentuserid, $groupsutility);

        //make sure the passed user and passed area match what is available
        if ($currentuserid != $usersutility->get_current()) {
            //there is some funny business going on here
            throw new moodle_exception('useridpassednotvalid', 'local_joulegrader');
        }

        //load the current area instance
        $gradeareainstance = $gareasutility::get_gradingarea_instance($currentareaid, $currentuserid);

        //commentloop
        $commentloop = $gradeareainstance->get_commentloop();
        $commentloop->init();

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
                $user = $DB->get_record('user', array('id' => $submission->userid), 'id, firstname, lastname, alternatename,
                    middlename, lastnamephonetic, firstnamephonetic', MUST_EXIST);
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
