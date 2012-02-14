<?php
/**
 * Renderer
 *
 * @author Sam Chaffee
 * @package local/mrooms
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');


class local_joulegrader_renderer extends plugin_renderer_base {

    /**
     * Renders a navigation widget containing a previous link, a next link, and a select menu
     *
     * @param local_joulegrader_lib_navigation_widget $navwidget
     * @return string
     */
    public function render_local_joulegrader_lib_navigation_widget(local_joulegrader_lib_navigation_widget $navwidget) {
        global $OUTPUT;

        //widget name
        $widgetname = $navwidget->get_name();

        //widget url
        $widgeturl = $navwidget->get_url();
        $linkurl   = clone($widgeturl);

        //prev link
        $prevlink = '';
        if ($previd = $navwidget->get_previd()) {
            $linkurl->param($navwidget->get_param(), $previd);
            $prevlink = $OUTPUT->action_icon($linkurl, new pix_icon('t/left', get_string('previous')));
        }

        //select menu
        $formid = "local-joulegrader-{$widgetname}nav-menu";
        $select = new single_select($widgeturl, $navwidget->get_param(), $navwidget->get_options()
            , $navwidget->get_currentid(), '', $formid);

        //set some select attributes
        $select->set_help_icon($widgetname.'nav', 'local_joulegrader');

        //render the select form
        $selectform = $OUTPUT->render($select);

        //next link
        $nextlink = '';
        if ($nextid = $navwidget->get_nextid()) {
            $linkurl->param($navwidget->get_param(), $nextid);
            $nextlink = $OUTPUT->action_icon($linkurl, new pix_icon('t/right', get_string('next')));
        }

        return $prevlink . $selectform . $nextlink;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_assignment_submission_online $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assignment_submission_online(local_joulegrader_lib_pane_view_mod_assignment_submission_online $viewpane) {
        global $USER, $OUTPUT;
        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
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
            $html .= $this->help_render_assignment_dates($assignment, $submission);

            //submission html
            $html .= $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter', 'online');
            if (!empty($submission)) {
                $text = file_rewrite_pluginfile_urls($submission->data1, 'pluginfile.php', $gacontext->id, 'mod_assignment', $assignment->filearea, $submission->id);
                $html .= format_text($text, $submission->data2, array('overflowdiv'=>true));

            } else {
                $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
            }
            $html .= $OUTPUT->box_end();
        }

        return $html;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_assignment_submission_offline $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assignment_submission_offline(local_joulegrader_lib_pane_view_mod_assignment_submission_offline $viewpane) {
        global $USER;

        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assignment();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {
            //dates html
            $html .= $this->help_render_assignment_dates($assignment);

            //nothing to display for offline
            $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
        }

        return $html;
    }

    /**
     * @param $assignment
     * @param null $submission
     * @return string
     */
    protected function help_render_assignment_dates($assignment, $submission = NULL) {
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