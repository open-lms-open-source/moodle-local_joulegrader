<?php
namespace local_joulegrader\gradingarea;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Grading area class for mod_hsuforum component, posts areaname
 *
 * @author Mark Nielsen
 * @package local/joulegrader
 */
class mod_hsuforum_posts extends gradingarea_abstract {

    /**
     * @var string
     */
    protected static $studentcapability = 'mod/hsuforum:viewdiscussion';

    /**
     * @var string
     */
    protected static $teachercapability = 'local/joulegrader:grade';

    /**
     * @static
     * @param \course_modinfo $courseinfo
     * @param \grading_manager $gradingmanager
     * @param bool $needsgrading
     * @return bool
     */
    public static function include_area(\course_modinfo $courseinfo, \grading_manager $gradingmanager, $needsgrading = false) {
        global $CFG, $DB;
        $include = false;

        require_once($CFG->dirroot.'/mod/hsuforum/lib.php');
        require_once($CFG->libdir.'/gradelib.php');

        try {
            /** @var $context \context_module */
            $context = $gradingmanager->get_context();
            $cminfo  = $courseinfo->get_cm($context->instanceid);
            $forum   = $DB->get_record('hsuforum', array('id' => $cminfo->instance), '*', MUST_EXIST);

            if ($forum->gradetype == HSUFORUM_GRADETYPE_MANUAL and $cminfo->uservisible) {
                $include = true;
            }

            //check to see if it should be included based on whether the needs grading button was selected
            if ($include and $needsgrading and has_capability(self::$teachercapability, $context)) {
                // Determine if the student is missing a grade and has posts for grading...
                $userids = get_enrolled_users($context, '', 0, 'u.id');
                $userids = \local_joulegrader\utility\users::limit_to_gradebook_roles($userids, $context);
                $grades  = grade_get_grades($courseinfo->get_course_id(), 'mod', 'hsuforum', $forum->id, array_keys($userids));

                $include = false;
                $posted = hsuforum_get_users_with_posts($forum->id);
                foreach ($grades->items as $item) {
                    foreach ($item->grades as $userid => $grade) {
                        if (is_null($grade->grade)) {
                            if (!empty($posted[$userid])) {
                                return true;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            //don't need to do anything
        }
        return $include;
    }

    /**
     * @static
     * @param $users
     * @param \grading_manager $gradingmanager
     * @param bool $needsgrading
     *
     * @return array
     */
    public static function include_users($users, \grading_manager $gradingmanager, $needsgrading) {
        global $DB, $CFG, $COURSE;
        $include = array();

        // return $users if needsgrading is false or there are no users to include
        if (empty($needsgrading) || empty($users)) {
            return $users;
        }

        try {
            require_once($CFG->libdir.'/gradelib.php');
            require_once($CFG->dirroot.'/mod/hsuforum/lib.php');

            // load the course_module from the context
            $cm = get_coursemodule_from_id('hsuforum', $gradingmanager->get_context()->instanceid, $COURSE->id, false, MUST_EXIST);

            // load the hsuforum record
            $hsuforum = $DB->get_record("hsuforum", array("id" => $cm->instance), '*', MUST_EXIST);

            // get existing grades
            $grades = grade_get_grades($COURSE->id, 'mod', 'hsuforum', $hsuforum->id, array_keys($users));
            $posted = hsuforum_get_users_with_posts($hsuforum->id);
            $include = false;
            foreach ($grades->items as $item) {
                foreach ($item->grades as $userid => $grade) {
                    if (is_null($grade->grade)) {
                        if (!empty($posted[$userid])) {
                            $include[$userid] = $users[$userid];
                        }
                    }
                }
            }

        } catch (\Exception $e) {

        }
        return $include;
    }

    /**
     * @return \stdClass
     */
    public function get_comment_info() {
        $options          = new \stdClass();
        $options->area    = 'userposts_comments';
        $options->context = $this->get_gradingmanager()->get_context();
        $options->itemid  = $this->get_guserid();
        $options->component = 'mod_hsuforum';

        return $options;
    }

    /**
     * @return \stdClass File area information for use in comments
     */
    public function get_comment_filearea_info() {
        return (object) array(
            'component' => 'mod_hsuforum',
            'filearea' => 'comments'
        );
    }

    /**
     * @return int
     */
    public function get_showpost_preference() {
        $preferenceparam = optional_param('showposts', -1, PARAM_INT);
        if ($preferenceparam != -1) {
            $this->update_showpost_preference($preferenceparam);
        }

        return get_user_preferences('local_joulegrader_mod_hsuforum_posts_showposts_grouped', 1);
    }

    /**
     * @param $preference
     * @return string
     */
    public function get_showpost_preference_label($preference) {
        if (!empty($preference)) {
            $label = get_string('showonlyuserposts', 'local_joulegrader');
        } else {
            $label = get_string('groupbydiscussion', 'local_joulegrader');
        }

        return $label;
    }

    /**
     * @param $newpreference
     */
    protected function update_showpost_preference($newpreference) {
        set_user_preference('local_joulegrader_mod_hsuforum_posts_showposts_grouped', $newpreference);
    }

    /**
     * @return array - the viewpane class and path to the class that this gradingarea class should use
     */
    protected function get_viewpane_info() {
        return array(
            '',
            '\\local_joulegrader\\pane\\view\\mod_hsuforum_posts',
        );
    }

    /**
     * @return array - the gradepane class and path to the class the this gradingarea class should use
     */
    protected function get_gradepane_info() {
        return array(
            '',
            "\\local_joulegrader\\pane\\grade\\mod_hsuforum_posts",
        );
    }
}
