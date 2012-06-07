<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/lib/gradelib.php');

/**
 * joule Grader Grade Pane abstract class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
abstract class local_joulegrader_lib_pane_grade_abstract implements renderable {

    /**
     * @var local_joulegrader_lib_gradingarea_abstract - instance of a gradingarea class
     */
    protected $gradingarea;

    /**
     * @var moodleform - instance of moodleform
     */
    protected $mform;

    /**
     * @var string
     */
    protected $advancedgradingerror;

    /**
     * @var
     */
    protected $gradinginfo;

    /**
     * @param local_joulegrader_lib_gradingarea_abstract $gradingarea
     */
    public function __construct(local_joulegrader_lib_gradingarea_abstract $gradingarea) {
        $this->gradingarea = $gradingarea;
    }

    /**
     * @return local_joulegrader_lib_gradingarea_abstract
     */
    public function get_gradingarea() {
        return $this->gradingarea;
    }

    /**
     * @return mixed
     */
    public function get_gradinginfo() {
        return $this->gradinginfo;
    }

    /**
     * Return the supported advanced grading plugins
     *
     * @return array
     */
    public static function get_supportedplugins() {
        return array('rubric', 'checklist');
    }

    /**
     *
     * @return string - preview for the rubric
     */
    protected function get_rubric_preview() {
        $html = '';

        $definition = $this->controller->get_definition();
        $rubricfilling = $this->gradinginstance->get_rubric_filling();

        $previewtable = new html_table();
        $previewtable->head[] = new html_table_cell(get_string('criteria', 'local_joulegrader'));
        $previewtable->head[] = new html_table_cell(get_string('score', 'local_joulegrader'));
        foreach ($definition->rubric_criteria as $criterionid => $criterion) {
            $row = new html_table_row();
            //criterion name cell
            $row->cells[] = new html_table_cell($criterion['description']);

            //score cell value
            if (!empty($rubricfilling['criteria']) && isset($rubricfilling['criteria'][$criterionid]['levelid'])) {
                $levelid = $rubricfilling['criteria'][$criterionid]['levelid'];
                $criterionscore = $criterion['levels'][$levelid]['score'];
            } else {
                $criterionscore = ' - ';
            }

            //score cell
            $row->cells[] = new html_table_cell($criterionscore);

            $previewtable->data[] = $row;
        }

        $previewtable = html_writer::table($previewtable);

        $grade = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()];
        if ((!$grade->grade === false) && empty($grade->hidden)) {
            $gradeval = $grade->str_long_grade;
        } else {
            $gradeval = '-';
        }
        $gradestr = '<div class="grade">'. get_string("grade").': '.$gradeval. '</div>';
        $html .= html_writer::tag('div', $previewtable . $gradestr, array('id' => 'local-joulegrader-viewrubric-preview-con'));

        //rubric warning message
        $html .= html_writer::tag('div', html_writer::tag('div', get_string('rubricerror', 'local_joulegrader')
            , array('class' => 'yui3-widget-bd')), array('id' => 'local-joulegrader-gradepane-rubricerror', 'class' => 'dontshow'));

        return $html;
    }

    /**
     * @return string - preview for the checklist
     */
    protected function get_checklist_preview() {
        global $PAGE;

        $html = '';

        $groups = $this->controller->get_definition()->checklist_groups;
        $options = $this->controller->get_options();

        $options['showremarksstudent'] = 0;
        $renderer = $this->controller->get_renderer($PAGE);

        $values = $this->gradinginstance->get_checklist_filling();

        $controller = $this->controller;
        $checklist = $renderer->display_checklist($groups, $options, $controller::DISPLAY_VIEW, 'checklistpreview', $values);

        $grade = $this->gradinginfo->items[0]->grades[$this->gradingarea->get_guserid()];
        if ((!$grade->grade === false) && empty($grade->hidden)) {
            $gradeval = $grade->str_long_grade;
        } else {
            $gradeval = '-';
        }
        $gradestr = '<div class="grade">'. get_string("grade").': '.$gradeval. '</div>';

        $html .= html_writer::tag('div', $checklist . $gradestr, array('id' => 'local-joulegrader-viewchecklist-preview-con'));

        return $html;
    }

    /**
     * @return mixed
     */
    abstract public function get_panehtml();

    /**
     * Do any initialization the panel needs before rendering
     *
     * @abstract
     */
    abstract public function init();

    /**
     * Process data submitted by this grade pane
     *
     * @abstract
     * @param $notify mr_notify
     */
    abstract public function process($notify);

    /**
     * Returns whether or not there is a grade yet for the area/user
     *
     * @abstract
     * @return boolean
     */
    abstract public function not_graded();
}