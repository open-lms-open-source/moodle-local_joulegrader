<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Grading area for assignment
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\gradingarea;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Grading area class for mod_assign component, submissions areaname
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class mod_assign_submissions extends gradingarea_abstract {

    /**
     * @var string
     */
    protected static $studentcapability = 'mod/assign:submit';

    /**
     * @var string
     */
    protected static $teachercapability = 'mod/assign:grade';

    /**
     * @var \assign - an instance of the assign class
     */
    protected $assign;

    /**
     * @var \stdClass - submission record for this assignment & gradeable user
     */
    protected $submission;

    /**
     * @var int - submission extension time.
     */
    protected $submissionextension = null;

    /**
     * @var array
     */
    protected static $supportedsubmissionplugins = array(
        'assign_submission_onlinetext',
        'assign_submission_file',
    );

    /**
     * @var int
     */
    protected $attemptnumber;

    public function get_supported_plugins() {
        return self::$supportedsubmissionplugins;
    }

    /**
     * @return array - the viewpane class and path to the class that this gradingarea class should use
     */
    protected function get_viewpane_info() {
        return array(
            '',
            "\\local_joulegrader\\pane\\view\\mod_assign_submissions",
        );
    }

    /**
     * @return array - the gradepane class and path to the class the this gradingarea class should use
     */
    protected function get_gradepane_info() {
        return array(
            '',
            "\\local_joulegrader\\pane\\grade\\mod_assign_submissions",
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
     * @param $options
     * @return bool
     */
    public static function pluginfile($course, $cm, $context, $itemid, $args, $forcedownload, $options) {
        global $USER, $DB, $CFG;

        if (!$submission = $DB->get_record('assign_submission', array('id' => $itemid))) {
            return false;
        }

        if (!$assign = $DB->get_record('assign', array('id' => $submission->assignment))) {
            return false;
        }

        if (!empty($assign->teamsubmission) && $submission->userid == 0) {
            // Check permissions.
            $hasgradecap = has_capability('mod/assign:grade', $context);

            if (!$hasgradecap) {
                require_once($CFG->dirroot . '/mod/assign/locallib.php');

                // Need to see if the $USER is a member of the group.
                $assignobj = new \assign($context, $cm, $course);
                $assignobj->set_instance($assign);

                $groupmembers = $assignobj->get_submission_group_members($submission->groupid, true);
                $ismember = false;
                foreach ($groupmembers as $member) {
                    if ($member->id == $USER->id) {
                        $ismember = true;
                        break;
                    }
                }

                if (!$ismember || ($ismember && !has_capability('mod/assign:submit', $context))) {
                    return false;
                }
            }
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
     * @param \course_modinfo $courseinfo
     * @param \grading_manager $gradingmanager
     * @param bool $needsgrading
     * @param int $currentgroup
     * @return bool
     */
    public static function include_area(\course_modinfo $courseinfo, \grading_manager $gradingmanager, $needsgrading = false,
            $currentgroup = 0) {
        global $USER, $DB, $CFG;
        $include = false;

        try {
            require_once($CFG->libdir.'/gradelib.php');
            list($cm, $assignment) = self::get_assign_info($gradingmanager);
            $cminfo = $courseinfo->get_cm($cm->id);
            if (
                has_capability('moodle/course:viewhiddenactivities', $gradingmanager->get_context()) ||
                // If the user is a teacher, they can see disabled activities, hidden to students unless they are available.
                (has_capability(self::$teachercapability, $gradingmanager->get_context()) && $cminfo->visible) ||
                ($cminfo->available && $cm->visible)
            ) {
                $include = true;
            }

            if (!empty($needsgrading) && $assignment->grade == 0) {
                $include = false;
            }

            $context = \context_module::instance($cm->id);

            // Check to see if it should be included based on whether the needs grading button was selected.
            if (!empty($include) && !empty($needsgrading) && has_capability(self::$teachercapability, $context)) {
                // Needs to be limited by "needs grading".
                // Check for submissions that do not have a grade yet.

                // Check to see if this assignment uses team submissions.
                if (!empty($assignment->teamsubmission)) {
                    require_once($CFG->dirroot . '/mod/assign/locallib.php');
                    // Team submissions are being used.
                    $sql = 'SELECT s.*
                              FROM {assign_submission} s
                             WHERE s.assignment = :assignid
                               AND s.timemodified IS NOT NULL
                               AND s.userid = :groupuserid
                               AND s.status = :submissionstatus
                               -- limit to latest submissions
                               AND latest = 1';

                    $params = array('assignid' => $assignment->id, 'groupuserid' => 0, 'submissionstatus' => ASSIGN_SUBMISSION_STATUS_SUBMITTED);
                    $submissions = $DB->get_records_sql($sql, $params);

                    if (empty($submissions)) {
                        // No submissions at all, no need to proceed.
                        $include = false;
                    } else {
                        $include = false;
                        $context = \context_module::instance($cm->id);
                        $assign = new \assign($context, $cm, null);
                        $course = $courseinfo->get_course();
                        $allgroups = true;
                        if ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
                            $allgroups = false;
                            $groups = groups_get_user_groups($course->id);
                        }
                        foreach ($submissions as $submission) {
                            if (!$allgroups and (empty($groups) or !in_array($submission->groupid, $groups[0]))) {
                                continue;
                            }
                            $groupusers = $assign->get_submission_group_members($submission->groupid, true);

                            foreach ($groupusers as $groupuser) {
                                $grade = $assign->get_user_grade($groupuser->id, false, $submission->attemptnumber);
                                if (empty($grade) OR is_null($grade->timemodified) OR $submission->timemodified > $grade->timemodified) {
                                    // Found a user that needs a grade updated
                                    $include = true;
                                    break 2;
                                }
                            }
                        }
                    }

                } else {
                    require_once($CFG->dirroot.'/mod/assign/locallib.php');

                    list($enrolsql, $enrolparams) = get_enrolled_sql($gradingmanager->get_context(), self::$studentcapability, $currentgroup);
                    // Team submissions are not being used, this simplifies the check.
                    $sql = "SELECT s.id
                          FROM {assign_submission} s
                    INNER JOIN ($enrolsql) enrol ON (enrol.id = s.userid)
                     LEFT JOIN {assign_grades} g ON s.assignment = g.assignment AND s.userid = g.userid AND s.attemptnumber = g.attemptnumber
                         WHERE s.assignment = :assign
                           AND s.timemodified IS NOT NULL
                           AND s.status = :status
                           AND s.userid <> 0
                           AND (s.timemodified > g.timemodified OR g.timemodified IS NULL OR g.grade = -1 OR g.grade IS NULL)
                           -- limit to latest submissions
                           AND s.latest = 1";

                    $params = array('assign' => $assignment->id, 'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED);
                    $params = array_merge($params, $enrolparams);

                    // Just need to check that there is at least one ungraded.
                    $submissions = $DB->get_records_sql($sql, $params, 0, 1);

                    if (empty($submissions)) {
                        // If there isn't at least one submission then don't include this.
                        $include = false;
                    }
                }
            } else if ($include && !has_capability(self::$teachercapability, $context)) {
                if (self::should_hide_from_nongrader('assign', $assignment->id, $courseinfo->courseid, $USER->id)) {
                    $include = false;
                };
            }
        } catch (\Exception $e) {
            //don't need to do anything
        }

        return $include;
    }

    /**
     * @static
     * @param array $users
     * @param \grading_manager $gradingmanager
     * @param bool $needsgrading
     *
     * @return bool
     */
    public static function include_users($users, \grading_manager $gradingmanager, $needsgrading) {
        global $DB, $CFG, $USER;
        $include = array();

        try {
            list($cm, $assignment) = self::get_assign_info($gradingmanager);
            $context = \context_module::instance($cm->id);
            // First limit by assigned markers if necessary.
            if (!empty($assignment->markingallocation) && !has_capability('mod/assign:manageallocations', $context)) {
                $loggedinuser = null;
                if (isset($users[$USER->id])) {
                    // Preserve the logged in user in this list as he/she is enrolled as student as well.
                    $loggedinuser = $users[$USER->id];
                }
                $userflagparams  = array(
                    'assignment' => $assignment->id,
                    'allocatedmarker'     => $USER->id,
                );
                $assignuserflags = $DB->get_records_menu('assign_user_flags', $userflagparams, '', 'userid, allocatedmarker');
                if (empty($assignuserflags)) {
                    $users = array();
                } else {
                    $users = array_intersect_key($users, $assignuserflags);
                }

                if (!empty($loggedinuser)) {
                    $users[$USER->id] = $loggedinuser;
                }
            }

            if (empty($users)) {
                return $users;
            }

            if (!empty($needsgrading)) {
                // Narrow the users to those that have submissions that have not been graded since they were modified.
                if (!empty($assignment->teamsubmission)) {
                    // Team submissions is being used.
                    require_once($CFG->dirroot . '/mod/assign/locallib.php');
                    $assign = new \assign($context, $cm, null);

                    $groupswithnosubmission = array();
                    foreach ($users as $user) {
                        $groupid = 0;
                        $submissiongroup = $assign->get_submission_group($user->id);
                        if (!empty($submissiongroup)) {
                            $groupid = $submissiongroup->id;
                        }
                        if (in_array($groupid, $groupswithnosubmission)) {
                            // Already determined not to have a submission, skip the user.
                            continue;
                        }
                        $submission = $assign->get_group_submission($user->id, $groupid, false);
                        if (empty($submission) OR is_null($submission->timemodified) OR $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                            // No submission yet for this group. Keep the group so we can skip other group members.
                            $groupswithnosubmission[] = $groupid;
                            continue;
                        }

                        $grade = $assign->get_user_grade($user->id, false);
                        if (empty($grade) OR is_null($grade->timemodified) OR $submission->timemodified > $grade->timemodified) {
                            $include[$user->id] = $user;
                        }
                    }

                } else {
                    // Team submissions are not being used.
                    // Check for submissions for this assignment that have timemarked < timemodified for all the users passed.
                    list($inorequals, $params) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED);

                    // Check for submissions that do not have a grade yet.
                    // Add latest field check to ensure latest attempt is graded.
                    $sql = "SELECT s.userid
                          FROM {assign_submission} s
                     LEFT JOIN {assign_grades} g ON s.assignment = g.assignment AND s.userid = g.userid AND s.attemptnumber = g.attemptnumber
                         WHERE s.assignment = :assignid
                           AND s.status = :status
                           AND s.userid $inorequals
                           AND s.timemodified IS NOT NULL
                           AND s.latest = 1
                           AND (s.timemodified > g.timemodified OR g.timemodified IS NULL OR g.grade = -1 OR g.grade IS NULL)";

                    $params['assignid'] = $assignment->id;
                    $params['status']   = ASSIGN_SUBMISSION_STATUS_SUBMITTED;

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

                }
            } else {
                $include = $users;
            }

            // Check to see if it is necessary to anonymize users for blind marking.
            if (!empty($assignment->blindmarking) && empty($assignment->revealidentities) && !empty($include)) {
                require_once($CFG->dirroot . '/mod/assign/locallib.php');
                $hiddenuserstr = get_string('hiddenuser', 'assign');
                foreach ($include as $userid => $user) {
                    $uniqueid = \assign::get_uniqueid_for_user_static($assignment->id, $userid);
                    $include[$userid]->firstname = trim($hiddenuserstr);
                    $include[$userid]->lastname = $uniqueid;
                }

                uasort($include, function($a, $b) {
                    $return = 0;
                    if ($a->lastname > $b->lastname) {
                        $return = 1;
                    } else if ($a->lastname < $b->lastname) {
                        $return = -1;
                    }

                    return $return;
                });
            }


        } catch (\Exception $e) {
            if (debugging('', DEBUG_DEVELOPER)) {
                throw $e;
            }
        }

        return $include;
    }

    public static function loggedinuser_can_grade(\grading_manager $gradingmanager, $loggedinuser, $usertograde = null) {
        global $DB;
        list($cm, $assignrecord) = self::get_assign_info($gradingmanager);
        $context = \context_module::instance($cm->id);

        if (!has_capability('mod/assign:grade', $context, $loggedinuser)) {
            // Must have this capability to do any kind of grading. Return false here since they don't.
            return false;
        }

        if (!empty($assignrecord->markingallocation)) {
            // Using marking allocation and has the capability to be a marker.
            if (!has_capability('mod/assign:manageallocations', $context, $loggedinuser)) {
                // Logged in user can't allocate markers so they must be allocated to a marker.
                $userflagparams = array('assignment' => $assignrecord->id, 'allocatedmarker' => $loggedinuser);
                if (!empty($usertograde)) {
                    $userflagparams['userid'] = $usertograde;
                }
                return ($DB->count_records('assign_user_flags', $userflagparams));
            }
        }

        return true;
    }

    public function __construct(\grading_manager $gradingmanager, $areaid, $guserid) {
        parent::__construct($gradingmanager, $areaid, $guserid);

        $this->attemptnumber = optional_param('attempt', -1, PARAM_INT);
    }

    /**
     * @return \assign
     */
    public function get_assign() {
        //check to see that it's loaded
        if (!isset($this->assign) || !($this->assign instanceof \assign)) {
            $this->load_assign();
        }

        return $this->assign;
    }

    /**
     * @param \local_joulegrader\utility\users $userutility
     */
    public function current_user($userutility) {
        global $COURSE, $USER;

        if ($USER->id == $this->guserid) {
            return;
        }

        $preferences = new \mr_preferences($COURSE->id, 'local_joulegrader');
        $previousarea = $preferences->get('previousarea', null);

        if (!is_null($previousarea) and $previousarea != $this->areaid) {
            if ($this->get_assign()->is_blind_marking()) {
                $userkeys = array_keys($userutility->get_items());
                $currentuser = array_shift($userkeys);
                $userutility->set_currentuser($currentuser);
                $this->guserid = $userutility->get_current();
            }
        }
    }

    /**
     * @param \local_joulegrader\utility\navigation $navutility
     */
    public function current_navuser(\local_joulegrader\utility\navigation $navutility) {
        if ($this->get_assign()->is_blind_marking()) {
            $navutility->set_navcurrentuser(null);
        }
    }

    /**
     * @static
     * @param \grading_manager $gradingmanager - instance of the grading_manager
     * @return array - cm and assign record
     */
    protected static function get_assign_info(\grading_manager $gradingmanager) {
        global $DB;

        //load the course_module from the context
        $cm = get_coursemodule_from_id('assign', $gradingmanager->get_context()->instanceid, 0, false, MUST_EXIST);

        //load the assignment record
        $assign = $DB->get_record('assign', array('id' => $cm->instance), '*', MUST_EXIST);

        return array($cm, $assign);
    }

    /**
     * @throws \coding_exception
     */
    protected function load_assign() {
        global $CFG;

        try {
            //load the assignment record
            list($cm, $assign) = self::get_assign_info($this->get_gradingmanager());

            //instantiate the assign class
            /// Load up the required assignment code
            require_once($CFG->dirroot.'/mod/assign/locallib.php');
            $this->assign = new \assign($this->get_gradingmanager()->get_context(), $cm, null);

            // Set the db record as the instance
            $this->assign->set_instance($assign);

        } catch (\Exception $e) {
            throw new \coding_exception('Could not load the assign class: ' . $e->getMessage());
        }
    }

    public function get_submission() {
        if (empty($this->submission)) {
            $this->submission = $this->load_submission();
        }

        return $this->submission;
    }

    public function get_submission_extension() {
        if (is_null($this->submissionextension)) {
            $this->submissionextension = $this->load_submission_extension();
        }

        return $this->submissionextension;
    }

    /**
     * @return \stdClass
     */
    public function get_comment_info() {
        $options          = new \stdClass();
        $options->area    = 'submission_comments';
        $options->course  = $this->get_assign()->get_course();
        $options->context = $this->get_assign()->get_context();
        $options->itemid  = $this->get_submission()->id;
        $options->component = 'assignsubmission_comments';

        return $options;
    }

    /**
     * @return bool
     */
    public function has_comments() {
        $hascomments = false;
        $submission = $this->get_submission();
        if (!empty($submission)) {
            $hascomments = $this->get_assign()->get_submission_plugin_by_type('comments')->is_enabled();
        }

        return $hascomments;
    }

    /**
     * @return \stdClass File area information for use in comments
     */
    public function get_comment_filearea_info() {
        return (object) array(
            'component' => 'assignsubmission_comments',
            'filearea' => 'comments'
        );
    }

    /**
     * Loads the submission
     *
     * @return mixed
     */
    protected function load_submission() {
        $assign = $this->get_assign();
        $teamsubmission = $assign->get_instance()->teamsubmission;

        if (!empty($teamsubmission)) {
            // Team submissions enabled, get the group submission.
            $submission = $assign->get_group_submission($this->guserid, 0, false, $this->attemptnumber);
        } else {
            // Team submissions not enabled, get the user's submission.
            $submission = $assign->get_user_submission($this->guserid, false, $this->attemptnumber);
        }

        return $submission;
    }

    /**
     * Loads and returns the extension time. 0 for no extension.
     *
     * @return int
     */
    protected function load_submission_extension() {
        $assign = $this->get_assign();

        $userflags = $assign->get_user_flags($this->guserid, false);

        if ($userflags && !empty($userflags->extensionduedate)) {
            return $userflags->extensionduedate;
        } else {
            return 0;
        }
    }

    /**
     * Get the submissions for all previous attempts.
     *
     * @return array $submissions All submission records for this user (or group).
     */
    public function get_all_submissions() {
        global $DB;

        $assign = $this->get_assign();

        if ($assign->get_instance()->teamsubmission) {
            $groupid = 0;
            $group = $assign->get_submission_group($this->guserid);
            if ($group) {
                $groupid = $group->id;
            }

            // Params to get the group submissions.
            $params = array('assignment' => $assign->get_instance()->id, 'groupid' => $groupid, 'userid' => 0);
        } else {
            // Params to get the user submissions.
            $params = array('assignment' => $assign->get_instance()->id, 'userid' => $this->guserid);
        }

        // Return the submissions ordered by attempt.
        $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber ASC');

        return $submissions;
    }

    public function allows_multiple_attempts() {
        return ($this->get_assign()->get_instance()->maxattempts > 1 || $this->get_assign()->get_instance()->maxattempts == ASSIGN_UNLIMITED_ATTEMPTS);
    }

    /**
     * @return bool
     */
    public function allow_new_manualattempt() {
        $attemptnumber = $this->get_attemptnumber();
        if ($attemptnumber != -1) {
            // Only allow a new manual attempt if this is the current attempt.
            return false;
        }

        $assign = $this->get_assign();
        $instance = $assign->get_instance();

        if (!$this->allows_multiple_attempts() || $instance->attemptreopenmethod == ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS) {
            // If assignment doesn't allow multiple attempts or reopen method is automatic until pass.
            return false;
        }

        $submission = $this->get_submission();

        // Don't allow a submission to be re-opened if there is no submission.
        $issubmission = !empty($submission);

        $isunlimited = $instance->maxattempts == ASSIGN_UNLIMITED_ATTEMPTS;
        $islessthanmaxattempts = $issubmission && ($submission->attemptnumber + 1 < ($instance->maxattempts));

        return (!$issubmission or $isunlimited or $islessthanmaxattempts);
    }

    public function get_attemptnumber() {
        return $this->attemptnumber;
    }

    /**
     * @param \MoodleQuickForm $mform
     */
    public function comment_form_hook($mform) {
        $attemptnumber = $this->get_attemptnumber();
        if ($attemptnumber >= 0) {
            $mform->addElement('hidden', 'attempt', $attemptnumber);
            $mform->setType('attempt', PARAM_INT);
        }
    }
}
