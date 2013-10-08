<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/grade/grading/lib.php');
require_once($CFG->dirroot . '/local/mr/framework/helper/abstract.php');

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
        'mod_hsuforum' => array(
            'posts',
        ),
        'mod_assign' => array(
            'submissions',
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
     * @param context $context
     */
    public function direct(context $context) {
        $this->load_gradingareas(has_capability('local/joulegrader:grade', $context));
    }

    /**
     * @static
     * @param $currentareaid
     * @param $currentuserid
     *
     * @throws coding_exception
     *
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
        global $DB, $CFG, $COURSE;

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

                $courseinfo = get_fast_modinfo($COURSE);

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
                if (!is_callable("{$classname}::{$includemethod}") || !($classname::$includemethod($courseinfo, $gm))) {
                    //either the method isn't callable or the area shouldn't be included
                    return $areaid;
                }

               $areaid = $arearec->id;
            }
        }

        return $areaid;
    }

    /**
     * @param bool $asteacher
     * @return array
     */
    protected function load_gradingareas($asteacher) {
        global $DB, $COURSE, $CFG;

        if (is_null($this->gradingareas)) {
            //get course context children
            $coursecontext = context_course::instance($COURSE->id);
            $childcontexts = array_keys($coursecontext->get_child_contexts());

            //if there are no child contexts then bail
            if (empty($childcontexts)) {
                //RETURN
                $this->gradingareas = array();
                return;
            }

            // *********************
            // *********************
            // Check to make sure that there are no legacy activities that don't have grading areas
            $this->ensure_grading_areas();

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
            $whereclause = '('.implode(' OR ', $whereorclauses).')';

            //in or equal clause for contexts
            list($inoreqwhere, $inoreqparams) = $DB->get_in_or_equal($childcontexts);

            $whereclause .= " AND contextid $inoreqwhere";
            $whereorparams = array_merge($whereorparams, $inoreqparams);

            //attempt to get the grading_area records
            $gareas = $DB->get_records_select('grading_areas', $whereclause, $whereorparams);

            //get fast modinfo
            $courseinfo = get_fast_modinfo($COURSE);

            //attempt get the visible names for each grading area record from grading area class
            //and add to gradingareas array if not limited by logged-in $USER

            $cmsbyareaid = array();
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
                if (empty($asteacher)) {
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
                $needsgrading = optional_param('needsgrading', 0, PARAM_BOOL);
                $includemethod = 'include_area';
                if (!is_callable("{$classname}::{$includemethod}") || !($classname::$includemethod($courseinfo, $gradingareamgr, $needsgrading))) {
                    //either the method isn't callable or the area shouldn't be included
                    continue;
                }

                $contextinfo = get_context_info_array($garea->contextid);
                $cmsbyareaid[$garea->id] = $contextinfo[2]->id;

                //@TODO - limit by needs grading param (Milestone 2)

                $gradingareas[$gareaid] = shorten_text(format_string($gradingareamgr->get_component_title())); //uncomment this to include the area title . ' - ' . $gradingareamgr->get_area_title();
            }

            //order the gradingareas by course order of activities?
            $gradingareas = $this->order_gradingareas($gradingareas, $courseinfo, $cmsbyareaid);

            //set the gradingareas data member
            $this->gradingareas = $gradingareas;
        }
    }

    /**
     * Get the grading areas for the menu
     * @return array - array of grading area activities
     */
    public function get_gradingareas() {
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
                reset($this->gradingareas);
                $garea = key($this->gradingareas);
            } else if (!array_key_exists($garea, $this->gradingareas) && !empty($this->gradingareas)) {
                reset($this->gradingareas);
                $garea = key($this->gradingareas);;
            }

            //special case where needs grading has excluded all grading areas
            if (empty($this->gradingareas) && optional_param('needsgrading', 0, PARAM_BOOL)) {
                $garea = null;
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
     * @param array $gradingareas
     * @param course_modinfo $courseinfo
     * @param array $cmsbyareaid - array of cm ids arrays keyed by grading area id
     *
     * @return array - ordered grading areas
     */
    protected function order_gradingareas($gradingareas, course_modinfo $courseinfo, $cmsbyareaid) {
        //if grading areas is empty, return empty array
        if (empty($gradingareas)) {
            //nothing to see here, move along
            return array();
        }

        //if $contextinfo is empty then we can't order the grading areas
        if (empty($cmsbyareaid)) {
            return $gradingareas;
        }

        //course modules in order from course_modinfo
        $cms = $courseinfo->get_cms();
        if (empty($cms)) {
            return $gradingareas;
        }

        //this gives us cm position 0.n keyed by cmid
        $cmidpositions = array_flip(array_keys($cms));

        $orderedareas = array();
        $unorderedareas = array();
        foreach ($cmsbyareaid as $areaid => $cmid) {
            if (array_key_exists($cmid, $cmidpositions)) {
                $pos = $cmidpositions[$cmid];
                if (!isset($orderedareas[$pos])) {
                    //set the areaids position
                    $orderedareas[$pos] = $areaid;

                    //continue to the next area
                    continue;
                }
            }

            //if it couldn't be ordered then add to unordered to append later
            $unorderedareas[] = $areaid;
        }

        //sort ordered areas array by key (position)
        ksort($orderedareas);

        $orderareasfinal = array();
        //now loop through ordered areas and get make the grading areas menu again
        foreach ($orderedareas as $areaid) {
            $orderareasfinal[$areaid] = $gradingareas[$areaid];
        }

        //add any unordered areas to the end
        foreach ($unorderedareas as $areaid) {
            $orderareasfinal[$areaid] = $gradingareas[$areaid];
        }

        return $orderareasfinal;
    }

    /**
     * Checks for assignments and advanced forums that don't have grading area records
     *
     * @ Todo: caching; make dynamic for other components (activities) and areanames
     */
    protected function ensure_grading_areas() {
        global $COURSE, $DB;

        // activity context level
        $modcontext = CONTEXT_MODULE;

        // build sql query
        $sql = <<<EOL
    SELECT c.id AS contextid, 'mod_assignment' AS component, 'submission' AS areaname
      FROM {assignment} a
INNER JOIN {course_modules} cm ON (cm.instance = a.id AND cm.course = ?)
INNER JOIN {modules} m ON (m.name = 'assignment' AND m.id = cm.module)
INNER JOIN {context} c ON (c.instanceid = cm.id AND c.contextlevel = ?)
 LEFT JOIN {grading_areas} ga ON (ga.contextid = c.id)
     WHERE ga.id IS NULL
 UNION ALL
    SELECT c.id AS contextid, 'mod_hsuforum' AS component, 'posts' AS areaname
      FROM {hsuforum} af
INNER JOIN {course_modules} cm ON (cm.instance = af.id AND cm.course = ?)
INNER JOIN {modules} m ON (m.name = 'hsuforum' AND m.id = cm.module)
INNER JOIN {context} c ON (c.instanceid = cm.id AND c.contextlevel = ?)
 LEFT JOIN {grading_areas} ga ON (ga.contextid = c.id)
     WHERE ga.id IS NULL
EOL;

        // query params
        $params = array($COURSE->id, $modcontext, $COURSE->id, $modcontext);

        try {
            // get the recordset
            $rs = $DB->get_recordset_sql($sql, $params);

            // iterate the recordset and add new grading_areas records
            foreach ($rs as $record) {
                try {
                    // insert the record
                    $DB->insert_record('grading_areas', $record);
                } catch (Exception $e) {
                    // catch exceptions from insert attempt and ignore it
                }
            }

            $rs->close();
        } catch (Exception $e) {
            // forget about it
        }

    }

    /**
     * @return array
     */
    public static function get_supportedareas() {
        return self::$supportedareas;
    }
}