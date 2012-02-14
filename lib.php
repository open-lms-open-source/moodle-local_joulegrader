<?php
/**
 * joule Grader lib functions
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

/**
 * @param $settings
 */
function local_joulegrader_extend_navigation_settings($settings) {

}

/**
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea
 * @param $args
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
