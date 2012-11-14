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