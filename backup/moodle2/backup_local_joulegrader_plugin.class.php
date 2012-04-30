<?php
/**
 * joule Grader backup plugin
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class backup_local_joulegrader_plugin extends backup_local_plugin {

    protected function define_area_plugin_structure() {
        // Only backup if user info is being included
        if (!$this->get_setting_value('userinfo')) {
            return;
        }

        $modulename = $this->task->get_modulename();
        // Only backup if the activity supports advanced grading
        if (!plugin_supports('mod', $modulename, FEATURE_ADVANCED_GRADING, false)) {
            return;
        }

        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element();

        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Now create joule grader structures
        $comments = new backup_nested_element('comments');
        $comment  = new backup_nested_element('comment', array('id'), array(
            'gareaid',
            'guserid',
            'content',
            'commenterid',
            'timecreated',
            'attachment',
            'deleted',
        ));

        // Now build the tree
        $pluginwrapper->add_child($comments);
        $comments->add_child($comment);

        // Set the source for comments
        $comment->set_source_table('local_joulegrader_comments', array('gareaid' => backup::VAR_PARENTID));

        // Annotate ids
        $comment->annotate_ids('user', 'guserid');
        $comment->annotate_ids('user', 'commenterid');

        // Annotate files
        $comment->annotate_files('local_joulegrader', 'comment', 'id');
    }
}