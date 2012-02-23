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
     * @var data needed for the moodleform for display/processing
     */
    protected $mformdata;

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
     * @return moodleform
     */
    public function get_mform() {
        return $this->mform;
    }

    /**
     * @return mixed
     */
    public function get_gradinginfo() {
        return $this->gradinginfo;
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
     */
    abstract public function process($notify);
}