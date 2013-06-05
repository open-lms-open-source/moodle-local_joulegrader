<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/gradingarea/abstract.php');

/**
 * Grading area class for mod_assignment component, submission areaname
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_gradingarea_mod_assignment_submission_class extends local_joulegrader_lib_gradingarea_abstract {

    /**
     * @var string
     */
    protected static $studentcapability = 'mod/assignment:submit';

    /**
     * @var string
     */
    protected static $teachercapability = 'mod/assignment:grade';

    /**
     * @var assignment_base - an instance of the assignment_base class (or most likely one of its subclasses)
     */
    protected $assignment;

    /**
     * @var stdClass - submission record for this assignment & gradeable user
     */
    protected $submission;

    /**
     * @var array - the assignment types supported by joule Grader
     */
    public static $supportedtypes = array(
        'online',
        'offline',
        'uploadsingle',
        'upload'
    );

    /**
     * @static
     * @param $course
     * @param $cm
     * @param $context
     * @param $itemid
     * @param $args
     * @param $forcedownload
     * @return bool
     */
    public static function pluginfile($course, $cm, $context, $itemid, $args, $forcedownload, $options) {
        global $USER, $DB;

        //should only be the filename left in the args
        if (count($args) != 1) {
            return false;
        }

        if (!$submission = $DB->get_record('assignment_submissions', array('id' => $itemid))) {
            return false;
        }

        if ($USER->id != $submission->userid and !has_capability(self::$teachercapability, $context)) {
            return false;
        }

        //get the "real" component and filearea
        $filename = array_shift($args);

        $fullpath = '/'.$context->id.'/mod_assignment/submission/'.$itemid.'/'.$filename;

        $fs = get_file_storage();

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, 86400, 0, $forcedownload, $options);
}


    /**
     * @static
     * @param $gradingmanager - instance of the grading_manager
     * @return array - cm and assignment record
     */
    protected static function get_assignment_info(grading_manager $gradingmanager) {
        global $DB;

        //load the course_module from the context
        $cm = get_coursemodule_from_id('assignment', $gradingmanager->get_context()->instanceid, 0, false, MUST_EXIST);

        //load the assignment record
        $assignment = $DB->get_record("assignment", array("id"=>$cm->instance), '*', MUST_EXIST);

        return array($cm, $assignment);
    }

    /**
     * @static
     * @param course_modinfo $courseinfo
     * @param grading_manager $gradingmanager
     * @param bool $needsgrading
     * @return bool
     */
    public static function include_area(course_modinfo $courseinfo, grading_manager $gradingmanager, $needsgrading = false) {
        global $DB;
        $include = false;

        try {
            list($cm, $assignment) = self::get_assignment_info($gradingmanager);
            if (in_array($assignment->assignmenttype, self::$supportedtypes)) {
                $cminfo = $courseinfo->get_cm($cm->id);
                if (has_capability('moodle/course:viewhiddenactivities', $gradingmanager->get_context()) || ($cminfo->available && $cm->visible)) {
                    $include = true;
                }

                //check to see if it should be included based on whether the needs grading button was selected
                if (!empty($include) && !empty($needsgrading) && has_capability(self::$teachercapability, context_module::instance($cm->id))) {
                    //needs to be limited by "needs grading"
                    //check for submissions for this assignment that have timemarked < timemodified
                    $submissions = $DB->get_records_select('assignment_submissions', 'assignment = ? AND timemarked < timemodified'
                            , array($assignment->id), '', 'id', 0, 1);

                    if (empty($submissions)) {
                        //if there isn't at least one submission then don't inlude this
                        $include = false;
                    }
                }
            }
        } catch (Exception $e) {
            //don't need to do anything
        }

        return $include;
    }

    /**
     * @static
     * @param array $users
     * @param grading_manager $gradingmanager
     * @param bool $needsgrading
     *
     * @return bool
     */
    public static function include_users($users, grading_manager $gradingmanager, $needsgrading) {
        global $DB;
        $include = array();

        // right now only need to narrow users if $needsgrading is true
        if (empty($needsgrading) || empty($users)) {
            // return the users array as it was passed
            return $users;
        }

        // narrow the users to those that have submissions that have not been graded since they were modified
        try {
            list($cm, $assignment) = self::get_assignment_info($gradingmanager);

            //check for submissions for this assignment that have timemarked < timemodified for all the users passed in
            list($inorequals, $params) = $DB->get_in_or_equal(array_keys($users));
            $sql = "SELECT asb.userid
                      FROM {assignment_submissions} asb
                     WHERE asb.userid $inorequals
                       AND asb.assignment = ?
                       AND asb.timemarked < asb.timemodified
                  GROUP BY asb.userid";

            $params[] = $assignment->id;

            // execute the query
            $submissionusers = $DB->get_records_sql($sql, $params);

            if (!empty($submissionusers)) {
                foreach ($submissionusers as $subuserid => $nada) {
                    if (!array_key_exists($subuserid, $users)) {
                        // this should not happen but just in case
                        continue;
                    }
                    $include[$subuserid] = $users[$subuserid];
                }
            }


        } catch (Exception $e) {

        }

        return $include;
    }

    /**
     * @return array - the viewpane class and path to the class that this gradingarea class should use
     */
    protected function get_viewpane_info() {
        global $CFG;

        // get the assignment and assignment type
        $assignment = $this->get_assignment();
        $assignmenttype = $assignment->type;

        return array(
            "$CFG->dirroot/local/joulegrader/lib/pane/view/mod_assignment_submission/$assignmenttype.php",
            "local_joulegrader_lib_pane_view_mod_assignment_submission_$assignmenttype",
        );
    }

    /**
     * @return array - the gradepane class and path to the class the this gradingarea class should use
     */
    protected function get_gradepane_info() {
        global $CFG;

        return array(
            "$CFG->dirroot/local/joulegrader/lib/pane/grade/mod_assignment_submission/class.php",
            "local_joulegrader_lib_pane_grade_mod_assignment_submission_class",
        );
    }

    /**
     * @return assignment_base
     */
    public function get_assignment() {
        //check to see that it's loaded
        if (!isset($this->assignment) || !($this->assignment instanceof assignment_base)) {
            $this->load_assignment();
        }

        return $this->assignment;
    }

    /**
     * @throws coding_exception
     */
    protected function load_assignment() {
        global $CFG;

        try {
            //load the assignment record
            list($cm, $assignment) = self::get_assignment_info($this->get_gradingmanager());

            /// Load up the required assignment code
            require_once($CFG->dirroot.'/mod/assignment/lib.php');
            require_once($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
            $assignmentclass = 'assignment_'.$assignment->assignmenttype;

            //instantiate the assignment class
            $this->assignment = new $assignmentclass($cm->id, $assignment, $cm, null);
        } catch (Exception $e) {
            throw new coding_exception('Could not load the assignment class: ' . $e->getMessage());
        }
    }

    /**
     * @param $create
     * @return stdClass - the submission record
     */
    public function get_submission($create = false) {
        //if it's not set try to load it
        if (empty($this->submission)) {
            $this->load_submission($create);
        }
        return $this->submission;
    }

    /**
     * @return stdClass
     */
    public function get_comment_info() {
        $options          = new stdClass();
        $options->area    = 'submission_comments';
        $options->context = $this->get_gradingmanager()->get_context();
        $options->itemid  = $this->get_submission(true)->id;
        $options->component = 'mod_assignment';

        return $options;
    }

    /**
     * @return stdClass File area information for use in comments
     */
    public function get_comment_filearea_info() {
        return (object) array(
            'component' => 'mod_assignment',
            'filearea' => 'comments'
        );
    }

    /**
     * Load the submission record for the set user / assignment
     *
     * @param $create
     */
    protected function load_submission($create) {
        $assignment = $this->get_assignment();

        try {
            $this->submission = $assignment->get_submission($this->guserid, $create, true);
        } catch (Exception $e) {
            throw new coding_exception("Could not load the submission for assignment: $assignment->assignment->name, userid: $this->guser");
        }
    }
}