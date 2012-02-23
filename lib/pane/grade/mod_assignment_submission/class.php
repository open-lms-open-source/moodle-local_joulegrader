<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/grade/abstract.php');
require_once($CFG->dirroot . '/local/joulegrader/form/mod_assignment_submission_grade.php');

/**
 * joule Grader mod_assignment_submission grade pane class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_grade_mod_assignment_submission_class extends local_joulegrader_lib_pane_grade_abstract {

    /**
     * Do some initialization
     */
    public function init() {
        global $USER;

        if (isset($this->mform) && isset($this->mformdata)) {
            return;
        }

        $mformdata = new stdClass();
        $mformdata->assignment = $assignment = $this->gradingarea->get_assignment();
        $mformdata->submission = $submission = $this->gradingarea->get_submission();

        $this->gradinginfo = grade_get_grades($assignment->course->id, 'mod', 'assignment', $assignment->assignment->id, array($this->gradingarea->get_guserid()));
        $mformdata->gradeoverridden = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->overridden;

        $gradingdisabled = $this->gradinginfo->items[0]->locked;

        if ($gradingmethod = $this->gradingarea->get_active_gradingmethod()) {
            $controller = $this->gradingarea->get_gradingmanager()->get_controller($gradingmethod);
            if ($controller->is_form_available()) {
                $itemid = null;
                if (!empty($submission->id)) {
                    $itemid = $submission->id;
                }
                if ($gradingdisabled && $itemid) {
                    $mformdata->gradinginstance = $controller->get_current_instance($USER->id, $itemid);
                } else if (!$gradingdisabled) {
                    $instanceid = optional_param('gradinginstanceid', 0, PARAM_INT);
                    $mformdata->gradinginstance = $controller->get_or_create_instance($instanceid, $USER->id, $itemid);
                }
            } else {
                $this->advancedgradingwarning = $controller->form_unavailable_notification();
            }
        }

        $posturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $assignment->course->id
                , 'garea' => $this->gradingarea->get_areaid(), 'guser' => $this->gradingarea->get_guserid(), 'action' => 'process'));

        //set mformdata
        $this->mformdata = $mformdata;

        //create the mform
        $this->mform = new local_joulegrader_form_mod_assignment_submission_grade($posturl, $mformdata);
    }

    /**
     * Process the grade data
     * @param $notify
     */
    public function process($notify) {
        $mform = $this->get_mform();
        $redirecturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $this->mformdata->assignment->course->id
                , 'garea' => $this->get_gradingarea()->get_areaid(), 'guser' => $this->get_gradingarea()->get_guserid()));

        if ($data = $mform->get_data()) {

            if ($data->assignment != $this->mformdata->assignment->assignment->id) {
                //throw an exception, could be some funny business going on here
                throw new moodle_exception('assignmentnotmatched', 'local_joulegrader');
            }

            if ($data->submission != $this->mformdata->submission->id) {
                //throw an exception, could be some funny business going on here
                throw new moodle_exception('submissionnotmatched', 'local_joulegrader');
            }

            if (isset($this->mformdata->gradinginstance)) {
                //using advanced grading
                $gradinginstance = $this->mformdata->gradinginstance;
                $grade = $gradinginstance->submit_and_get_grade($data->grade, $this->mformdata->submission->id);
            } else if ($this->mformdata->assignment->assignment->grade < 0) {
                $grade = clean_param($data->grade, PARAM_INT);
            } else {
                //just using regular grading
                $lettergrades = grade_get_letters(context_course::instance($this->mformdata->assignment->course->id));
                $grade = $data->grade;

                //determine if user is submitting as a letter grade, percentage or float
                if (in_array($grade, $lettergrades)) {
                    //submitting lettergrade, find percent grade
                    $percentvalue = 0;
                    foreach ($lettergrades as $value => $letter) {
                        if ($grade == $letter) {
                            $percentvalue = $value;
                            break;
                        }
                    }

                    //transform to an integer within the range of the assignment
                    $grade = (int) ($this->mformdata->assignment->assignment->grade * ($percentvalue / 100));

                } else if (strpos($grade, '%') !== false) {
                    //trying to submit percentage
                    $percentgrade = trim(strstr($grade, '%', true));
                    $percentgrade = clean_param($percentgrade, PARAM_FLOAT);

                    //transform to an integer within the range of the assignment
                    $grade = (int) ($this->mformdata->assignment->assignment->grade * ($percentgrade / 100));

                } else if ($grade === '') {
                    //setting to "No grade"
                    $grade = -1;
                } else {
                    //just a numeric value, clean it as int b/c that's what assignment module accepts
                    $grade = clean_param($grade, PARAM_INT);
                }
            }

            //save the grade
            if ($this->save_grade($grade, isset($data->override))) {
                $notify->good('gradesaved');
            }
        }

        redirect($redirecturl);
    }

    /**
     * @return bool
     */
    public function is_validated() {
        $validated = $this->mform->is_validated();
        return $validated;
    }

    /**
     * @param $grade
     * @param $override
     */
    protected function save_grade($grade, $override) {
        global $USER, $DB;

        $success = true;

        //need to do two things here
        //1) update the grade in assignment_submission table
        //2) update the grade in the gradebook if the grade is NOT overridden or if it IS overridden AND the override check
        //   was checked

        $submission = $this->mformdata->submission;
        $assignment = $this->mformdata->assignment;

        $submission->grade      = $grade;
        $submission->teacher    = $USER->id;
        $mailinfo = get_user_preferences('assignment_mailinfo', 0);
        if (!$mailinfo) {
            $submission->mailed = 1;       // treat as already mailed
        } else {
            $submission->mailed = 0;       // Make sure mail goes out (again, even)
        }
        $submission->timemarked = time();

        unset($submission->data1);  // Don't need to update this.
        unset($submission->data2);  // Don't need to update this.

        //update the submission
        $success = $success && (bool) $DB->update_record('assignment_submissions', $submission);

        //now need to update the gradebook
        if (!$this->mformdata->gradeoverridden) {
            $assignment->update_grade($submission);
        } else if ($override) {
            //try to fetch the gradeitem first
            $params = array('courseid' => $assignment->assignment->course, 'itemtype' => 'mod'
                    , 'itemmodule' => 'assignment', 'iteminstance' => $assignment->assignment->id);

            $gradeitem = grade_item::fetch($params);

            //if no grade item, create a new one
            if (empty($gradeitem)) {

                $params['itemname'] = $assignment->assignment->name;
                $params['idnumber'] = $assignment->assignment->cmidnumber;

                //set up additional params for the grade item
                if ($assignment->assignment->grade > 0) {
                    $params['gradetype'] = GRADE_TYPE_VALUE;
                    $params['grademax']  = $assignment->assignment->grade;
                    $params['grademin']  = 0;

                } else if ($assignment->assignment->grade < 0) {
                    $params['gradetype'] = GRADE_TYPE_SCALE;
                    $params['scaleid']   = -$assignment->assignment->grade;

                } else {
                    $params['gradetype'] = GRADE_TYPE_TEXT; // allow text comments only
                }

                //create and insert the new grade item
                $gradeitem = new grade_item($params);
                $gradeitem->insert();
            }

            //if grade is -1 in assignment_submissions table, it should be passed as null
            $grade = $submission->grade;
            if ($grade == -1) {
                $grade = null;
            }

            $success = $success && (bool) $gradeitem->update_final_grade($submission->userid, $grade, 'local/joulegrader', false, FORMAT_MOODLE, $submission->teacher);
        }

        return $success;
    }
}