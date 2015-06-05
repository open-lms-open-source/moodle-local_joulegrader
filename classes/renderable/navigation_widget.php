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
 * Navigation widget
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\renderable;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader Navigation Widget
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

class navigation_widget implements \renderable {

    /**
     * @var string - the name of the navigation widget (likely user or activity)
     *
     * This will be used to generate ids for the select form and the links
     */
    protected $name;

    /**
     * @var \moodle_url - the url for the form action/links
     */
    protected $url;

    /**
     * @var array - options for the select menu
     */
    protected $options;

    /**
     * @var string - the parameter that changes on select/used in link
     */
    protected $param;

    /**
     * @var int - the currently selected id (used to set the selected menu item)
     */
    protected $currentid;

    /**
     * @var mixed - the id for the next link
     */
    protected $nextid;

    /**
     * @var mixed - the id for previous link
     */
    protected $previd;

    /**
     * @var string visible label for use in previous and next labels/tool tips, etc
     */
    protected $label = '';

    /**
     * @param string $name -the name of the navigation widget (likely user or activity)
     * @param \moodle_url $url - the url for the form action/links
     * @param array $options - options for the select menu
     * @param string $param - the parameter that changes on select/used in link
     * @param int $currentid - the currently selected id (used to set the selected menu item)
     * @param mixed $nextid - the id for the next link
     * @param mixed $previd - the id for previous link
     */
    public function __construct($name, \moodle_url $url, array $options, $param, $currentid, $nextid, $previd) {
        $this->name      = $name;
        $this->url       = $url;
        $this->options   = $options;
        $this->param     = $param;
        $this->currentid = $currentid;
        $this->nextid    = $nextid;
        $this->previd    = $previd;
    }

    /**
     * @return int
     */
    public function get_currentid() {
        return $this->currentid;
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function get_nextid() {
        return $this->nextid;
    }

    /**
     * @return array
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * @return string
     */
    public function get_param() {
        return $this->param;
    }

    /**
     * @return mixed
     */
    public function get_previd() {
        return $this->previd;
    }

    /**
     * @return \moodle_url
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * @return string
     */
    public function get_label() {
        return $this->label;
    }

    /**
     * @param $label
     */
    public function set_label($label) {
        $this->label = $label;
    }
}

