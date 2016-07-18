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
 * joule Grader Version file
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @var object $plugin */
$plugin->version      = 2016012600;
$plugin->requires     = 2015111604; // Requires this Moodle version (3.0.4).
$plugin->component    = 'local_joulegrader';
$plugin->release      = '3.0.4 (Build: 20160509)';
$plugin->maturity     = MATURITY_STABLE;
$plugin->dependencies = array(
    'mod_hsuforum' => ANY_VERSION,
    'mod_assign'   => ANY_VERSION,
    'local_mr'     => ANY_VERSION,
);
