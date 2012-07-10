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

        if (isset($this->mform)) {
            return;
        }

        $assignment = $this->gradingarea->get_assignment();
        $submission = $this->gradingarea->get_submission();

        $this->gradinginfo = grade_get_grades($assignment->course->id, 'mod', 'assignment', $assignment->assignment->id, array($this->gradingarea->get_guserid()));

        $gradingdisabled = $this->gradinginfo->items[0]->locked;

        if (($gradingmethod = $this->gradingarea->get_active_gradingmethod()) && in_array($gradingmethod, self::get_supportedplugins())) {
            $controller = $this->gradingarea->get_gradingmanager()->get_controller($gradingmethod);
            $this->controller = $controller;
            if ($controller->is_form_available()) {
                $itemid = null;
                if (!empty($submission->id)) {
                    $itemid = $submission->id;
                }
                if ($gradingdisabled && $itemid) {
                    $this->gradinginstance = $controller->get_current_instance($USER->id, $itemid);
                } else if (!$gradingdisabled) {
                    $instanceid = optional_param('gradinginstanceid', 0, PARAM_INT);
                    $this->gradinginstance = $controller->get_or_create_instance($instanceid, $USER->id, $itemid);
                }

                $currentinstance = $this->gradinginstance->get_current_instance();
                $this->needsupdate = false;
                if ($currentinstance && $currentinstance->get_status() == gradingform_instance::INSTANCE_STATUS_NEEDUPDATE) {
                    $this->needsupdate = true;
                }
            } else {
                $this->advancedgradingerror = $controller->form_unavailable_notification();
            }
        }


        $this->teachercap = has_capability($this->gradingarea->get_teachercapability(), $this->gradingarea->get_gradingmanager()->get_context());
        if ($this->teachercap) {
            //set up the form
            $mformdata = new stdClass();
            $mformdata->assignment = $assignment;
            $mformdata->submission = $submission;
            $mformdata->gradeoverridden = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->overridden;
            $mformdata->gradingdisabled = $gradingdisabled;

            //For advanced grading methods
            if (!empty($this->gradinginstance)) {
                $mformdata->gradinginstance = $this->gradinginstance;
            }

            $posturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $assignment->course->id
                    , 'garea' => $this->gradingarea->get_areaid(), 'guser' => $this->gradingarea->get_guserid(), 'action' => 'process'));

            if ($needsgrading = optional_param('needsgrading', 0, PARAM_BOOL)) {
                $posturl->param('needsgrading', 1);
            }

            $mformdata->nextuser = $this->gradingarea->get_nextuserid();

            //create the mform
            $this->mform = new local_joulegrader_form_mod_assignment_submission_grade($posturl, $mformdata);
        }
    }

    /**
     * @return mixed
     */
    public function get_panehtml() {
        //initialize
        $html = '';

        $assignment = $this->get_gradingarea()->get_assignment();
        //if this is an ungraded assignment just return a no grading info box
        if ($assignment->assignment->grade == 0) {
            //no grade for this assignment
            $html = html_writer::tag('div', get_string('notgraded', 'local_joulegrader'), array('class' => 'local_joulegrader_notgraded'));
        } else {
            //there is a grade for this assignment
            //check to see if advanced grading is being used
            if (empty($this->controller) || (!empty($this->controller) && !$this->controller->is_form_available())) {
                //advanced grading not used
                //check for cap
                if (!empty($this->teachercap)) {
                    //get the form html for the teacher
                    $mrhelper = new mr_helper();
                    $html = $mrhelper->buffer(array($this->mform, 'display'));
                    $html = html_writer::tag('div', $html, array('class' => 'local_joulegrader_simplegrading'));

                    //advanced grading error warning
                    if (!empty($this->controller) && !$this->controller->is_form_available()) {
                        $html .= $this->advancedgradingerror;
                    }
                } else {
                    //get student grade html

                    // initialize
                    $grade = -1;

                    if (!empty($this->gradinginfo->items[0]) and !empty($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()])
                        and !is_null($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->grade)) {
                        $grade = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->str_grade;
                    }

                    //start the html
                    $html = html_writer::start_tag('div', array('id' => 'local-joulegrader-gradepane-grade'));
                    if ($assignment->assignment->grade < 0) {
                        $html .= get_string('grade') . ': ';
                        if ($grade != -1) {
                            $html .= $grade;
                        } else {
                            $html .= get_string('nograde');
                        }
                    } else {
                        //if grade isn't set yet then, make is blank, instead of -1
                        if ($grade == -1) {
                            $grade = ' - ';
                        }
                        $html .= get_string('gradeoutof', 'local_joulegrader', $assignment->assignment->grade) . ': ';
                        $html .= $grade;
                    }
                    $html .= html_writer::end_tag('div');

                }
            } else if ($this->controller->is_form_available()) {
                //generate preview based on type of advanced grading plugin (rubric or checklist)
                $gradingmethod = $this->gradingarea->get_active_gradingmethod();

                // shouldn't have this happen, but just in case
                if (!in_array($gradingmethod, self::get_supportedplugins())) {
                    return '';
                }

                $html = '';
                if (!empty($this->teachercap) || !$this->needsupdate) {
                    $controller = $this->controller;
                    $options = $controller->get_options();
                    $grade = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()];
                    
                    if (!empty($options['alwaysshowdefinition']) || !empty($this->teachercap) || (!empty($grade->grade) && empty($grade->hidden))) {
                        //need to generate the condensed rubric html
                        //first a "view" button
                        //If the user has the ability to see the rubric!
                        $buttonatts = array('type' => 'button', 'id' => 'local-joulegrader-preview-button');
                        $viewbutton = html_writer::tag('button', get_string('view' . $gradingmethod, 'local_joulegrader'), $buttonatts);

                        $html .= html_writer::tag('div', $viewbutton, array('id' => 'local-joulegrader-viewpreview-button-con'));

                        // needsupdate?
                        if ($this->needsupdate) {
                            $html .= html_writer::tag('div', get_string('needregrademessage', 'gradingform_' . $gradingmethod), array('class' => "gradingform_$gradingmethod-regrade"));
                        }

                        //gradingmethod preview
                        $previewmethod = 'get_' . $gradingmethod . '_preview';
                        $html .= $this->$previewmethod();
                    }
                }

                if ((!$grade->grade === false) && empty($grade->hidden)) {
                    $gradeval = $grade->str_long_grade;
                } else {
                    $gradeval = '-';
                }
                $html .= '<div class="grade">'. get_string("grade").': '.$gradeval. '</div>';
            }
        }

        return $html;
    }

    /**
     * @return string - html for a modal
     */
    public function get_modal_html() {
        global $PAGE;

        $html = '';

        $assignment = $this->gradingarea->get_assignment();
        $submission = $this->gradingarea->get_submission();

        if (empty($this->controller) || !$this->controller->is_form_available()) {
            return $html;
        }

        if (empty($this->teachercap) && $this->needsupdate) {
            return $html;
        }

        //check for capability
        if (!empty($this->teachercap)) {
            //get the form and render it via buffer helper
            $mrhelper = new mr_helper();
            $html = $mrhelper->buffer(array($this->mform, 'display'));
        } else {
            //this is for a student
            $gradingmethod = $this->gradingarea->get_active_gradingmethod();

            //get grading info
            $item = $this->gradinginfo->items[0];
            $grade = $item->grades[$this->gradingarea->get_guserid()];

            if ((!$grade->grade === false) && empty($grade->hidden)) {
                $gradestr = '<div class="grade">'. get_string("grade").': '.$grade->str_long_grade. '</div>';
            } else {
                $gradestr = '';
            }

            $controller = $this->controller;
            if (empty($submission) || !$controller->get_active_instances($submission->id)) {
                $renderer = $controller->get_renderer($PAGE);
                $options = $controller->get_options();
                switch ($gradingmethod) {
                    case 'rubric':
                        $criteria = $controller->get_definition()->rubric_criteria;
                        $html = $renderer->display_rubric($criteria, $options, $controller::DISPLAY_VIEW, 'rubric');
                        break;
                    case 'checklist':
                        $groups = $controller->get_definition()->checklist_groups;
                        $html = $renderer->display_checklist($groups, $options, $controller::DISPLAY_VIEW, 'checklist');
                        break;
                }
            } else {
                $controller->set_grade_range(make_grades_menu($assignment->assignment->grade));
                $html = $controller->render_grade($PAGE, $submission->id, $item, $gradestr, false);
            }
        }

        return $html;
    }


    /**
     * Process the grade data
     * @param $notify
     */
    public function process($notify) {
        //a little setup
        $assignment = $this->get_gradingarea()->get_assignment();
        $submission = $this->get_gradingarea()->get_submission(true);

        //get the moodleform
        $mform = $this->mform;

        //set up a redirect url
        $redirecturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $assignment->course->id
                , 'garea' => $this->get_gradingarea()->get_areaid(), 'guser' => $this->get_gradingarea()->get_guserid()));

        //get the data from the form
        if ($data = $mform->get_data()) {

            if ($data->assignment != $assignment->assignment->id) {
                //throw an exception, could be some funny business going on here
                throw new moodle_exception('assignmentnotmatched', 'local_joulegrader');
            }

            if (isset($data->gradinginstanceid)) {
                //using advanced grading
                $gradinginstance = $this->gradinginstance;
                $grade = $gradinginstance->submit_and_get_grade($data->grade, $submission->id);
            } else if ($assignment->assignment->grade < 0) {
                //scale grade
                $grade = clean_param($data->grade, PARAM_INT);
            } else {
                //just using regular grading
                $lettergrades = grade_get_letters(context_course::instance($assignment->course->id));
                $grade = $data->grade;

                //determine if user is submitting as a letter grade, percentage or float
                $touppergrade = textlib::strtoupper($grade);
                $toupperlettergrades = array_map('textlib::strtoupper', $lettergrades);
                if (in_array($touppergrade, $toupperlettergrades)) {
                    //submitting lettergrade, find percent grade
                    $percentvalue = 0;
                    foreach ($toupperlettergrades as $value => $letter) {
                        if ($touppergrade == $letter) {
                            $percentvalue = $value;
                            break;
                        }
                    }

                    //transform to an integer within the range of the assignment
                    $grade = (int) ($assignment->assignment->grade * ($percentvalue / 100));

                } else if (strpos($grade, '%') !== false) {
                    //trying to submit percentage
                    $percentgrade = trim(strstr($grade, '%', true));
                    $percentgrade = clean_param($percentgrade, PARAM_FLOAT);

                    //transform to an integer within the range of the assignment
                    $grade = (int) ($assignment->assignment->grade * ($percentgrade / 100));

                } else if ($grade === '') {
                    //setting to "No grade"
                    $grade = -1;
                } else {
                    //just a numeric value, clean it as int b/c that's what assignment module accepts
                    $grade = clean_param($grade, PARAM_INT);
                }
            }

            //redirect to next user if set
            if (optional_param('saveandnext', 0, PARAM_BOOL) && !empty($data->nextuser)) {
                $redirecturl->param('guser', $data->nextuser);
            }

            if (optional_param('needsgrading', 0, PARAM_BOOL)) {
                $redirecturl->param('needsgrading', 1);
            }

            //save the grade
            if ($this->save_grade($grade, isset($data->override))) {
                $notify->good('gradesaved');
            }
        }

        redirect($redirecturl);
    }

    /**
     * @return void
     */
    public function require_js() {

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
     *
     * @return bool
     */
    protected function save_grade($grade, $override) {
        global $USER, $DB;

        $success = true;

        //need to do two things here
        //1) update the grade in assignment_submission table
        //2) update the grade in the gradebook if the grade is NOT overridden or if it IS overridden AND the override check
        //   was checked

        $assignment = $this->get_gradingarea()->get_assignment();
        $submission = $this->get_gradingarea()->get_submission();

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
        $gradeoverridden = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->overridden;
        if (!$gradeoverridden) {
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

    /**
     * Determines whether or not there is a grade for the current grading area/user
     *
     * @return boolean
     */
    public function not_graded() {
        $notgraded = false;

        $assignment = $this->get_gradingarea()->get_assignment();
        $submission = $this->get_gradingarea()->get_submission();

        if ($assignment->assignment->grade != 0) {
            //check the submission first
            if (!empty($submission) && $submission->grade == -1) {
                $notgraded = true;
            } else if (!empty($this->gradinginfo) && is_null($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->grade)) {
                //check the gradebook
                $notgraded = true;
            }
        }

        return $notgraded;
    }
}