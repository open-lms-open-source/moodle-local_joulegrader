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
 * Grade modal form
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\form;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->libdir.'/formslib.php');
/**
 * Grade form for advanced grading modal
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 * @see moodleform
 */
class grade_modal extends \moodleform {

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('hidden', 'nextuser', $this->_customdata->get_gradingarea()->get_nextuserid());
        $mform->setType('nextuser', PARAM_INT);

        if ($this->_customdata->get_gradingdisabled()) {
            // Add a message notifying user that grading is disabled.
            $mform->addElement('html', \html_writer::tag('div', get_string('gradingdisabled', 'local_joulegrader'),
                array('class' => 'warning')));
        }

        //for the grade range
        $grademenu = make_grades_menu($this->_customdata->get_grade());

        //set up the grading instance
        $gradinginstance = $this->_customdata->get_gradinginstance();
        $gradinginstance->get_controller()->set_grade_range($grademenu);
        $gradingelement = $mform->addElement('grading', 'grade', get_string('gradenoun').':', array('gradinginstance' => $gradinginstance));
        if ($this->_customdata->get_gradingdisabled()) {
            $gradingelement->freeze();
        } else {
            $mform->addElement('hidden', 'gradinginstanceid', $gradinginstance->get_id());
            $mform->setType('gradinginstanceid', PARAM_INT);
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
            // If overridden in gradebook, add a checkbox.
            $mform->addElement('checkbox', 'override', null, get_string('overridetext', 'local_joulegrader'));
            $mform->setType('override', PARAM_BOOL);
        }

        $this->_customdata->modalform_hook($mform);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submit', get_string('save', 'local_joulegrader'));
        $nextuser = $this->_customdata->get_gradingarea()->get_nextuserid();
        if (isset($nextuser)) {
            $buttonarray[] = &$mform->createElement('submit', 'saveandnext', get_string('saveandnext', 'local_joulegrader'));
        }

        $buttongrp = $mform->addGroup($buttonarray, 'grading_buttonar', '', array(' '), false);
        $mform->setType('grading_buttonar', PARAM_RAW);

        if ($this->_customdata->get_gradingdisabled()) {
            $buttongrp->freeze();
        }

    }
}
