<?php
namespace local_joulegrader\pane\view;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * View Pane class for Offline Assignment type
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class mod_assignment_submission_offline extends view_abstract {

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');
    }
}