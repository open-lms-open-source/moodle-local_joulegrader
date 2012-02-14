<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Grading area abstract class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 *
 */
require_once($CFG->dirroot . '/grade/grading/lib.php');
abstract class local_joulegrader_lib_gradingarea_abstract {

    /**
     * @var int - id of the grading_areas entry
     */
    protected $areaid;

    /**
     * @var int - id of the gradeable user
     */
    protected $guserid;

    /**
     * @var gradingmanager - instance of the grading_manager for the area
     */
    protected $gradingmanager;

    /**
     * @var string - the required capability for a student to view this grading area
     */
    protected static $studentcapability = 'local/joulegrader:view';


    /**
     * @var string - the required capability for a teacher to grade this grading area
     */
    protected static $teachercapability = 'local/joulegrader:grade';

    /**
     * @var local_joulegrade_lib_pane - instance
     */
    protected $viewpane;

    /**
     * @var local_joulegrade_lib_pane - instance
     */
    protected $gradingpane;

    /**
     * Additional checks called by gradingareas helper to see if the area should be included in the navigation.
     * This is in addition to the capability check done by the helper. For instance, this will be used to make
     * sure that a mod_assignment_submission grading area is of a type that joule Grader will support at this time
     *
     * @abstract
     * @param grading_manager $gradingmanager
     * @param bool $asstudent
     * @return bool
     */
    abstract public static function include_area(grading_manager $gradingmanager, $asstudent);

    /**
     * @return string
     */
    public static function get_studentcapability() {
        return self::$studentcapability;
    }

    /**
     * @return string
     */
    public static function get_teachercapability() {
        return self::$teachercapability;
    }


    /**
     * @param grading_manager $gradingmanager - instance
     * @param $areaid - id of the grading_areas entry
     * @param $guserid - the id of the gradeable user
     */
    public function __construct(grading_manager $gradingmanager, $areaid, $guserid) {
        $this->gradingmanager = $gradingmanager;
        $this->areaid = (int) $areaid;
        $this->guserid  = (int) $guserid;
    }

    /**
     * @return \grading_manager
     */
    public function get_gradingmanager() {
        if (!($this->gradingmanager instanceof grading_manager)) {
            $this->load_gradingmanager();
        }
        return $this->gradingmanager;
    }

    /**
     * Load the grading_manager instance
     *
     * @return local_joulegrader_lib_gradingarea_abstract
     * @throws coding_exception
     */
    public function load_gradingmanager() {
        //first check to see that an instance is not already loaded
        if (!isset($this->gradingmanager) || !($this->gradingmanager instanceof grading_manager)) {
            if (!isset($this->areaid)) {
                throw new coding_exception('Cannot load grading_manager instance if areaid is not set');
            }

            //load the grading_manager instance
            $this->gradingmanager = get_grading_manager($this->areaid);
        }

        return $this;
    }

    /**
     * Load the viewpane instance
     *
     * @return local_joulegrader_lib_gradingarea_abstract
     * @throws coding_exception
     */
    protected function load_viewpane() {
        global $CFG; //don't remove this: needed for the include_once call

        //first check to see that an instance is not already loaded
        if (!isset($this->viewpane) || !($this->viewpane instanceof local_joulegrader_lib_pane_view_abstract)) {
            //get the viewpane class info from the subclass
            list($classpath, $classname) = $this->get_viewpane_info();

            //try to include the class
            include_once($classpath);

            //check to see that it was loaded
            if (!class_exists($classname)) {
                throw new coding_exception("View pane class: $classname is not defined");
            }

            //try to isntantiate it
            try {
                $this->viewpane = new $classname($this);
            } catch (Exception $e) {
                throw new coding_exception("View pane class: $classname could not be instantiated");
            }
        }

        return $this;
    }

    /**
     * @return int - the grading_areas entry id
     */
    public function get_areaid() {
        return $this->areaid;
    }

    /**
     * @return int - the gradeable user's id
     */
    public function get_guserid() {
        return $this->guserid;
    }

    /**
     * Get the view pane html for the grading area
     *
     * @return string - the html for the view pane
     */
    public function get_viewpane_html() {
        if (!($this->viewpane instanceof local_joulegrade_lib_pane_view_abstract)) {
            $this->load_viewpane();
        }

        return $this->viewpane->get_html();
    }

    /**
     * Return the name of and path to the viewpane class that this grading_area class should use
     *
     * @abstract
     */
    abstract protected function get_viewpane_info();

}