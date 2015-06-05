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
 * Restore
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * joule Grader restore plugin
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class restore_local_joulegrader_plugin extends restore_local_plugin {
    /**
     * @return array
     */
    protected function define_area_plugin_structure() {
        // Only restore if user info is being included
        if (!$this->get_setting_value('userinfo')) {
            return array();
        }

        $modulename = $this->task->get_modulename();
        // Only restore if the activity supports advanced grading
        if (!plugin_supports('mod', $modulename, FEATURE_ADVANCED_GRADING, false)) {
            return array();
        }

        // Return the paths
        return array(
            new restore_path_element('joulegrader_comment', $this->get_pathfor('/comments/comment')),
        );
    }

    /**
     * Restore a single comment
     *
     * @param stdClass $data
     */
    public function process_joulegrader_comment($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Remap user ids
        $data->guserid = $this->get_mappingid('user', $data->guserid);
        $data->commenterid = $this->get_mappingid('user', $data->commenterid);

        // Remap grading area id
        $data->gareaid = $this->get_new_parentid('grading_area');

        // Insert new comment record
        $newitemid = $DB->insert_record('local_joulegrader_comments', $data);
        $this->set_mapping('comment', $oldid, $newitemid, true);
    }

    /**
     * Add related files
     */
    public function after_execute_area() {
        $this->add_related_files('local_joulegrader', 'comment', 'comment');
    }

    /**
     * After restore process. Currently handles upgrading 2.3 Joule Grader comments restored into 2.4+.
     */
    public function after_restore_area() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/joulegrader/db/upgradelib.php');

        $newcommentsqls = "SELECT newitemid, newitemid
                             FROM {backup_ids_temp}
                            WHERE itemname = ?
                              AND parentitemid = ?";

        $params = array('comment', $this->task->get_old_contextid());

        if ($commentids = $DB->get_records_sql($newcommentsqls, $params)) {
            $commentsupgrade = new local_joulegrader_comments_upgrader();
            $commentsupgrade->upgrade(array_keys($commentids), true);
        }
    }
}