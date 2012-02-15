<?php
/**
 * joule Grader lib functions
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

/**
 * Extend the settings navigation
 *
 * @param $settings
 * @param $context
 */
function joulegrader_extend_settings_navigation($settings, $context) {
    global $COURSE, $PAGE, $DB, $CFG;

    if ($COURSE->id != SITEID && (has_capability('local/joulegrader:view', $context) || has_capability('local/joulegrader:grade', $context))) {
        //try to get the courseadmin node
        $coursenode = $settings->get('courseadmin');

        //if there's a course node
        if (is_object($coursenode)) {
            //url params
            $urlparams = array('courseid' => $COURSE->id);

            //try to see if this is within an activity
            $activityname = $PAGE->activityname;
            if (isset($activityname)) {
                require_once($CFG->dirroot . '/grade/grading/lib.php');

                //get a grading manager
                $gm = get_grading_manager($context, $activityname);

                //check to make sure this supports grading areas
                if ($areas = $gm->get_available_areas()) {
                    //there are grading areas supported, since we don't really know which area they may be after,
                    //pick the first one
                    if ($area = $DB->get_record('grading_areas', array('contextid' => $context->id, 'component' => $gm->get_component())
                            , 'id', IGNORE_MULTIPLE)) {
                        //add it to the url params
                        $urlparams['garea'] = $area->id;
                    }
                }
            }

            $url = new moodle_url('/local/joulegrader/view.php', $urlparams);
            //not sure if it should be a popup
            //$actionlink = new action_link($url, '', new popup_action('click', $url, 'popup', array('height' => 768, 'width' => 1024)));

            $coursenode->add(get_string('pluginname', 'local_joulegrader'), $url, 'grade');
        }
    }
}

/**
 * Send a file
 *
 * This is a workaround for allowing a file from an assignment submission file area to be embedded, since
 * mod_assignment_pluginfile forces a download, which sends the download header, and disrupts pdf embedding
 *
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea - not the actual file area, should be set to 'gradingarea'
 * @param array $args - first arg should be 'itemid', next needs to be the gradingarea compenent_area (e.g. mod_assignment_submission)
 * @param $forcedownload
 * @return bool
 */
function local_joulegrader_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG;

    require_login($course, false, $cm);

    if ($filearea != 'gradingarea') {
        return false;
    }

    //shift the itemid off the front
    $itemid = (int) array_shift($args);

    //next arg should be the gradingarea component_area (e.g. mod_assignment_submission)
    $gradingarea = array_shift($args);

    $classname = 'local_joulegrader_lib_gradingarea_' . $gradingarea . '_class';
    if (!class_exists($classname)) {
        try {
            include_once($CFG->dirroot . '/local/joulegrader/lib/gradingarea/' . $gradingarea . '/class.php');
        } catch (Exception $e) {
            return false;
        }
    }

    //pass everything off to the gradingarea class to handle sending the file
    $method = 'pluginfile';
    if (!is_callable("$classname::$method")) {
        return false;
    }

    $classname::$method($course, $cm, $context, $itemid, $args, $forcedownload);
}
