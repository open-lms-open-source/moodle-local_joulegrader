<?php
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
        $mform->addElement('editor', 'comment', null, null, $editoroptions);
        $mform->setType('comment', PARAM_RAW);
        $mform->addRule('comment', get_string('commentrequired', 'local_joulegrader'), 'required', null, 'client');

        $this->_customdata->comment_form_hook($mform);

        //submit button
        $this->add_action_buttons(false, get_string('add', 'local_joulegrader'));
    }
}