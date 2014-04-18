<?php
namespace local_joulegrader\pane\view;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
/**
 * View Pane class for 2.3 Assignment Activity
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class mod_assign_submissions extends view_abstract {

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');
    }
}
