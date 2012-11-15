<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/view/abstract.php');
/**
 * View Pane class for 2.3 Assignment Activity
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_view_mod_assign_submissions_class extends local_joulegrader_lib_pane_view_abstract {

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');
    }
}
