<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/local/joulegrader/lib/comment/loop.php');
/**
 * Grading area abstract class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 *
 */
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
     * @var int - id of the next gradeable user
     */
    protected $nextuserid;

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
     * @var local_joulegrade_lib_pane_view_abstract - instance
     */
    protected $viewpane;

    /**
     * @var local_joulegrade_lib_pane_grade_abstract - instance
     */
    protected $gradepane;

    /**
     * @var local_joulegrader_lib_comment_loop - instance
     */
    protected $commentloop;

    /**
     * Additional checks called by gradingareas helper to see if the area should be included in the navigation.
     * This is in addition to the capability check done by the helper. For instance, this will be used to make
     * sure that a mod_assignment_submission grading area is of a type that joule Grader will support at this time
     *
     * @param course_modinfo $courseinfo
     * @param grading_manager $gradingmanager
     * @param bool $needsgrading
     * @return bool
     */
    public static function include_area(course_modinfo $courseinfo, grading_manager $gradingmanager, $needsgrading = false) {
        return false;
    }

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
     * Returns the active grading method for the grading area
     *
     * @return null|string
     */
    public function get_active_gradingmethod() {
        return $this->get_gradingmanager()->get_active_method();
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
                throw new coding_exception("View pane class $classname is not defined");
            }

            //try to isntantiate it
            try {
                $this->viewpane = new $classname($this);
            } catch (Exception $e) {
                throw new coding_exception("View pane class $classname could not be instantiated");
            }
        }

        return $this;
    }

    /**
     * Load the gradepane instance
     *
     * @return local_joulegrader_lib_gradingarea_abstract
     * @throws coding_exception
     */
    protected function load_gradepane() {
        global $CFG; //don't remove this: needed for the include_once call

        //first check to see that an instance is not already loaded
        if (!isset($this->gradepane) || !($this->gradepane instanceof local_joulegrader_lib_pane_grade_abstract)) {
            //get the viewpane class info from the subclass
            list($classpath, $classname) = $this->get_gradepane_info();

            //try to include the class
            include_once($classpath);

            //check to see that it was loaded
            if (!class_exists($classname)) {
                throw new coding_exception("Grade pane class $classname is not defined");
            }

            //try to isntantiate it
            try {
                $this->gradepane = new $classname($this);
            } catch (Exception $e) {
                throw new coding_exception("Grade pane class $classname could not be instantiated");
            }
        }

        return $this;
    }

    /**
     * @param local_joulegrader_helper_users $userhelper
     */
    public function current_user($userhelper) {
        return;
    }

    /**
     * @param local_joulegrader_helper_navigation $navhelper
     */
    public function current_navuser(local_joulegrader_helper_navigation $navhelper) {
        return;
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
     * @return int - next gradeable user's id
     */
    public function get_nextuserid() {
        return $this->nextuserid;
    }

    /**
     * @param $nextuserid
     */
    public function set_nextuserid($nextuserid) {
        $this->nextuserid = $nextuserid;
    }

    /**
     * Get the view pane html for the grading area
     *
     * @return string - the html for the view pane
     */
    public function get_viewpane() {
        if (!($this->viewpane instanceof local_joulegrade_lib_pane_view_abstract)) {
            $this->load_viewpane();
            $this->viewpane->init();
        }

        return $this->viewpane;
    }

    /**
     * Get the grade pane for the grading area
     *
     * @return local_joulegrade_lib_pane_grade_abstract
     */
    public function get_gradepane() {
        if (!($this->gradepane instanceof local_joulegrade_lib_pane_grade_abstract)) {
            $this->load_gradepane();
            $this->gradepane->init();
        }

        return $this->gradepane;
    }

    /**
     * @return local_joulegrader_lib_comment_loop
     */
    public function get_commentloop() {
        if (is_null($this->commentloop)) {
            $this->commentloop = new local_joulegrader_lib_comment_loop($this);
        }
        return $this->commentloop;
    }

    /**
     * @param $commentloop
     */
    public function set_commentloop($commentloop) {
        $this->commentloop = $commentloop;
    }


    /**
     * @return bool
     */
    public function has_comments() {
        return true;
    }


    public function get_editor_options() {
        return array(
            'return_types' => FILE_EXTERNAL | FILE_INTERNAL,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'context' => $this->get_gradingmanager()->get_context(),
        );
    }

    /**
     * @param MoodleQuickForm $mform
     */
    public function comment_form_hook($mform) {

    }

    /**
     * @return stdClass
     */
    abstract public function get_comment_filearea_info();

    /**
     * Returns the $options object to be passed to comment/lib.php comment class constructor
     *
     * @return stdClass|null
     */
    abstract public function get_comment_info();

    /**
     * Return the name of and path to the viewpane class that this grading_area class should use
     *
     * @abstract
     */
    abstract protected function get_viewpane_info();

    /**
     * Return the name of and path to the gradepane class that this grading_area class should use
     *
     * @abstract
     */
    abstract protected function get_gradepane_info();
}