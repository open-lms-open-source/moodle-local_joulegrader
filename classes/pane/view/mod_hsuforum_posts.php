<?php
namespace local_joulegrader\pane\view;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * View Pane class for mod_hsuforum_posts
 *
 * @author Mark Nielsen
 * @package local/joulegrader
 */
class mod_hsuforum_posts extends view_abstract {

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');
    }
}