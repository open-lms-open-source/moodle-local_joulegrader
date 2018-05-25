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
 * joule Grader lib functions
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_joulegrader\utility\gradingareas;

/**
 * Extend the settings navigation
 *
 * @param $settings
 * @param $context
 */
function local_joulegrader_extend_settings_navigation($settings, $context) {
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
                //try to get an areaid from the context and activity name
                if ($areaid = gradingareas::get_areaid_from_context_activityname($context, $activityname)) {
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
 * @param $options
 * @return bool
 */
function local_joulegrader_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;

    require_login($course, false, $cm);

    if ($filearea != 'gradingarea') {
        return false;
    }

    if ($filearea == 'gradingarea') {
        //shift the itemid off the front
        $itemid = (int) array_shift($args);

        //next arg should be the gradingarea component_area (e.g. mod_assignment_submission)
        $gradingarea = array_shift($args);

        $classname = '\\local_joulegrader\\gradingarea\\' . $gradingarea;
        if (!class_exists($classname)) {
            return false;
        }

        //pass everything off to the gradingarea class to handle sending the file
        $method = 'pluginfile';
        if (!is_callable("$classname::$method")) {
            return false;
        }

        $classname::$method($course, $cm, $context, $itemid, $args, $forcedownload, $options);
    }
}

/**
 * @param context $context
 * @param string $activityname
 * @return int
 */
function local_joulegrader_area_from_context(context $context, $activityname) {
    return gradingareas::get_areaid_from_context_activityname($context, $activityname);
}
