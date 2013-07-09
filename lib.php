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
function local_joulegrader_extends_settings_navigation($settings, $context) {
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
                require_once($CFG->dirroot . '/local/joulegrader/helper/gradingareas.php');

                //try to get an areaid from the context and activity name
                if ($areaid = local_joulegrader_helper_gradingareas::get_areaid_from_context_activityname($context, $activityname)) {
                    //add it to the url
                    $urlparams['garea'] = $areaid;
                }

            }

            $url = new moodle_url('/local/joulegrader/view.php', $urlparams);
            //not sure if it should be a popup
            //$actionlink = new action_link($url, '', new popup_action('click', $url, 'popup', array('height' => 768, 'width' => 1024)));

            $coursenode->add(get_string('pluginname', 'local_joulegrader'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('joulegrader', get_string('pluginname', 'local_joulegrader'), 'local_joulegrader'));
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
function local_joulegrader_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG, $DB, $USER;

    require_login($course, false, $cm);

    if ($filearea != 'gradingarea' && $filearea != 'comment') {
        return false;
    }

    if ($filearea == 'gradingarea') {
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

        $classname::$method($course, $cm, $context, $itemid, $args, $forcedownload, $options);

    } else if ($filearea == 'comment') {

        //make sure itemid and filename are set
        if (!isset($args[0]) || !isset($args[1])) {
            return false;
        }

        //make sure the user has capabilities to download this comment attachment
        if (!has_capability('local/joulegrader:grade', $context)) {
            //get the graded user for the comment
            $commentid = clean_param($args[0], PARAM_INT);
            $guserid = $DB->get_field('local_joulegrader_comments', 'guserid', array('id' => $commentid), MUST_EXIST);
            if (!has_capability('local/joulegrader:view', $context) || $USER->id != $guserid) {
                return false;
            }
        }

        $fullpath = '/'.$context->id.'/local_joulegrader/comment/'.$args[0].'/'.$args[1];

        $fs = get_file_storage();

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, 86400, 0, true, $options);
    }
}

/**
 * @param context $context
 * @param string $activityname
 * @return int
 */
function local_joulegrader_area_from_context(context $context, $activityname) {
    global $CFG;
    require_once($CFG->dirroot . '/local/joulegrader/helper/gradingareas.php');

    return local_joulegrader_helper_gradingareas::get_areaid_from_context_activityname($context, $activityname);
}
