<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/grade/abstract.php');

/**
 * joule Grader mod_hsuforum_posts grade pane class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_grade_mod_hsuforum_posts_class extends local_joulegrader_lib_pane_grade_abstract {

    protected $cm;
    /**
     * @var context_module
     */
    protected $context;
    protected $forum;

    /**
     * @var gradingform_controller
     */
    protected $controller;

    /**
     * @var gradingform_instance
     */
    protected $gradinginstance;

    /**
     * @var boolean
     */
    protected $teachercap;

    /**
     * Do some initialization
     */
    public function init() {
        global $DB, $USER;

        $this->context = $this->gradingarea->get_gradingmanager()->get_context();
        $this->cm      = get_coursemodule_from_id('hsuforum', $this->context->instanceid, 0, false, MUST_EXIST);
        $this->forum   = $DB->get_record('hsuforum', array('id' => $this->cm->instance), '*', MUST_EXIST);
        $this->courseid = $this->cm->course;

        $this->gradinginfo = grade_get_grades($this->cm->course, 'mod', 'hsuforum', $this->forum->id, array($this->gradingarea->get_guserid()));

        $this->gradingdisabled = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->locked;
        $this->gradeoverride = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->overridden;

        if (($gradingmethod = $this->gradingarea->get_active_gradingmethod()) && in_array($gradingmethod, self::get_supportedplugins())) {
            $this->controller = $this->gradingarea->get_gradingmanager()->get_controller($gradingmethod);
            if ($this->controller->is_form_available()) {
                if ($this->gradingdisabled) {
                    $this->gradinginstance = $this->controller->get_current_instance($USER->id, $this->gradingarea->get_guserid());
                } else {
                    $instanceid = optional_param('gradinginstanceid', 0, PARAM_INT);
                    $this->gradinginstance = $this->controller->get_or_create_instance($instanceid, $USER->id, $this->gradingarea->get_guserid());
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
                $this->advancedgradingerror = $this->controller->form_unavailable_notification();
            }
        }


        $this->teachercap = has_capability($this->gradingarea->get_teachercapability(), $this->context);
    }

    public function has_grading() {
        $hasgrading = true;

        if ($this->forum->scale == 0) {
            $hasgrading = false;
        }

        return $hasgrading;
    }

    public function get_currentgrade() {
        return $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->grade;
    }

    public function format_gradevalue($grade) {
        $gradeitem = grade_item::fetch(array('itemtype'=> 'mod', 'itemmodule'=> 'hsuforum',
            'iteminstance'=> $this->forum->id, 'courseid'=> $this->courseid,
            'outcomeid' => null));

        $decimals = $gradeitem->get_decimals();
        $formattedgrade = format_float($grade, $decimals, true);

        return $formattedgrade;
    }

    public function has_active_gradinginstances() {
        return $this->controller->get_active_instances($this->gradingarea->get_guserid());
    }

    public function has_modal() {
        return !(empty($this->controller) || empty($this->gradinginstance) || (!empty($this->controller) && !$this->controller->is_form_available()));
    }

    public function get_needsupdate() {
        return $this->needsupdate;
    }

    public function get_grade() {
        return $this->forum->scale;
    }

    public function has_teachercap() {
        return $this->teachercap;
    }

    public function get_courseid() {
        return $this->courseid;
    }

    public function has_override() {
        return false;
    }

    public function has_paneform() {
        return (empty($this->controller) || empty($this->gradinginstance) || (!empty($this->controller) && !$this->controller->is_form_available()));
    }

    public function get_agitemid() {
        return $this->gradingarea->get_guserid();
    }

    /**
     * @return bool
     */
    public function has_overall_feedback() {
        return true;
    }

    /**
     * Returns the formatted overall feedback from the gradebook.
     * For use with the student view.
     *
     * @return string
     */
    public function get_overall_feedback() {
        $feedback = '';
        if ($this->has_overall_feedback()) {
            $feedbackinfo = $this->get_feedback_info();
            if (!empty($feedbackinfo['feedback'])) {
                $feedback = format_text($feedbackinfo['feedback'], $feedbackinfo['feedbackformat']);
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

            $editor = $mform->addElement('editor', 'hsuforumfeedback_editor',
                get_string('overallfeedback', 'local_joulegrader') . ': ', null, null);
            $mform->setType('hsuforumfeedback_editor', PARAM_RAW);

            $feedbackinfo = $this->get_feedback_info();

            if (!empty($feedbackinfo['feedback'])) {
                // Add the existing feedback.
                $data = array();
                $data['text'] = $feedbackinfo['feedback'];
                $data['format'] = $feedbackinfo['feedbackformat'];

                $editor->setValue($data);
            }
        }
    }

    /**
     * @return array
     */
    protected function get_feedback_info() {
        $feedbackinfo = array('feedback' => '', 'feedbackformat' => FORMAT_HTML);

        if (!empty($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()])
            && !empty($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->feedback)) {

            $feedbackinfo['feedback'] = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->feedback;
            $feedbackinfo['feedbackformat'] = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->feedbackformat;
        }

        return $feedbackinfo;
    }

    /**
     * Process the grade data
     * @param $data
     * @param mr_html_notify $notify
     * @throws moodle_exception
     */
    public function process($data, $notify) {
        //set up a redirect url
        $redirecturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $this->cm->course
                , 'garea' => $this->get_gradingarea()->get_areaid(), 'guser' => $this->get_gradingarea()->get_guserid()));

        //get the data from the form
        if ($data) {
            if (isset($data->gradinginstanceid)) {
                //using advanced grading
                $gradinginstance = $this->gradinginstance;
                $this->controller->set_grade_range(make_grades_menu($this->forum->scale));
                $grade = $gradinginstance->submit_and_get_grade($data->grade, $this->gradingarea->get_guserid());
            } else if ($this->forum->scale < 0) {
                //scale grade
                $grade = clean_param($data->grade, PARAM_INT);
            } else {
                //just using regular grading
                $lettergrades = grade_get_letters(context_course::instance($this->cm->course));
                $grade = $data->grade;

                $touppergrade = textlib::strtoupper($grade);
                $toupperlettergrades = array_map('textlib::strtoupper', $lettergrades);
                if (in_array($touppergrade, $toupperlettergrades)) {
                    //submitting lettergrade, find percent grade
                    $percentvalue = 0;
                    $max = 100;
                    foreach ($toupperlettergrades as $value => $letter) {
                        if ($touppergrade == $letter) {
                            $percentvalue = ($max + $value) / 2;
                            break;
                        }
                        $max = $value - 1;
                    }

                    //transform to an integer within the range of the assignment
                    $grade = (float) ($this->forum->scale * ($percentvalue / 100));

                } else if (strpos($grade, '%') !== false) {
                    //trying to submit percentage
                    $percentgrade = trim(strstr($grade, '%', true));
                    $percentgrade = clean_param($percentgrade, PARAM_FLOAT);

                    //transform to an integer within the range of the assignment
                    $grade = (float) ($this->forum->scale * ($percentgrade / 100));

                } else if ($grade === '') {
                    //setting to "No grade"
                    $grade = -1;
                } else {
                    //just a numeric value, clean it as int b/c that's what assignment module accepts
                    $grade = clean_param($grade, PARAM_FLOAT);
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
            if ($this->save_grade($grade, $data->hsuforumfeedback_editor)) {
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
     * Returns whether or not there is a grade yet for the area/user
     *
     * @return boolean
     */
    public function not_graded() {
        if (!empty($this->gradinginfo) && is_null($this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()]->grade)) {
            return true;
        }
        return false;
    }

    /**
     * @param $grade
     * @param $override
     *
     * @return bool
     */
    protected function save_grade($grade, $feedbackinfo) {
        $gradeitem = grade_item::fetch(array(
            'courseid'     => $this->cm->course,
            'itemtype'     => 'mod',
            'itemmodule'   => 'hsuforum',
            'iteminstance' => $this->forum->id,
            'itemnumber'   => 0,
        ));

        $success = false;
        //if no grade item, create a new one
        if (!empty($gradeitem)) {
            //if grade is -1 in assignment_submissions table, it should be passed as null
            if ($grade == -1) {
                $grade = null;
            }
            if (!$this->gradeoverride) {
                $grades = array(
                    'userid' => $this->gradingarea->get_guserid(),
                    'rawgrade' => $grade,
                    'feedback' => $feedbackinfo['text'],
                    'feedbackformat' => $feedbackinfo['format'],
                );
                $success = grade_update('local/joulegrader', $gradeitem->courseid, $gradeitem->itemtype,
                        $gradeitem->itemmodule, $gradeitem->iteminstance, 0, $grades);
                $success = $success == GRADE_UPDATE_OK ? true : false;
            } else {
                $success = $gradeitem->update_final_grade($this->gradingarea->get_guserid(), $grade, 'local/joulegrader',
                    $feedbackinfo['text'], $feedbackinfo['format']);
            }
        }
        return $success;
    }
}