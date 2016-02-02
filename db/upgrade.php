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
 * Upgrade script for joule Grader
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function
 *
 * @param int $oldversion
 */
function xmldb_local_joulegrader_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012030700) {

        // Define table local_joulegrader_comments to be created
        $table = new xmldb_table('local_joulegrader_comments');

        // Adding fields to table local_joulegrader_comments
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gareaid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('guserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('content', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null);
        $table->add_field('commenterid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('attachment', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('deleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);

        // Adding keys to table local_joulegrader_comments
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('gareaid', XMLDB_KEY_FOREIGN, array('gareaid'), 'grading_areas', array('id'));
        $table->add_key('guserid', XMLDB_KEY_FOREIGN, array('guserid'), 'user', array('id'));

        // Adding indexes to table local_joulegrader_comments
        $table->add_index('gareaid-guserid', XMLDB_INDEX_NOTUNIQUE, array('gareaid', 'guserid'));

        // Conditionally launch create table for local_joulegrader_comments
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // joulegrader savepoint reached
        upgrade_plugin_savepoint(true, 2012030700, 'local', 'joulegrader');
    }

    if ($oldversion < 2013053000) {
        require_once(__DIR__ . '/upgradelib.php');

        $commentsupgrader = new local_joulegrader_comments_upgrader();
        $commentsupgrader->upgrade();

        // Joule grader savepoint reached.
        upgrade_plugin_savepoint(true, 2013053000, 'local', 'joulegrader');
    }

    return true;
}