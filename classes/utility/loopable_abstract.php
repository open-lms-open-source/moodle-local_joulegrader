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
 * Abstract class for a loopable utility.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  2014 Blackboard Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\utility;


abstract class loopable_abstract implements loopable_interface {
    /**
     * @var array
     */
    protected $items;

    /**
     * @var int
     */
    protected $current;

    /**
     * @var int
     */
    protected $next;

    /**
     * @var int
     */
    protected $previous;

    /**
     * @return array
     */
    public function get_items() {
        return $this->items;
    }

    /**
     * @return int
     */
    public function get_next() {
        if (is_null($this->next) && !empty($this->items) && count($this->items) > 1) {
            list($this->previous, $this->next) = $this->find_previous_and_next($this->items, $this->get_current());
        }

        return $this->next;
    }

    public function get_previous() {
        if (is_null($this->previous) && !empty($this->items) && count($this->items) > 1) {
            list($this->previous, $this->next) = $this->find_previous_and_next($this->items, $this->get_current());
        }

        return $this->previous;
    }

    public function get_current() {
        return $this->current;
    }

    /**
     * Find the previous and next user ids
     */
    protected function find_previous_and_next($list, $currentid) {
        $ids         = array_keys($list);
        $previd      = null;
        $nextid      = null;
        $idslength = count($ids);

        $currentkey = array_search($currentid, $ids);
        if ($currentkey > 0){
            $previd = $ids[$currentkey - 1];
        } else {
            $previd = $ids[$idslength - 1];
        }

        if ($currentkey < $idslength - 1){
            $nextid = $ids[$currentkey + 1];
        } else {
            $nextid = $ids[0];
        }

        return array($previd, $nextid);
    }

    abstract public function load_items();
} 
