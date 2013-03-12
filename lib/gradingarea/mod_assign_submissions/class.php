<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/gradingarea/abstract.php');
/**
 * Grading area class for mod_assign component, submissions areaname
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_gradingarea_mod_assign_submissions_class extends local_joulegrader_lib_gradingarea_abstract {

    /**
     * @var string
     */
    protected static $studentcapability = 'mod/assign:submit';

    /**
     * @var string
     */
    protected static $teachercapability = 'mod/assign:grade';

    /**
     * @var assign - an instance of the assign class
     */
    protected $assign;

    /**
     * @var stdClass - submission record for this assignment & gradeable user
     */
    protected $submission;

    /**
     * @var array
     */
    protected static $supportedsubmissionplugins = array(
        'assign_submission_onlinetext',
        'assign_submission_file',
    );

    public function get_supported_plugins() {
        return self::$supportedsubmissionplugins;
    }

    /**
     * @return array - the viewpane class and path to the class that this gradingarea class should use
     */
    protected function get_viewpane_info() {
        global $CFG;

        return array(
            "$CFG->dirroot/local/joulegrader/lib/pane/view/mod_assign_submissions/class.php",
            "local_joulegrader_lib_pane_view_mod_assign_submissions_class",
        );
    }

    /**
     * @return array - the gradepane class and path to the class the this gradingarea class should use
     */
    protected function get_gradepane_info() {
        global $CFG;

        return array(
            "$CFG->dirroot/local/joulegrader/lib/pane/grade/mod_assign_submissions/class.php",
            "local_joulegrader_lib_pane_grade_mod_assign_submissions_class",
        );
    }

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

        if (!$submission = $DB->get_record('assign_submission', array('id' => $itemid))) {
            return false;
        }

        if ($USER->id != $submission->userid and !has_capability(self::$teachercapability, $context)) {
            return false;
        }

        // Get the filename from args.
        $filename = array_pop($args);

        // May still have the path to determine.
        $filepath = '';
        while (!empty($args)) {
            $filepath .= array_shift($args) . '/';
        }

        $fullpath = '/'.$context->id.'/assignsubmission_file/submission_files/'.$itemid.'/'. $filepath . $filename;

        $fs = get_file_storage();

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, 86400, 0, $forcedownload, $options);
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
            list($cm, $assignment) = self::get_assign_info($gradingmanager);
            $cminfo = $courseinfo->get_cm($cm->id);
            if (has_capability('moodle/course:viewhiddenactivities', $gradingmanager->get_context()) || ($cminfo->available && $cm->visible)) {
                $include = true;
            }

            //check to see if it should be included based on whether the needs grading button was selected
            if (!empty($include) && !empty($needsgrading) && has_capability(self::$teachercapability, context_module::instance($cm->id))) {
                //needs to be limited by "needs grading"
                //check for submissions that do not have a grade yet
                $sql = 'SELECT s.id
                          FROM {assign_submission} s
                     LEFT JOIN {assign_grades} g ON s.assignment = g.assignment AND s.userid = g.userid
                         WHERE s.assignment = ?
                           AND s.timemodified IS NOT NULL
                           AND s.status = ?
                           AND (s.timemodified > g.timemodified OR g.timemodified IS NULL)';

                $params = array($assignment->id, 'submitted');

                // Just need to check that there is at least one ungraded
                $submissions = $DB->get_records_sql($sql, $params, 0, 1);

                if (empty($submissions)) {
                    //if there isn't at least one submission then don't include this
                    $include = false;
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
            list($cm, $assignment) = self::get_assign_info($gradingmanager);

            //check for submissions for this assignment that have timemarked < timemodified for all the users passed in
            list($inorequals, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED);

            //check for submissions that do not have a grade yet
            $sql = "SELECT s.userid
                      FROM {assign_submission} s
                 LEFT JOIN {assign_grades} g ON s.assignment = g.assignment AND s.userid = g.userid
                     WHERE s.assignment = :assignid
                       AND s.userid $inorequals
                       AND s.timemodified IS NOT NULL
                       AND (s.timemodified > g.timemodified OR g.timemodified IS NULL)";

            $params['assignid'] = $assignment->id;

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
     * @return assignment_base
     */
    public function get_assign() {
        //check to see that it's loaded
        if (!isset($this->assign) || !($this->assign instanceof assign)) {
            $this->load_assign();
        }

        return $this->assign;
    }

    /**
     * @static
     * @param $gradingmanager - instance of the grading_manager
     * @return array - cm and assign record
     */
    protected static function get_assign_info(grading_manager $gradingmanager) {
        global $COURSE, $DB;

        //load the course_module from the context
        $cm = get_coursemodule_from_id('assign', $gradingmanager->get_context()->instanceid, $COURSE->id, false, MUST_EXIST);

        //load the assignment record
        $assign = $DB->get_record('assign', array('id' => $cm->instance), '*', MUST_EXIST);

        return array($cm, $assign);
    }

    /**
     * @throws coding_exception
     */
    protected function load_assign() {
        global $CFG, $COURSE;

        try {
            //load the assignment record
            list($cm, $assign) = self::get_assign_info($this->get_gradingmanager());

            //instantiate the assign class
            /// Load up the required assignment code
            require_once($CFG->dirroot.'/mod/assign/locallib.php');
            $this->assign = new assign($this->get_gradingmanager()->get_context(), $cm, $COURSE);

            // Set the db record as the instance
            $this->assign->set_instance($assign);

        } catch (Exception $e) {
            throw new coding_exception('Could not load the assign class: ' . $e->getMessage());
        }
    }

    public function get_submission($create = false) {
        if (empty($this->submission)) {
            $this->submission = $this->load_submission($create);
        }

        return $this->submission;
    }

    protected function load_submission($create) {
        global $DB;

        $assign = $this->get_assign();

        $submission = $DB->get_record('assign_submission', array('assignment' => $assign->get_instance()->id, 'userid' => $this->guserid));

        if ($submission) {
            return $submission;
        }
        if ($create) {
            $submission = new stdClass();
            $submission->assignment   = $assign->get_instance()->id;
            $submission->userid       = $this->guserid;
            $submission->timecreated = time();
            $submission->timemodified = $submission->timecreated;

            if ($assign->get_instance()->submissiondrafts) {
                $submission->status = ASSIGN_SUBMISSION_STATUS_DRAFT;
            } else {
                $submission->status = ASSIGN_SUBMISSION_STATUS_SUBMITTED;
            }
            $sid = $DB->insert_record('assign_submission', $submission);
            $submission->id = $sid;
            return $submission;
        }
        return false;
    }
}
