<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/grade/abstract.php');
/**
 * joule Grader mod_assign_submissions grade pane class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_grade_mod_assign_submissions_class extends  local_joulegrader_lib_pane_grade_abstract {

    /**
     * @var array
     */
    protected $feedbackplugins;

    /**
     * @var array
     */
    protected $usergrades;

    /**
     * Do some initialization
     */
    public function init() {
        global $USER;

        $assignment = $this->gradingarea->get_assign();
        $usergrade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber());

        $this->courseid = $assignment->get_instance()->course;

        $this->gradinginfo = grade_get_grades($this->courseid, 'mod', 'assign', $assignment->get_instance()->id, array($this->gradingarea->get_guserid()));

        $this->gradingdisabled = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->locked;
        $this->gradeoverridden = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->overridden;

        if (($gradingmethod = $this->gradingarea->get_active_gradingmethod()) && in_array($gradingmethod, self::get_supportedplugins())) {
            $controller = $this->gradingarea->get_gradingmanager()->get_controller($gradingmethod);
            $this->controller = $controller;
            if ($controller->is_form_available()) {
                $itemid = null;
                if (!empty($usergrade->id)) {
                    $itemid = $usergrade->id;
                }
                if ($this->gradingdisabled && $itemid) {
                    $this->gradinginstance = $controller->get_current_instance($USER->id, $itemid);
                } else if (!$this->gradingdisabled) {
                    $instanceid = optional_param('gradinginstanceid', 0, PARAM_INT);
                    $this->gradinginstance = $controller->get_or_create_instance($instanceid, $USER->id, $itemid);
                }

                $currentinstance = null;
                if (!empty($this->gradinginstance)) {
                    $currentinstance = $this->gradinginstance->get_current_instance();
                }
                $this->needsupdate = false;
                if (!empty($currentinstance) && $currentinstance->get_status() == gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
                    $this->needsupdate = true;
                }
            } else {
                $this->advancedgradingerror = $controller->form_unavailable_notification();
            }
        }

        $this->teachercap = has_capability($this->gradingarea->get_teachercapability(), $this->gradingarea->get_gradingmanager()->get_context());
    }

    /**
     * @return bool
     */
    public function has_grading() {
        $hasgrading = true;
        $assignment = $this->gradingarea->get_assign();

        if ($assignment->get_instance()->grade == 0) {
            $hasgrading = false;
        }

        return $hasgrading;
    }

    public function has_teachercap() {
        return $this->teachercap;
    }

    public function has_modal() {
        return !(empty($this->controller) || empty($this->gradinginstance) || (!empty($this->controller) && !$this->controller->is_form_available()));
    }

    public function get_needsupdate() {
        return $this->needsupdate;
    }

    public function get_courseid() {
        return $this->courseid;
    }

    public function get_grade() {
        $assignment = $this->gradingarea->get_assign();
        return $assignment->get_instance()->grade;
    }

    public function has_override() {
        return !$this->gradingdisabled && $this->gradeoverridden;
    }

    public function get_currentgrade() {
        $usergrade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber());

        $grade = -1;
        if (!empty($usergrade) && isset($usergrade->grade)) {
            $grade = $usergrade->grade;
        }

        return $grade;
    }

    public function get_activity_grade() {
        return $this->get_currentgrade();
    }

    public function get_activity_grade_label() {
        return get_string('attemptgrade', 'local_joulegrader');
    }

    public function format_gradevalue($grade) {
        $gradeitem = grade_item::fetch(array('itemtype'=> 'mod', 'itemmodule'=> 'assign',
                'iteminstance'=> $this->gradingarea->get_assign()->get_instance()->id, 'courseid'=> $this->courseid,
                'outcomeid' => null));

        $decimals = $gradeitem->get_decimals();
        $formattedgrade = format_float($grade, $decimals, true);

        return $formattedgrade;
    }

    public function has_paneform() {
        return (empty($this->controller) || empty($this->gradinginstance) || (!empty($this->controller) && !$this->controller->is_form_available()));
    }

    public function has_active_gradinginstances() {
        $usergrade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber());
        return !(empty($usergrade) || !$this->controller->get_active_instances($usergrade->id));
    }

    public function get_agitemid() {
        $agitem = null;
        $usergrade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber());

        if (!empty($usergrade) && isset($usergrade->id)) {
            $agitem = $usergrade->id;
        }
        return $agitem;
    }

    /**
     * @return bool
     */
    public function has_file_feedback() {
        return $this->has_feedback_type('file');
    }

    /**
     * @return bool
     */
    public function has_overall_feedback() {
        return $this->has_feedback_type('comments');
    }

    /**
     * Returns the formatted overall feedback from the assign_feedback_comments plugin.
     * For use with the student view.
     *
     * @return string
     */
    public function get_overall_feedback() {
        $feedback = '';
        if ($this->has_overall_feedback()) {
            if ($grade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber())) {
                $feedback = $this->get_feedbackcomment_plugin()->view($grade);
            }
        }

        return $feedback;
    }

    /**
     * Conditionally adds the feedback form element to the form.
     *
     * @param MoodleQuickForm $mform
     */
    public function add_feedback_form($mform) {
        if ($this->has_overall_feedback()) {
            $editor = $mform->addElement('editor', 'assignfeedbackcomments_editor',
                get_string('overallfeedback', 'local_joulegrader') . ': ', null, null);
            $mform->setType('assignfeedbackcomments_editor', PARAM_RAW);

            if ($grade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber())) {
                $feedbackcomments = $this->get_feedbackcomment_plugin()->get_feedback_comments($grade->id);
                if ($feedbackcomments) {
                    $data = array();
                    $data['text'] = $feedbackcomments->commenttext;
                    $data['format'] = $feedbackcomments->commentformat;

                    $editor->setValue($data);
                }
            }
        }
    }

    /**
     * Conditionally adds the file feedback form element to the form.
     *
     * @param MoodleQuickForm $mform
     */
    public function add_filefeedback_form($mform) {
        if ($this->has_file_feedback()) {
            $userid = $this->gradingarea->get_guserid();
            $data = new stdClass();
            $this->get_feedbackfile_plugin()->get_form_elements_for_user($this->get_usergrade($userid, false, $this->gradingarea->get_attemptnumber()), $mform, $data, $userid);
            $elementname = 'files_' . $userid . '_filemanager';
            $mform->setDefault($elementname, $data->$elementname);
            $mform->getElement($elementname)->setLabel(html_writer::tag('div', get_string('filefeedback', 'local_joulegrader') . ': '));
        }
    }

    /**
     * Returns the formatted file feedback from the assign_feedback_file plugin.
     * For use with the student view.
     *
     * @return string
     */
    public function get_file_feedback() {
        $feedback = '';
        if ($this->has_file_feedback()) {
            if ($grade = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber())) {
                $feedback = $this->get_feedbackfile_plugin()->view($grade);
            }
        }

        return $feedback;
    }

    /**
     * @return bool
     */
    public function not_graded() {
        $notgraded = false;

        $assignment = $this->gradingarea->get_assign();
        $usergrade  = $this->get_usergrade($this->gradingarea->get_guserid(), false, $this->gradingarea->get_attemptnumber());

        if ($assignment->get_instance()->grade != 0) {
            //check the submission first
            if (!empty($usergrade) && $usergrade->grade == -1) {
                $notgraded = true;
            } else if (!empty($this->gradinginfo) && is_null($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->grade)) {
                //check the gradebook
                $notgraded = true;
            }
        }

        return $notgraded;
    }

    /**
     * @param MoodleQuickForm $mform
     */
    public function paneform_hook($mform) {
        $this->add_applyall_element($mform);
        $this->add_newattempt_element($mform);
        $this->blindmarking_modification($mform);
    }

    /**
     * @param MoodleQuickForm $mform
     */
    public function modalform_hook($mform) {
        $this->add_applyall_element($mform);
        $this->add_newattempt_element($mform);
        $this->blindmarking_modification($mform);
    }

    /**
     * @param MoodleQuickForm $mform
     */
    private function blindmarking_modification($mform) {
        // Check to see if the assignment uses blind marking.
        if ($this->gradingarea->get_assign()->is_blind_marking()) {
            // Check to see if the override element has been added to the form.
            if ($mform->elementExists('override')) {
                $mform->removeElement('override');
            }
        }
    }

    /**
     * @param MoodleQuickForm $mform
     */
    private function add_applyall_element($mform) {
        $assignment = $this->gradingarea->get_assign();
        $teamsubmission = $assignment->get_instance()->teamsubmission;
        if (!empty($teamsubmission)) {
            $mform->addElement('select', 'applytoall', get_string('applytoall', 'local_joulegrader'), array(get_string('no'), get_string('yes')));
            $mform->setDefault('applytoall', 1);
            $mform->setType('applytoall', PARAM_BOOL);
            $mform->addHelpButton('applytoall', 'applytoall', 'local_joulegrader');

            // Check to see if the override element has been added to the form.
            if ($mform->elementExists('override')) {
                // Disable override if "Apply grade and feedback to all" is set to yes.
                $mform->disabledIf('override', 'applytoall', 'eq', 1);
            }
        }
    }

    /**
     * @param MoodleQuickForm $mform
     */
    private function add_newattempt_element($mform) {
        if ($this->gradingarea->allow_new_manualattempt()) {
            $mform->addElement('selectyesno', 'addattempt', get_string('addattempt', 'assign'));
            $mform->setDefault('addattempt', 0);
        }
    }

    /**
     * @param $data
     * @param mr_notify $notify
     */
    public function process($data, $notify) {
        //a little setup
        $assignment = $this->gradingarea->get_assign();

        //set up a redirect url
        $redirecturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $this->courseid
                , 'garea' => $this->get_gradingarea()->get_areaid(), 'guser' => $this->get_gradingarea()->get_guserid()));

        //get the data from the form
        if ($data) {

            if (!isset($data->gradinginstanceid)) {
                if ($assignment->get_instance()->grade < 0) {
                    // Scale grade.
                    $data->grade = clean_param($data->grade, PARAM_INT);
                } else {
                    //just using regular grading
                    $lettergrades = grade_get_letters(context_course::instance($this->courseid));
                    $grade = $data->grade;

                    // Determine if user is submitting as a letter grade, percentage or float.
                    $touppergrade = textlib::strtoupper($grade);
                    $toupperlettergrades = array_map('textlib::strtoupper', $lettergrades);
                    if (in_array($touppergrade, $toupperlettergrades)) {
                        // Submitting lettergrade, find percent grade.
                        $percentvalue = 0;
                        $max = 100;
                        foreach ($toupperlettergrades as $value => $letter) {
                            if ($touppergrade == $letter) {
                                $percentvalue = ($max + $value) / 2;
                                break;
                            }
                            $max = $value - 1;
                        }

                        // Transform to a float within the range of the assignment.
                        $data->grade = (float) ($assignment->get_instance()->grade * ($percentvalue / 100));

                    } else if (strpos($grade, '%') !== false) {
                        // Trying to submit percentage.
                        $percentgrade = trim(strstr($grade, '%', true));
                        $percentgrade = clean_param($percentgrade, PARAM_FLOAT);

                        // Transform to an integer within the range of the assignment.
                        $data->grade = (float) ($assignment->get_instance()->grade * ($percentgrade / 100));

                    } else if ($grade === '') {
                        // Setting to "No grade".
                        $data->grade = -1;
                    } else {
                        // Just a numeric value, clean it as float b/c that's what assign module accepts.
                        $data->grade = clean_param($grade, PARAM_FLOAT);
                    }
                }
            }

            $userid = $this->get_gradingarea()->get_guserid();
            $teamsubmission = $assignment->get_instance()->teamsubmission;
            if (!empty($teamsubmission) && !empty($data->applytoall)) {
                $groupid = 0;
                if ($assignment->get_submission_group($userid)) {
                    $group = $assignment->get_submission_group($userid);
                    if ($group) {
                        $groupid = $group->id;
                    }
                }

                $gradessaved = 0;
                $couldnotsave = array();
                $members = $assignment->get_submission_group_members($groupid, true);
                foreach ($members as $member) {
                    // User may exist in multiple groups (which should put them in the default group).
                    if ($this->apply_grade_to_user($data, $member->id)) {
                        $gradessaved++;
                    } else {
                        $couldnotsave[] = $member->id;
                    }
                }

                // Set notifications for grades saves/failures.
                $this->set_notifications($notify, $gradessaved, $couldnotsave);
            } else {
                if ($this->apply_grade_to_user($data, $userid)) {
                    $notify->good('gradesaved');
                } else {
                    $notify->bad('couldnotsave');
                }
            }

            if ($this->should_reopen_attempt($data)) {
                $this->process_add_attempt($userid);
            }

            //redirect to next user if set
            if (optional_param('saveandnext', 0, PARAM_BOOL) && !empty($data->nextuser)) {
                $redirecturl->param('guser', $data->nextuser);
            }

            if (optional_param('needsgrading', 0, PARAM_BOOL)) {
                $redirecturl->param('needsgrading', 1);
            }
        }

        redirect($redirecturl);
    }

    /**
     * @return bool
     */
    public function read_only() {
        $readonly = false;
        $attemptnumber = $this->gradingarea->get_attemptnumber();
        if ($attemptnumber != -1) {
            if ($submissions = $this->gradingarea->get_all_submissions()) {
                $mostrecent = end($submissions);
                $readonly = ($mostrecent->attemptnumber != $attemptnumber);
            }
        }

        return $readonly;
    }

    protected function apply_grade_to_user($data, $userid) {
        global $USER;

        /** @var assign $assignment */
        $assignment = $this->gradingarea->get_assign();
        $usergrade = $this->get_usergrade($userid, true, $this->gradingarea->get_attemptnumber());

        $teamsubmission = $assignment->get_instance()->teamsubmission;

        if (isset($data->gradinginstanceid)) {
            $gradesmenu = make_grades_menu($assignment->get_instance()->grade);
            // Using advanced grading.
            if ($userid == $this->gradingarea->get_guserid()) {
                // Can use the grading instance already set up by this object.
                $gradinginstance = $this->gradinginstance;
            } else {
                $gradingdisabled = $assignment->grading_disabled($userid);
                $itemid = null;
                if ($usergrade) {
                    $itemid = $usergrade->id;
                }
                if ($gradingdisabled && $itemid) {
                    $gradinginstance = ($this->controller->get_current_instance($USER->id, $itemid));
                } else if (!$gradingdisabled) {
                    $instanceid = optional_param('gradinginstanceid', 0, PARAM_INT);
                    $gradinginstance = ($this->controller->get_or_create_instance($instanceid, $USER->id, $itemid));
                }
            }

            $allowgradedecimals = $assignment->get_instance()->grade > 0;
            $this->controller->set_grade_range($gradesmenu, $allowgradedecimals);
            $usergrade->grade = $gradinginstance->submit_and_get_grade($data->grade, $usergrade->id);
        } else {
            // The grade has already been processed in the process method.
            $usergrade->grade = $data->grade;
        }

        $override = false;
        $blindmarking = $assignment->is_blind_marking();
        if (!$blindmarking) {
            $override = isset($data->override) || (!empty($teamsubmission) && !empty($data->applytoall));
        }

        return $this->save_grade($usergrade, $data, $override, $blindmarking);
    }

    /**
     * @param $usergrade
     * @param $data
     * @param bool $override
     * @param bool $blindmarking
     *
     * @return bool
     */
    protected function save_grade($usergrade, $data, $override, $blindmarking = false) {
        global $USER, $DB;

        // Need to do two things here.
        // 1) update the grade in assign_grades table.
        // 2) update the grade in the gradebook if the grade is NOT overridden
        //    or if it IS overridden AND the override check was checked.
        $assignment = $this->gradingarea->get_assign();

        $usergrade->grader = $USER->id;
        $usergrade->timemodified = time();

        // Update the submission.
        $success = $DB->update_record('assign_grades', $usergrade);

        if ($this->has_overall_feedback()) {
            $this->get_feedbackcomment_plugin()->save($usergrade, $data);
        }

        if ($this->has_file_feedback()) {
            $this->get_feedbackfile_plugin()->save($usergrade, $data);
        }

        // If blind marking is used don't upgrade the gradebook.
        if ($blindmarking)  {
            // Blind marking is being used and identities have not been revealed. Return here.
            return $success;
        }

        // Now need to update the gradebook.
        if ($usergrade->userid == $this->gradingarea->get_guserid()) {
            $gradinginfo = $this->gradinginfo;
        } else {
            $gradinginfo = grade_get_grades($this->get_courseid(), 'mod', 'assign', $assignment->get_instance()->id, array($usergrade->userid));
        }
        $gradeoverridden = $gradinginfo->items[0]->grades[$usergrade->userid]->overridden;

        if ($this->has_overall_feedback()) {
            $assignadmincfg = $this->gradingarea->get_assign()->get_admin_config();
            $gradebookplugin = $assignadmincfg->feedback_plugin_for_gradebook;

            if ('assignfeedback_' . $this->get_feedbackcomment_plugin()->get_type() == $gradebookplugin) {
                $usergrade->feedbacktext = $this->get_feedbackcomment_plugin()->text_for_gradebook($usergrade);
                $usergrade->feedbackformat = $this->get_feedbackcomment_plugin()->format_for_gradebook($usergrade);
            }
        }


        if ($success && !$gradeoverridden) {
            $gradebookgrade = $this->convert_grade_for_gradebook($usergrade);

            $assign = clone $assignment->get_instance();
            $assign->cmidnumber = $assignment->get_course_module()->id;

            $success = $success && (GRADE_UPDATE_OK == assign_grade_item_update($assign, $gradebookgrade));
        } else if ($success && $override) {
            // Try to fetch the gradeitem first.
            $params = array('courseid' => $this->courseid, 'itemtype' => 'mod'
            , 'itemmodule' => 'assign', 'iteminstance' => $assignment->get_instance()->id);

            $gradeitem = grade_item::fetch($params);

            // If no grade item, create a new one.
            if (empty($gradeitem)) {

                $params['itemname'] = $assignment->get_instance()->name;
                $params['idnumber'] = $assignment->get_course_module()->id;

                // Set up additional params for the grade item.
                if ($assignment->get_instance()->grade > 0) {
                    $params['gradetype'] = GRADE_TYPE_VALUE;
                    $params['grademax']  = $assignment->get_instance()->grade;
                    $params['grademin']  = 0;

                } else if ($assignment->assignment->grade < 0) {
                    $params['gradetype'] = GRADE_TYPE_SCALE;
                    $params['scaleid']   = -$assignment->get_instance()->grade;

                } else {
                    $params['gradetype'] = GRADE_TYPE_TEXT; // allow text comments only
                }

                // Create and insert the new grade item.
                $gradeitem = new grade_item($params);
                $gradeitem->insert();
            }

            // If grade is -1 in assign_grades table, it should be passed as null.
            $grade = $usergrade->grade;
            if ($grade == -1) {
                $grade = null;
            }

            $feedback = false;
            if (isset($usergrade->feedbacktext)) {
                $feedback = $usergrade->feedbacktext;
            }

            $feedbackformat = FORMAT_MOODLE;
            if (isset($usergrade->feedbackformat)) {
                $feedbackformat = $usergrade->feedbackformat;
            }

            $success = $success && (bool) $gradeitem->update_final_grade($usergrade->userid, $grade, 'local/joulegrader',
                    $feedback, $feedbackformat, $usergrade->grader);
        }

        return $success;
    }

    /**
     * @param mr_notify $notify
     * @param int $gradessaved
     * @param array $couldnotsave
     */
    protected function set_notifications($notify, $gradessaved, $couldnotsave) {
        global $DB;

        // Set notification for grades successfully updated.
        if ($gradessaved > 0) {
            $notify->good('gradesavedx', $gradessaved);
        }

        // Set nofifications for grades that were not successfully updated.
        if (!empty($couldnotsave)) {
            $assign = $this->gradingarea->get_assign();
            $blindmarking = $assign->is_blind_marking();

            foreach ($couldnotsave as $userid) {
                if ($blindmarking) {
                    $username = get_string('hiddenuser', 'assign') . $assign->get_uniqueid_for_user($userid);
                } else {
                    $user = $DB->get_record('user', array('id' => $userid), 'id, firstname, lastname');
                    $username = fullname($user);
                }

                $notify->bad('couldnotsavex', $username);
            }
        }
    }

    /**
     * @return assign_feedback_comments|null
     */
    protected function get_feedbackcomment_plugin() {
        return $this->get_feedback_plugin('comments');
    }

    /**
     * @return assign_feedback_file|null
     */
    protected function get_feedbackfile_plugin() {
        return $this->get_feedback_plugin('file');
    }

    /**
     * @param string $type
     * @return assign_feedback_plugin|null
     */
    protected function get_feedback_plugin($type) {
        if (!isset($this->feedbackplugins[$type])) {
            $this->feedbackplugins[$type] = $this->gradingarea->get_assign()->get_feedback_plugin_by_type($type);
        }
        return $this->feedbackplugins[$type];
    }

    /**
     * @param string $type
     * @return bool
     */
    protected function has_feedback_type($type) {
        $feedbackplugin = $this->get_feedback_plugin($type);
        return (!empty($feedbackplugin) && $feedbackplugin->is_enabled() && $feedbackplugin->is_visible());
    }

    /**
     * @param int $userid
     * @param bool $create
     * @param int $attemptnumber
     * @return bool|stdClass
     */
    private function get_usergrade($userid, $create = false, $attemptnumber = -1) {
        if (empty($this->usergrades[$userid])) {
            $this->usergrades[$userid] = array();
        }
        if (empty($this->usergrades[$userid][$attemptnumber])) {
            $this->usergrades[$userid][$attemptnumber] = $this->gradingarea->get_assign()->get_user_grade($userid, $create, $attemptnumber);
        }

        return $this->usergrades[$userid][$attemptnumber];
    }

    /**
     * convert the final raw grade(s) in the  grading table for the gradebook
     * (Taken from assign::convert_grade_for_gradebook, which is a private method
     *
     * @param stdClass $grade
     * @return array
     */
    private function convert_grade_for_gradebook(stdClass $grade) {
        $gradebookgrade = array();
        // trying to match those array keys in grade update function in gradelib.php
        // with keys in th database table assign_grades
        // starting around line 262
        if ($grade->grade >= 0) {
            $gradebookgrade['rawgrade'] = $grade->grade;
        }
        $gradebookgrade['userid'] = $grade->userid;
        $gradebookgrade['usermodified'] = $grade->grader;
        $gradebookgrade['datesubmitted'] = NULL;
        $gradebookgrade['dategraded'] = $grade->timemodified;
        if (isset($grade->feedbackformat)) {
            $gradebookgrade['feedbackformat'] = $grade->feedbackformat;
        }
        if (isset($grade->feedbacktext)) {
            $gradebookgrade['feedback'] = $grade->feedbacktext;
        }

        return $gradebookgrade;
    }

    /**
     * @param $formdata
     * @return bool
     */
    public function should_reopen_attempt($formdata) {

        if (!$this->gradingarea->allows_multiple_attempts()) {
            // Assignment doesn't allow reopening attempts.
            return false;
        }

        /** @var assign $assignment */
        $assignment = $this->gradingarea->get_assign();
        $instance   = $assignment->get_instance();

        if (($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL) and empty($formdata->addattempt)) {
            // Reopen method is manual and the user did NOT elect to add a new attempt.
            return false;
        }

        $submission = $this->gradingarea->get_submission();
        // Don't re-open a submission if there isn't one yet.
        if (empty($submission)) {
            return false;
        }

        $maxattemptsreached = !empty($submission) && ($instance->maxattempts != ASSIGN_UNLIMITED_ATTEMPTS) &&
            ($submission->attemptnumber >= ($instance->maxattempts - 1));

        if ($maxattemptsreached) {
            // Reached the max attempts allowed.
            return false;
        }

        // At this point we haven't reach max attempts, if the method was manual then the user did select to add a new attempt.
        if ($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL) {
            // Method is manual so we can reopen.
            return true;
        }

        // Reopen method is until passing grade.
        if ($instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS) {
            $userid = $this->gradingarea->get_guserid();
            // Check the gradetopass from the gradebook.
            $gradinginfo = grade_get_grades($assignment->get_course()->id,
                'mod',
                'assign',
                $instance->id,
                $userid);

            // What do we do if the grade has not been added to the gradebook (e.g. blind marking)?
            $gradingitem = null;
            $gradebookgrade = null;
            if (isset($gradinginfo->items[0])) {
                $gradingitem = $gradinginfo->items[0];
                /** @var grade_grade $gradebookgrade */
                $gradebookgrade = $gradingitem->grades[$userid];
            }

            if (!empty($gradebookgrade)) {
                // TODO: This code should call grade_grade->is_passed().
                $shouldreopen = true;
                if (is_null($gradebookgrade->grade)) {
                    $shouldreopen = false;
                }
                if (empty($gradingitem->gradepass) || $gradingitem->gradepass == $gradingitem->grademin) {
                    $shouldreopen = false;
                }
                if ($gradebookgrade->grade >= $gradingitem->gradepass) {
                    $shouldreopen = false;
                }
                return $shouldreopen;
            }
        }

        return false;
    }

    /**
     * Add a new attempt for a user.
     *
     * @param int $userid int The user to add the attempt for
     * @return bool - true if successful.
     */
    protected function process_add_attempt($userid) {
        /** @var assign $assignment */
        $assignment = $this->gradingarea->get_assign();
        $instance   = $assignment->get_instance();

        $submission = $this->gradingarea->get_submission();

        if (!$submission) {
            return false;
        }

        // Create the new submission record for the group/user.
        if ($instance->teamsubmission) {
            $submission = $assignment->get_group_submission($userid, 0, true, $submission->attemptnumber+1);
        } else {
            $submission = $assignment->get_user_submission($userid, true, $submission->attemptnumber+1);
        }

        // Set the status of the new attempt to reopened.
        $submission->status = ASSIGN_SUBMISSION_STATUS_REOPENED;
        $this->update_submission($submission, $userid, false, $instance->teamsubmission);
        return true;
    }

    /**
     * Update grades in the gradebook based on submission time. Modified from protected method in assign class.
     * In Joule Grader it is only used when adding a new attempt to the assignment.
     *
     * @param stdClass $submission
     * @param int $userid
     * @param bool $updatetime
     * @param bool $teamsubmission
     * @return bool
     */
    protected function update_submission(stdClass $submission, $userid, $updatetime, $teamsubmission) {
        global $DB;

        if ($teamsubmission) {
            return $this->update_team_submission($submission, $userid, $updatetime);
        }

        if ($updatetime) {
            $submission->timemodified = time();
        }
        $result = $DB->update_record('assign_submission', $submission);

        return $result;
    }

    /**
     * Update team submission.  Modified from protected method in assign class.
     * In Joule Grader it is only used when adding a new attempt to the assignment.
     *
     * @param stdClass $submission
     * @param int $userid
     * @param bool $updatetime
     * @return bool
     */
    protected function update_team_submission(stdClass $submission, $userid, $updatetime) {
        global $DB;

        /** @var assign $assignment */
        $assignment = $this->gradingarea->get_assign();

        if ($updatetime) {
            $submission->timemodified = time();
        }

        // First update the submission for the current user.
        $mysubmission = $assignment->get_user_submission($userid, true, $submission->attemptnumber);
        $mysubmission->status = $submission->status;

        $this->update_submission($mysubmission, 0, $updatetime, false);

        // Now check the team settings to see if this assignment qualifies as submitted or draft.
        $team = $assignment->get_submission_group_members($submission->groupid, true);

        $result = true;
        // Set the group submission to reopened.
        foreach ($team as $member) {
            $membersubmission = $assignment->get_user_submission($member->id, true, $submission->attemptnumber);
            $membersubmission->status = ASSIGN_SUBMISSION_STATUS_REOPENED;
            $result = $DB->update_record('assign_submission', $membersubmission) && $result;
        }
        $result = $DB->update_record('assign_submission', $submission) && $result;

        return $result;
    }
}
