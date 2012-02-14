<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/local/joulegrader/lib/pane/view/abstract.php');
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_lib_pane_view_mod_assignment_submission_offline extends local_joulegrader_lib_pane_view_abstract {

    protected function load_html() {
        $html = '';

        $this->html = $html;
    }
}