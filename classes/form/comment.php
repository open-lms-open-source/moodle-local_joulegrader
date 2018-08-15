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
 * Comment form
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Blackboard Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_joulegrader\form;
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');
global $CFG;
require_once($CFG->libdir.'/formslib.php');
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */

class comment extends \moodleform {

    /**
     * Form definition
     */
    public function definition() {

        $mform =& $this->_form;

        //tinymce params
        if (is_callable('mr_on') && mr_on('kaltura', '_MR_LOCAL')) {
            $webcamplugin = '-kalturamedia,';
            $webcambutton = 'kalturamedia';
        } else {
            $webcamplugin = '';
            $webcambutton = '';
        }

        $tineymceparams = array(
            'plugins' => "{$webcamplugin}safari,layer,advlink,emotions,inlinepopups,paste,directionality,save,iespell,preview,print,noneditable,visualchars,xhtmlxtras,template",
            'theme_advanced_buttons1' => "bold,italic,underline,strikethrough,bullist,numlist,{$webcambutton}",
            'theme_advanced_buttons1_add' => null,
            'theme_advanced_buttons2' => 'undo,redo,|,link,unlink',
            'theme_advanced_buttons2_add' => null,
            'theme_advanced_buttons3' => null,
            'theme_advanced_buttons3_add' => null,
            'width' => '100%',
        );

        // Editor options.
        $editoroptions = $this->_customdata->get_editor_options();
        $editoroptions['tinymceparams'] = $tineymceparams;

        //comment editor
        $editorname = 'comment_' . $this->_customdata->get_areaid() . '_' . $this->_customdata->get_guserid();
        $mform->addElement('editor', $editorname, null, null, $editoroptions);
        $mform->setType($editorname, PARAM_RAW);
        $mform->addRule($editorname, get_string('commentrequired', 'local_joulegrader'), 'required', null, 'client');

        $this->_customdata->comment_form_hook($mform);

        //submit button
        $this->add_action_buttons(false, get_string('add', 'local_joulegrader'));
    }
}
