<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/grade/grading/lib.php');

/**
 * joule Grader grading areas helper
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_helper_gradingareas extends mr_helper_abstract {

    /**
     * Grading areas currently supported by joule Grader
     *
     * @var array - multidimesion array of grading_area compenents and areas
     */
    protected static $supportedareas = array(
        'mod_assignment' => array(
            'submission',
        ),
    );

    /**
     * @var int - id of the current grading area
     */
    protected $currentarea;

    /**
     * @var int - id of the next grading area
     */
    protected $nextarea;

    /**
     * @var int - id of the previous grading area
     */
    protected $prevarea;

    /**
     * @var array - grading areas available for the course
     */
    protected $gradingareas;

    /**
     * Main method into helper
     */
    public function direct() {}

    /**
     * @static
     * @param $currentareaid
     * @param $currentuserid
     * @return local_joulegrader_lib_gradingarea_abstract - instance of a gradingarea class
     */
    public static function get_gradingarea_instance($currentareaid, $currentuserid) {
        global $CFG;

        $gradingmanager = get_grading_manager($currentareaid);

        //component and area names of the grading area
        $component = $gradingmanager->get_component();
        $area = $gradingmanager->get_area();

        $classname = "local_joulegrader_lib_gradingarea_{$component}_{$area}_class";

        //include the class
        include_once("$CFG->dirroot/local/joulegrader/lib/gradingarea/{$component}_{$area}/class.php");

        //check to be sure the class was loaded
        if (!class_exists($classname)) {
            throw new coding_exception("Class: $classname does not exist or could not be loaded");
        }

        return new $classname($gradingmanager, $currentareaid, $currentuserid);
    }

    /**
     * @static
     * @param context $context
     * @param string $activityname
     * @return int - an areaid from grading_areas table
     */
    public static function get_areaid_from_context_activityname(context $context, $activityname) {
        global $DB, $CFG;

        //initialize
        $areaid = 0;

        //get a grading manager
        $gm = get_grading_manager($context, $activityname);

        //check to make sure this supports grading areas
        $supportedareas = self::$supportedareas;

        //get the component
        $component = $gm->get_component();

        if (array_key_exists($component, $supportedareas)) {
            //there are grading areas supported, since we don't really know which area they may be after,
            //pick the first one
            if ($arearec = $DB->get_record('grading_areas', array('contextid' => $context->id, 'component' => $gm->get_component())
                , 'id', IGNORE_MULTIPLE)) {

                //we've got an area id
                $gm->load($arearec->id);
                $area = $gm->get_area();

                $classname = "local_joulegrader_lib_gradingarea_{$component}_{$area}_class";
                //include the class
                include_once("$CFG->dirroot/local/joulegrader/lib/gradingarea/{$component}_{$area}/class.php");

                //check to be sure the class was loaded
                if (!class_exists($classname)) {
                    //if not, then return nothing
                    return $areaid;
                }

                //give the grading_area class an opportunity to exclude this particular grading_area
                $includemethod = 'include_area';
                if (!is_callable("{$classname}::{$includemethod}") || !($classname::$includemethod($gm, false))) {
                    //either the method isn't callable or the area shouldn't be included
                    return $areaid;
                }

               $areaid = $arearec->id;
            }
        }

        return $areaid;
    }

    /**
     * Get the grading areas for the menu
     *
     * @param bool $asstudent - is this being viewed as a student
     * @return array - array of grading area activities
     */
    public function get_gradingareas($asstudent = false) {
        global $DB, $COURSE, $CFG;

        if (is_null($this->gradingareas)) {
            //get course context children
            $coursecontext = context_course::instance($COURSE->id);
            $childcontexts = array_keys($coursecontext->get_child_contexts());

            //if there are no child contexts then bail
            if (empty($childcontexts)) {
                //RETURN EMPTY ARRAY
                return array();
            }

            //determine where clause based on supported grading_areas
            $whereorclauses = array();
            $whereorparams  = array();

            foreach (self::$supportedareas as $component => $areas) {
                foreach ($areas as $area) {
                    $whereorclauses[] = '(component = ? AND areaname = ?)';
                    $whereorparams[]  = $component;
                    $whereorparams[]  = $area;
                }
            }

            //create the where clause
            $whereclause = implode(' OR ', $whereorclauses);

            //in or equal clause for contexts
            list($inoreqwhere, $inoreqparams) = $DB->get_in_or_equal($childcontexts);

            $whereclause .= " AND contextid $inoreqwhere";
            $whereorparams = array_merge($whereorparams, $inoreqparams);

            //attempt to get the grading_area records
            $gareas = $DB->get_records_select('grading_areas', $whereclause, $whereorparams);

            //attempt get the visible names for each grading area record from grading area class
            //and add to gradingareas array if not limited by logged-in $USER
            $gradingareas = array();
            foreach ($gareas as $gareaid => $garea) {
                //get the grading manager
                $gradingareamgr = get_grading_manager($gareaid);

                //determine classname based on the grading area
                $component = $gradingareamgr->get_component();
                $area = $gradingareamgr->get_area();

                $classname = "local_joulegrader_lib_gradingarea_{$component}_{$area}_class";

                //include the class
                include_once("$CFG->dirroot/local/joulegrader/lib/gradingarea/{$component}_{$area}/class.php");

                //check to be sure the class was loaded
                if (!class_exists($classname)) {
                    //if not, then skip this grading area
                    continue;
                }

                //is this being viewed as a student?
                if (!empty($asstudent)) {
                    $method = 'get_studentcapability';
                } else {
                    $method = 'get_teachercapability';
                }

                //make sure the method to get the required capability
                if (!is_callable("{$classname}::{$method}")) {
                    //continue
                    continue;
                }

                $capability = $classname::$method();
                if (!has_capability($capability, context::instance_by_id($garea->contextid))) {
                    //if the menu is limited and the $USER does have capability then continue
                    continue;
                }

                //give the grading_area class an opportunity to exclude this particular grading_area
                $includemethod = 'include_area';
                if (!is_callable("{$classname}::{$includemethod}") || !($classname::$includemethod($gradingareamgr, $asstudent))) {
                    //either the method isn't callable or the area shouldn't be included
                    continue;
                }

                //@TODO - limit by needs grading param

                $gradingareas[$gareaid] = shorten_text(format_string($gradingareamgr->get_component_title())); //uncomment this to include the area title . ' - ' . $gradingareamgr->get_area_title();
            }

            $this->gradingareas = $gradingareas;
        }

        return $this->gradingareas;
    }

    /**
     * Get id of the current grading area
     *
     * @return int - id of the current grading area
     */
    public function get_currentarea() {

        if (is_null($this->currentarea)) {
            //check for a passed parameter
            $garea = optional_param('garea', 0, PARAM_INT);

            //if no param passed take the first area in the course (in the menu)
            if (empty($garea) && !empty($this->gradingareas)) {
                $garea = array_shift(array_keys($this->gradingareas));
            } else if (!array_key_exists($garea, $this->gradingareas) && !empty($this->gradingareas)) {
                $garea = array_shift(array_keys($this->gradingareas));
            }

            $this->currentarea = $garea;
        }

        return $this->currentarea;
    }

    /**
     * Get the id of the next grading area
     *
     * @return int - the id of the next area
     */
    public function get_nextarea() {

        if (is_null($this->nextarea) && !empty($this->gradingareas) && count($this->gradingareas) > 1) {
            $this->find_previous_and_next();
        }

        return $this->nextarea;
    }

    /**
     * Get the id of the previous grading area
     *
     * @return int - the id of the previous area
     */
    public function get_prevarea() {

        if (is_null($this->prevarea) && !empty($this->gradingareas) && count($this->gradingareas) > 1) {
            $this->find_previous_and_next();
        }

        return $this->prevarea;
    }

    /**
     * Find the previous and next area ids
     */
    protected function find_previous_and_next() {
        $currentarea = $this->get_currentarea();
        $areaids     = array_keys($this->gradingareas);
        $previd      = null;
        $nextid      = null;

        //try to get the area before the current area
        while (list($unused, $areaid) = each($areaids)) {
            if ($areaid == $currentarea) {
                break;
            }
            $previd = $areaid;
        }

        //if we haven't reached the end of the array, current should give "nextid"
        $nextid = current($areaids);

        reset($areaids);
        if ($nextid === false) {
            //the current category is the last so start at the beginning
            $nextid = $areaids[0];
        } else if ($previd === null) {
            //the current category is the first so get the last
            $previd = end($areaids);
        }

        $this->prevarea = $previd;
        $this->nextarea = $nextid;
    }

    /**
     * @return array
     */
    public static function get_supportedareas() {
        return self::$supportedareas;
    }
}