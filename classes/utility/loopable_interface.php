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
 * Interface for a loopable utility.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  2014 Moodlerooms, Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\utility;


interface loopable_interface {
    /**
     * @return int
     */
    public function get_next();

    /**
     * @return int
     */
    public function get_previous();

    /**
     * @return int
     */
    public function get_current();

    /**
     * @return array
     */
    public function get_items();
} 