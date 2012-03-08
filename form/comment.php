<?php
/**
 * @author Sam Chaffee
 * @package local/joulegrader
 */

class local_joulegrader_form_comment extends moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform =& $this->_form;

        //comment editor
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext'=>true);
        $mform->addElement('editor', 'comment', null, array('cols' => 10), $editoroptions);
        $mform->addRule('comment', get_string('commentrequired', 'local_joulegrader'), 'required', null, 'client');

        //file picker
        $mform->addElement('filepicker', 'attachments', get_string('attachments', 'local_joulegrader'));

        //submit button
        $this->add_action_buttons(false, get_string('add', 'local_joulegrader'));
    }
}