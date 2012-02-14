<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
require_once($CFG->dirroot . '/local/joulegrader/lib/pane/view/abstract.php');

/**
 * View Pane class for Upload Single Assignment type
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_view_mod_assignment_submission_uploadsingle extends local_joulegrader_lib_pane_view_abstract {

    /**
     * @var stored_file - user's uploaded file
     */
    protected $file;

    /**
     * Init function overridden from abstract class
     */
    public function init() {
        //set the empty message
        $this->emptymessage = get_string('nothingtodisplay', 'local_joulegrader');

        //try to get the user's file if there is a submission
        $submission = $this->get_gradingarea()->get_submission();
        if (!empty($submission)) {
            //file storage
            $fs = get_file_storage();

            if ($files = $fs->get_area_files($this->get_gradingarea()->get_gradingmanager()->get_context()->id, 'mod_assignment', 'submission', $submission->id, "timemodified", false)) {
                foreach ($files as $file) {
                    $this->file = $file;
                    //this upload single, there should only be "1" file
                    break;
                }
            }
        }
    }

    /**
     * @return stored_file
     */
    public function get_file() {
        return $this->file;
    }
}