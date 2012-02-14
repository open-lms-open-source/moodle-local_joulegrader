<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/view/abstract.php');

/**
 * View Pane class for Online Assignment type
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_view_mod_assignment_submission_online extends local_joulegrader_lib_pane_view_abstract {

    /**
     * Loads the html for this pane
     */
    protected function load_html() {
        global $USER, $OUTPUT, $CFG;
        $html = '';

        $gradingarea = $this->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assignment();

        //need the submission
        $submission = $gradingarea->get_submission();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {

            //dates html
            $html .= $this->dates_html($assignment, $submission);

            //submission html
            $html .= $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter', 'online');
            if (!empty($submission)) {
                $text = file_rewrite_pluginfile_urls($submission->data1, 'pluginfile.php', $gacontext->id, 'mod_assignment', $assignment->filearea, $submission->id);
                $html .= format_text($text, $submission->data2, array('overflowdiv'=>true));

            } else {
                $html .= html_writer::tag('div', get_string('emptysubmission', 'assignment'), array('class' => 'aligncenter'));
            }
            $html .= $OUTPUT->box_end();
        }

        $this->html = $html;
    }

    /**
     * @param $assignment
     * @param $submission
     * @return string
     */
    protected function dates_html($assignment, $submission) {
        global $OUTPUT, $CFG;

        $html = $OUTPUT->box_start('generalbox boxaligncenter', 'dates');
        $html .= html_writer::start_tag('table');
        if ($assignment->assignment->timeavailable) {
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', get_string('availabledate', 'assignment'), array('class' => 'c0'));
            $html .= html_writer::tag('td', userdate($assignment->assignment->timeavailable), array('class' => 'c1'));
            $html .= html_writer::end_tag('tr');
        }
        if ($assignment->assignment->timedue) {
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', get_string('duedate', 'assignment'), array('class' => 'c0'));
            $html .= html_writer::tag('td', userdate($assignment->assignment->timedue), array('class' => 'c1'));
            $html .= html_writer::end_tag('tr');
        }

        if (!empty($submission)) {
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', get_string('lastedited'), array('class' => 'c0'));
            $html .= html_writer::start_tag('td', array('class' => 'c1'));
            $html .= userdate($submission->timemodified);

            /// Decide what to count
            if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
                $html .= ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')';
            } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                $html .= ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')';
            }

            $html .= html_writer::end_tag('td');
            $html .= html_writer::end_tag('tr');
        }
        $html .= html_writer::end_tag('table');
        $html .= $OUTPUT->box_end();

        return $html;
    }
}
