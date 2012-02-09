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
    protected $supportedareas = array(
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
     * Get the grading areas for the menu
     *
     * @param bool $limited - whether or not areas should be limited to those the logged-in $USER can access
     * @return array - array of grading area activities
     */
    public function get_gradingareas($limited = false) {
        global $DB, $COURSE;

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

            foreach ($this->supportedareas as $component => $areas) {
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

                //first check to see if we should limit the list according to logged-in $USER's capabilities
                if (!empty($limited)) {
                    $capability = 'mod/assignment:submit'; //@TODO - make this dynamic based on gradearea plugin
                    if (!has_capability($capability, context::instance_by_id($garea->contextid))) {
                        //if the menu is limited and the $USER does have capability then continue
                        continue;
                    }
                }

                //@TODO - limit by needs grading param

                $gradingareas[$gareaid] = $gradingareamgr->get_component_title() . ' - ' . $gradingareamgr->get_area_title();
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
}