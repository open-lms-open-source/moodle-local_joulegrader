<?php
namespace local_joulegrader\pane\view;
use local_joulegrader\gradingarea;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */

abstract class view_abstract implements \renderable {

    /**
     * @var gradingarea\gradingarea_abstract - instance of a gradingarea class
     */
    protected $gradingarea;

    /**
     * @var string - message to display if there is nothing for the panel to display
     */
    protected $emptymessage;

    /**
     * @param gradingarea\gradingarea_abstract $gradingarea
     */
    public function __construct(gradingarea\gradingarea_abstract $gradingarea) {
        $this->gradingarea = $gradingarea;
    }

    /**
     * @return gradingarea\gradingarea_abstract
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