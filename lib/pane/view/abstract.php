<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */

abstract class local_joulegrader_lib_pane_view_abstract implements renderable {

    /**
     * @var local_joulegrader_lib_gradingarea_abstract - instance of a gradingarea class
     */
    protected $gradingarea;

    /**
     * @var string - message to display if there is nothing for the panel to display
     */
    protected $emptymessage;

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
     * @return string
     */
    public function get_emptymessage() {
        return $this->emptymessage;
    }

    /**
     * Do any initialization the panel needs before rendering
     *
     * @abstract
     */
    abstract public function init();
}