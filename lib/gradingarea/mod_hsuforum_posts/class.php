<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/gradingarea/abstract.php');

/**
 * Grading area class for mod_hsuforum component, posts areaname
 *
 * @author Mark Nielsen
 * @package local/joulegrader
 */
class local_joulegrader_lib_gradingarea_mod_hsuforum_posts_class extends local_joulegrader_lib_gradingarea_abstract {

    /**
     * @var string
     */
    protected static $studentcapability = 'mod/hsuforum:viewdiscussion';

    /**
     * @var string
     */
    protected static $teachercapability = 'mod/hsuforum:rate';

    /**
     * @static
     * @param course_modinfo $courseinfo
     * @param grading_manager $gradingmanager
     * @param bool $needsgrading
     * @return bool
     */
    public static function include_area(course_modinfo $courseinfo, grading_manager $gradingmanager, $needsgrading = false) {
        global $CFG, $DB;
        $include = false;

        require_once($CFG->dirroot.'/mod/hsuforum/lib.php');
        require_once($CFG->libdir.'/gradelib.php');

        try {
            /** @var $context context_module */
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
                $grades  = grade_get_grades($courseinfo->get_course_id(), 'mod', 'hsuforum', $forum->id, array_keys($userids));

                $include = false;
                foreach ($grades->items as $item) {
                    foreach ($item->grades as $userid => $grade) {
                        if (is_null($grade->grade)) {
                            $posts = hsuforum_get_user_posts($forum->id, $userid);

                            if (!empty($posts)) {
                                return true;
                            }
                        }
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
     * @param $users
     * @param grading_manager $gradingmanager
     * @param bool $needsgrading
     *
     * @return array
     */
    public static function include_users($users, grading_manager $gradingmanager, $needsgrading) {
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
            $include = false;
            foreach ($grades->items as $item) {
                foreach ($item->grades as $userid => $grade) {
                    if (is_null($grade->grade)) {
                        $posts = hsuforum_get_user_posts($hsuforum->id, $userid);

                        // if they have posts
                        if (!empty($posts)) {
                            $include[$userid] = $users[$userid];
                        }
                    }
                }
            }

        } catch (Exception $e) {

        }

        return $include;
    }

    /**
     * @return stdClass
     */
    public function get_comment_info() {
        $options          = new stdClass();
        $options->area    = 'userposts_comments';
        $options->context = $this->get_gradingmanager()->get_context();
        $options->itemid  = $this->get_guserid();
        $options->component = 'mod_hsuforum';

        return $options;
    }

    /**
     * @return stdClass File area information for use in comments
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
        global $CFG;

        return array(
            "$CFG->dirroot/local/joulegrader/lib/pane/view/mod_hsuforum_posts/class.php",
            'local_joulegrader_lib_pane_view_mod_hsuforum_posts_class',
        );
    }

    /**
     * @return array - the gradepane class and path to the class the this gradingarea class should use
     */
    protected function get_gradepane_info() {
        global $CFG;

        return array(
            "$CFG->dirroot/local/joulegrader/lib/pane/grade/mod_hsuforum_posts/class.php",
            "local_joulegrader_lib_pane_grade_mod_hsuforum_posts_class",
        );
    }
}