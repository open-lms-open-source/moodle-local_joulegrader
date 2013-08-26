<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->libdir.'/formslib.php');
/**
 * Grade form for grade pane
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 * @see moodleform
 */
class local_joulegrader_form_gradepaneform extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $nextuserid = $this->_customdata->get_gradingarea()->get_nextuserid();

        $mform->addElement('hidden', 'nextuser', $nextuserid);
        $mform->setType('nextuser', PARAM_INT);

        if (!$this->_customdata->has_modal()) {
            if ($this->_customdata->get_gradingdisabled()) {
                // Add a message notifying user that grading is disabled.
                $mform->addElement('html', html_writer::tag('div', get_string('gradingdisabled', 'local_joulegrader'),
                    array('class' => 'warning')));
            }

            //for the grade range
            $grademenu = make_grades_menu($this->_customdata->get_grade());

            //check for an existing grade
            $grade = $this->_customdata->get_currentgrade();

            //check to see if this is a scale
            $isscale = (bool) ($this->_customdata->get_grade() < 0);
            if ($isscale) {
                $grademenu[-1] = get_string('nograde');
                //heading
                $mform->addElement('static', 'gradeheader', null, get_string('grade'));

                //scale grade element
                $gradingelement = $mform->addElement('select', 'grade', null, $grademenu);
                $mform->setType('grade', PARAM_INT);
            } else {
                //add heading
                $mform->addElement('static', 'gradeheader', null, get_string('gradeoutof', 'local_joulegrader', $this->_customdata->get_grade()));

                //add the grade text element
                $gradingelement = $mform->addElement('text', 'grade', null, array('size' => 5));
                $gradingelement->setHiddenLabel(true);

                //want to accept numbers, letters, percentage here
                $mform->setType('grade', PARAM_RAW_TRIMMED);

                //if the there is no grade yet make it blank
                if ($grade == -1) {
                    $grade = '';
                } else {
                    $grade = $this->_customdata->format_gradevalue($grade);
                }
            }
            if ($this->_customdata->get_gradingdisabled()) {
                $gradingelement->freeze();
            }

            $mform->setDefault('grade', $grade);
        }

        // Add overall feedback.
        if ($this->_customdata->has_overall_feedback()) {
            $this->_customdata->add_feedback_form($mform);
        }

        // Add file feedback.
        if ($this->_customdata->has_file_feedback()) {
            $this->_customdata->add_filefeedback_form($mform);
        }

        //check for override
        if ($this->_customdata->has_override()) {
            //if overridden in gradebook, add a checkbox
            $mform->addElement('checkbox', 'override', null, get_string('overridetext', 'local_joulegrader'));
            $mform->setType('override', PARAM_BOOL);
        }

        $this->_customdata->paneform_hook($mform);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submit', get_string('save', 'local_joulegrader'));
        if (!empty($nextuserid)) {
            $buttonarray[] = &$mform->createElement('submit', 'saveandnext', get_string('saveandnext', 'local_joulegrader'));
        }

        $buttongrp = $mform->addGroup($buttonarray, 'grading_buttonar', '', array(' '), false);
        $mform->setType('grading_buttonar', PARAM_RAW);

        if ($this->_customdata->get_gradingdisabled()) {
            $buttongrp->freeze();
        }

    }

    /**
     * Form validation
     *
     * @param $data
     * @param $files
     *
     * @return bool
     */
    public function validation($data, $files) {
        $validated = true;

        $outofgrade = $this->_customdata->get_grade();

        //only need to do extra validation if they submitted via text box (not an advanced grading and not scale)
        if (!$this->_customdata->has_modal() && $outofgrade  >= 0) {
            $validated = array('grade' => get_string('gradeoutofrange', 'local_joulegrader'));

            //just using regular grading
            $lettergrades = grade_get_letters(context_course::instance($this->_customdata->get_courseid()));
            $grade = trim($data['grade']);

            //determine if user is submitting as a letter grade, percentage or float
            if ($grade === '') {
                $validated = array();
            } else if (is_numeric($grade)) {
                //straight point value
                $grade = clean_param($grade, PARAM_FLOAT);

                //needs to be in range 0 - $assignmentgrade
                if ($grade >= 0 && $grade <= $outofgrade) {
                    $validated = array();
                }
            } else if (strpos($grade, '%') !== false) {
                // trying to submit percentage
                $percentgrade = trim(strstr($grade, '%', true));

                // make sure what is left is numeric
                if (is_numeric($percentgrade)) {
                    $percentgrade = clean_param($percentgrade, PARAM_INT);
                    if ($percentgrade >= 0 && $percentgrade <= 100) {
                        $validated = array();
                    }
                }
            } else if (in_array(textlib::strtoupper($grade), array_map('textlib::strtoupper', $lettergrades))) {
                //look for a lettergrade
                $validated = array();
            }

            $validated = $this->_customdata->gradepane_validation($data, $validated);
        }

        return $validated;
    }
}