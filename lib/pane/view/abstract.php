<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */

abstract class local_joulegrader_lib_pane_view_abstract {

    /**
     * @var string - the html for the view pane
     */
    protected $html;

    /**
     * @var local_joulegrader_lib_gradingarea_abstract - instance of a gradingarea class
     */
    protected $gradingarea;

    /**
     * @param local_joulegrader_lib_gradingarea_abstract $gradingarea
     */
    public function __construct(local_joulegrader_lib_gradingarea_abstract $gradingarea) {
        $this->gradingarea = $gradingarea;
    }

    /**
     * @return string
     */
    public function get_html() {
        if (!isset($this->html)) {
            $this->load_html();
        }

        return $this->html;
    }

    public function get_gradingarea() {
        return $this->gradingarea;
    }

    /**
     * Load the html for this pane
     *
     * @abstract
     */
    abstract protected function load_html();
}