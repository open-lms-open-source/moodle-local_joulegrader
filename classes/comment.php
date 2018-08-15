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
 * Comment
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader comment class
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class comment implements \renderable {

    /**
     * @var \stdClass Comment record from comment api
     */
    public $commentrecord;

    /**
     * @var \context
     */
    protected $context;

    /**
     * @var int
     */
    protected $guserid;

    /**
     * @var int
     */
    protected $gareaid;

    /**
     * @param \stdClass|null  $commentrecord
     */
    public function __construct(\stdClass $commentrecord = null) {
        $this->commentrecord = $commentrecord;
    }

    /**
     * @return int
     */
    public function get_id() {
        return $this->commentrecord->id;
    }

    /**
     * @return string
     */
    public function get_content() {
        return $this->commentrecord->content;
    }

    /**
     * @param string $content
     */
    public function set_content($content) {
        $this->commentrecord->content = $content;
    }

    /**
     * @return int
     */
    public function get_timecreated() {
        return $this->commentrecord->timecreated;
    }

    /**
     * @return string
     */
    public function get_avatar() {
        return $this->commentrecord->avatar;
    }

    /**
     * @return string
     */
    public function get_user_fullname() {
        return $this->commentrecord->fullname;
    }

    /**
     * @return string
     */
    public function get_user_profileurl() {
        return $this->commentrecord->profileurl;
    }

    /**
     * @return bool
     */
    public function can_delete() {
        return !empty($this->commentrecord->delete);
    }

    /**
     * @return string
     */
    public function get_dateformat() {
        return $this->commentrecord->strftimeformat;
    }

    /**
     * @param $context
     */
    public function set_context($context) {
        $this->context = $context;
    }

    /**
     * @return mixed - context
     */
    public function get_context() {
        return $this->context;
    }

    /**
     * @return int
     */
    public function get_guserid() {
        return $this->guserid;
    }

    /**
     * @return mixed
     */
    public function get_gareaid() {
        return $this->gareaid;
    }

    /**
     * @param int $guserid
     */
    public function set_guserid($guserid) {
        $this->guserid = $guserid;
    }

    /**
     * @param int $gareaid
     */
    public function set_gareaid($gareaid) {
        $this->gareaid = $gareaid;
    }

}
