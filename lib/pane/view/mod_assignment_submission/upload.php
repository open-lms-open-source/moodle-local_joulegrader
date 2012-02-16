<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/view/abstract.php');

/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_view_mod_assignment_submission_upload extends local_joulegrader_lib_pane_view_abstract {

    /**
     * @var array
     */
    protected $fileareatree;

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        //initialize the empty message
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');

        //try to get the user's file if there is a submission
        $submission = $this->get_gradingarea()->get_submission();
        if (!empty($submission)) {
            //file storage
            $fs = get_file_storage();

            //get fileareatree
            $this->fileareatree = $fs->get_area_tree($this->get_gradingarea()->get_gradingmanager()->get_context()->id, 'mod_assignment', 'submission', $submission->id);
        }
    }

    /**
     * @return array
     */
    public function get_fileareatree() {
        return $this->fileareatree;
    }
}