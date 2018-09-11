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
 * View abstract renderable
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\pane\view;
use local_joulegrader\gradingarea;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */
abstract class view_abstract implements \renderable {

    /**
     * @var gradingarea\gradingarea_abstract - instance of a gradingarea class
     */
    protected $gradingarea;

    /**
     * @var string - message to display if there is nothing for the panel to display
     */
    protected $emptymessage;

    /**
     * @param gradingarea\gradingarea_abstract $gradingarea
     */
    public function __construct(gradingarea\gradingarea_abstract $gradingarea) {
        $this->gradingarea = $gradingarea;
    }

    /**
     * @return gradingarea\gradingarea_abstract
     */
    public function get_gradingarea() {
        return $this->gradingarea;
    }

    /**
     * @return string
     */
    public function get_emptymessage() {
        return $this->emptymessage;
    }

    /**
     * Do any initialization the panel needs before rendering
     *
     * @abstract
     */
    abstract public function init();
}
