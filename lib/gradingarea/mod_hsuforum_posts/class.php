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