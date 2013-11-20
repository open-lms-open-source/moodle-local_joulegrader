<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/local/joulegrader/form/gradepaneform.php');
require_once($CFG->dirroot . '/local/joulegrader/form/grademodalform.php');

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
    protected $paneform = null;

    /**
     * @var moodleform
     */
    protected $modalform = null;

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
     * @return mixed
     */
    public function get_gradingdisabled() {
        return $this->gradingdisabled;
    }

    public function get_gradinginstance() {
        return $this->gradinginstance;
    }

    public function get_advancedgradingerror() {
        return $this->advancedgradingerror;
    }

    public function get_controller() {
        return $this->controller;
    }

    public function get_paneform() {
        if ($this->has_paneform()) {
            if (is_null($this->paneform)) {
                $this->paneform = new local_joulegrader_form_gradepaneform($this->get_posturl(), $this);
            }
        }
        return $this->paneform;
    }

    public function get_modalform() {
        if ($this->has_modal()) {
            if (is_null($this->modalform)) {
                $this->modalform = new local_joulegrader_form_grademodalform($this->get_posturl(), $this);
            }
        }
        return $this->modalform;
    }

    /**
     * @return bool
     */
    public function read_only() {
        return false;
    }

    /**
     * @return bool
     */
    public function has_overall_feedback() {
        return false;
    }

    /**
     * @return string
     */
    public function get_overall_feedback() {
        return '';
    }

    /**
     * @return bool
     */
    public function has_file_feedback() {
        return false;
    }

    /**
     * @return string
     */
    public function get_file_feedback() {
        return '';
    }

    /**
     * @param mixed $grade
     * @return mixed
     */
    public function format_gradevalue($grade) {
        return $grade;
    }

    /**
     * @param array $data
     * @param array $validated
     * @return array
     */
    public function gradepane_validation($data, $validated) {
        return $validated;
    }

    /**
     * @param MoodleQuickForm $mform
     */
    public function paneform_hook($mform) {
    }

    /**
     * @param MoodleQuickForm $mform
     */
    public function modalform_hook($mform) {
    }

    private function get_posturl() {
        $posturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $this->get_courseid()
        , 'garea' => $this->get_gradingarea()->get_areaid(), 'guser' => $this->get_gradingarea()->get_guserid(), 'action' => 'process'));

        if ($needsgrading = optional_param('needsgrading', 0, PARAM_BOOL)) {
            $posturl->param('needsgrading', 1);
        }

        return $posturl;
    }

    /**
     * @return false|grade_grade
     */
    public function get_gradebook_grade() {
        // Current gradebook grade.
        $grade = $this->get_gradinginfo()->items[0]->grades[$this->get_gradingarea()->get_guserid()];

        return $grade;
    }

    /**
     * @return null|int
     */
    public function get_activity_grade() {
        return null;
    }

    public function get_activity_grade_label() {
        return get_string('grade');
    }

    /**
     * Return the supported advanced grading plugins
     *
     * @return array
     */
    public static function get_supportedplugins() {
        return array('rubric', 'checklist', 'guide');
    }

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
     * @param $data
     * @param $notify mr_notify
     */
    abstract public function process($data, $notify);

    /**
     * Returns whether or not there is a grade yet for the area/user
     *
     * @abstract
     * @return boolean
     */
    abstract public function not_graded();

    /**
     * Returns wehter or not the area can be graded
     *
     * @abstract
     * @return boolean
     */
    abstract public function has_grading();
}