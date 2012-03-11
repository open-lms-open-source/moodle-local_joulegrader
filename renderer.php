<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * Renderer
 *
 * @author Sam Chaffee
 * @package local/mrooms
 */
class local_joulegrader_renderer extends plugin_renderer_base {

    /**
     * @param local_joulegrader_lib_comment_loop $commentloop
     * @return string
     */
    public function render_local_joulegrader_lib_comment_loop(local_joulegrader_lib_comment_loop $commentloop) {
        global $PAGE;

        //get the comments
        $comments = $commentloop->get_comments();

        //render comment html
        $commentshtml = '';
        foreach ($comments as $comment) {
            $commentshtml .= $this->render($comment);
        }

        $commentshtml = html_writer::tag('div', $commentshtml, array('class' => 'local_joulegrader_commentloop_comments'));

        //get the comment form
        $mform = $commentloop->get_mform();

        //render the form
        $mrhelper = new mr_helper();
        $mformhtml = $mrhelper->buffer(array($mform, 'display'));

        $id = uniqid('local-joulegrader-commentloop-con-');
        $html = html_writer::tag('div', $commentshtml . $mformhtml, array('id' => $id));

        $module = array(
            'name' => 'local_joulegrader',
            'fullpath' => '/local/joulegrader/javascript.js',
            'requires' => array(
                'base',
                'node',
                'event',
                'io'
            ),
        );

        $PAGE->requires->js_init_call('M.local_joulegrader.init_commentloop', array('id' => $id), false, $module);

        return $html;
    }

    /**
     * @param local_joulegrader_lib_comment_class $comment
     * @return string
     */
    public function render_local_joulegrader_lib_comment_class(local_joulegrader_lib_comment_class $comment) {
        global $OUTPUT, $COURSE;

        //get the commenter user object - has fields for $OUTPUT->user_picture() and fullname()
        $commenter = $comment->get_commenter();

        //commenter picture
        $userpic = html_writer::tag('div', $OUTPUT->user_picture($commenter), array('class' => 'local_joulegrader_comment_commenter_pic'));
        $username = html_writer::tag('div', $commenter->firstname, array('class' => 'local_joulegrader_comment_commenter_firstname'));
        $commenterpicture = html_writer::tag('div', $userpic . $username, array('class' => 'local_joulegrader_comment_commenter'));

        //comment timestamp
        $commenttime = html_writer::tag('div', userdate($comment->get_timecreated(), '%d %B %H:%M'), array('class' => 'local_joulegrader_comment_time'));

        //comment content
        $content = file_rewrite_pluginfile_urls($comment->get_content(), 'pluginfile.php', context_course::instance($COURSE->id)->id
                , 'local_joulegrader', 'comment', $comment->get_id());
        $commentcontent = html_writer::tag('div', $content, array('class' => 'local_joulegrader_comment_content'));

        //coment body
        $commentdeleted = $comment->get_deleted();

        if ($commentdeleted && !has_capability('moodle/site:config', context_system::instance())) {
            $commentbody = $commenttime . get_string('commentdeleted', 'local_joulegrader'
                , array('deletedby' => fullname($commenter), 'deletedon' => userdate($commentdeleted, '%d %B %H:%M')));
        } else {
            $commentbody = $commenttime . $commentcontent;
        }

        //comment body
        $commentbody = html_writer::tag('div', $commentbody, array('class' => 'local_joulegrader_comment_body'));

        //delete button
        $deletebutton = '';
        if ($comment->user_can_delete()) {
            $deleteurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'action' => 'deletecomment'
                    , 'commentid' => $comment->get_id(), 'sesskey' => sesskey()));
            $deletebutton = html_writer::tag('div', $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete'
                    , get_string('deletecomment', 'local_joulegrader'))), array('class' => 'local_joulegrader_comment_delete'));
        }

        //attachments - tricky

        //determine classes for comment
        $commentclasses = array('local_joulegrader_comment');
        if ($commentdeleted) {
            $commentclasses[] = 'deleted';
        }
        //put it all together
        $html = html_writer::tag('div', $commenterpicture . $commentbody . $deletebutton, array('class' => implode(' ', $commentclasses)));

        return $html;
    }

    /**
     * Renders grade pane
     *
     * @param local_joulegrader_lib_pane_grade_mod_assignment_submission_class $gradepane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_grade_mod_assignment_submission_class(local_joulegrader_lib_pane_grade_mod_assignment_submission_class $gradepane) {
        global $PAGE;

        $html = $gradepane->get_panehtml();

        $modalhtml = $gradepane->get_modal_html();
        if (!empty($modalhtml)) {
            //wrap it in the proper modal html
            $modalhtml = html_writer::tag('div', $modalhtml, array('class' => 'yui3-widget-bd'));
            $modalhtml = html_writer::tag('div', $modalhtml, array('id' => 'local-joulegrader-gradepane-panel'));

            $html .= $modalhtml;
        }

        $gradepane->require_js();

        $module = array(
            'name' => 'local_joulegrader',
            'fullpath' => '/local/joulegrader/javascript.js',
            'requires' => array(
                'base',
                'node',
                'event',
                'panel',
                'dd-plugin'
            ),
            'strings' => array(
                array('rubric', 'local_joulegrader'),
            ),
        );
        $PAGE->requires->js_init_call('M.local_joulegrader.init_gradepane_panel', array('local-joulegrader-gradepane-panel'), false, $module);

        return $html;
    }

    /**
     * Renders a navigation widget containing a previous link, a next link, and a select menu
     *
     * @param local_joulegrader_lib_navigation_widget $navwidget
     * @return string
     */
    public function render_local_joulegrader_lib_navigation_widget(local_joulegrader_lib_navigation_widget $navwidget) {
        global $OUTPUT;

        //widget name
        $widgetname = $navwidget->get_name();

        //widget url
        $widgeturl = $navwidget->get_url();
        $linkurl   = clone($widgeturl);

        //prev link
        $prevlink = '';
        if ($previd = $navwidget->get_previd()) {
            $linkurl->param($navwidget->get_param(), $previd);
            $prevlink = $OUTPUT->action_icon($linkurl, new pix_icon('t/left', get_string('previous')));
        }

        //select menu
        $formid = "local-joulegrader-{$widgetname}nav-menu";
        $select = new single_select($widgeturl, $navwidget->get_param(), $navwidget->get_options()
            , $navwidget->get_currentid(), '', $formid);

        //set some select attributes
        $select->set_help_icon($widgetname.'nav', 'local_joulegrader');

        //render the select form
        $selectform = $OUTPUT->render($select);

        //next link
        $nextlink = '';
        if ($nextid = $navwidget->get_nextid()) {
            $linkurl->param($navwidget->get_param(), $nextid);
            $nextlink = $OUTPUT->action_icon($linkurl, new pix_icon('t/right', get_string('next')));
        }

        return $prevlink . $selectform . $nextlink;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_assignment_submission_online $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assignment_submission_online(local_joulegrader_lib_pane_view_mod_assignment_submission_online $viewpane) {
        global $USER, $OUTPUT;
        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assignment();

        //need the submission
        $submission = $gradingarea->get_submission();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {

            //dates html
            $html .= $this->help_render_assignment_dates($assignment, $submission);

            //submission html
            $html .= $OUTPUT->box_start('generalbox boxwidthwide boxaligncenter', 'online');
            if (!empty($submission)) {
                $text = file_rewrite_pluginfile_urls($submission->data1, 'pluginfile.php', $gacontext->id, 'mod_assignment', $assignment->filearea, $submission->id);
                $html .= format_text($text, $submission->data2, array('overflowdiv'=>true));

            } else {
                $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
            }
            $html .= $OUTPUT->box_end();
        }

        return $html;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_assignment_submission_offline $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assignment_submission_offline(local_joulegrader_lib_pane_view_mod_assignment_submission_offline $viewpane) {
        global $USER;

        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assignment();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {
            //dates html
            $html .= $this->help_render_assignment_dates($assignment);

            //nothing to display for offline
            $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
        }

        return $html;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_assignment_submission_uploadsingle $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assignment_submission_uploadsingle(local_joulegrader_lib_pane_view_mod_assignment_submission_uploadsingle $viewpane) {
        global $USER;

        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assignment();

        //need the submission
        $submission = $gradingarea->get_submission();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {
            //dates html
            $html .= $this->help_render_assignment_dates($assignment);

            //get the file from a submission
            $file = $viewpane->get_file();
            if (!empty($file)) {
                //render the file
                $html .= $this->help_render_assignment_uploadsingle_file($file, $submission, $gacontext);
            } else {
                //nothing to display
                $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
            }
        }

        return $html;
    }

    /**
     * Renders the viewpane for upload assignment type (Advanced Uploading)
     *
     * @param local_joulegrader_lib_pane_view_mod_assignment_submission_upload $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assignment_submission_upload(local_joulegrader_lib_pane_view_mod_assignment_submission_upload $viewpane) {
        global $USER, $OUTPUT;

        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assignment();

        //need the submission
        $submission = $gradingarea->get_submission();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {
            //dates html
            $html .= $this->help_render_assignment_dates($assignment);

            //get the file from a submission
            $fileareatree = $viewpane->get_fileareatree();
            if (!empty($fileareatree)) {
                //render the heading
                if (!$assignment->drafts_tracked() or !$assignment->isopen() or $assignment->is_finalized($submission)) {
                    $html .= $OUTPUT->heading(get_string('submission', 'assignment'), 3);
                } else {
                    $html .= $OUTPUT->heading(get_string('submissiondraft', 'assignment'), 3);
                }

                $module = array('name'=>'mod_assignment', 'fullpath'=>'/mod/assignment/assignment.js', 'requires'=>array('yui2-treeview'));
                $htmlid = 'local-joulegrader-assignment-files-tree';
                $this->page->requires->js_init_call('M.mod_assignment.init_tree', array(true, $htmlid), false, $module);

                $html .= html_writer::tag('div', $this->help_htmllize_tree($gacontext, $submission, $fileareatree), array('id' => $htmlid));

            } else {
                //nothing to display
                $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
            }

        }

        return $html;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     * (Modified from mod/assignment/renderer.php)
     *
     * @param context $context
     * @param stdClass $submission
     * @param array
     *
     * @return string
     */
    protected function help_htmllize_tree($context, $submission, $dir) {
        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }

        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon("f/folder", $subdir['dirname'], 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.s($subdir['dirname']).'</div> '.$this->help_htmllize_tree($context, $submission, $subdir).'</li>';
        }

        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $filepath = $file->get_filepath();
            $icon = mimeinfo("icon", $filename);

            $fileurl = moodle_url::make_pluginfile_url($context->id, 'mod_assignment', 'submission', $submission->id, $filepath, $filename, true);
            $filelink = html_writer::link($fileurl, $filename);

            $image = $this->output->pix_icon("f/$icon", $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.$filelink.' </div></li>';
        }

        $result .= '</ul>';

        return $result;
    }

    /**
     * @param stored_file $file
     * @param stdClass $submission
     * @param stdClass $context
     * @return string
     */
    protected function help_render_assignment_uploadsingle_file(stored_file $file, $submission, $context) {
        $download = array('application/zip', 'application/x-tar', 'application/g-zip');    // binary formats
        $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
            'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
            'video/quicktime', 'video/mpeg', 'video/mp4',
            'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats
            'application/pdf', 'text/html',
        );

        //get the filename
        $filename = $file->get_filename();

        //get the mimetype
        $mimetype = $file->get_mimetype();

        if (in_array($mimetype, $embed)) {
            $html = $this->help_render_assignment_file_embedded($file, $submission, $context, $filename, $mimetype);
        } else {
            $html = $this->help_render_assignment_file_download($file, $submission, $context, $filename, $mimetype);
        }

        return $html;
    }

    /**
     * @param stored_file $file
     * @param $submission
     * @param $context
     * @param $filename
     * @param $mimetype
     * @return string
     */
    protected function help_render_assignment_file_download(stored_file $file, $submission, $context, $filename, $mimetype) {
        global $OUTPUT;
        //make the url to the file
        $fullurl = moodle_url::make_pluginfile_url($context->id, 'mod_assignment', 'submission', $submission->id, $file->get_filepath(), $filename, true);

        $html = '<a href="'.$fullurl.'" ><img src="'.$OUTPUT->pix_url(file_mimetype_icon($mimetype)).'" class="icon" alt="'.$mimetype.'" />'.s($filename).'</a>';

        return $html;
    }

    /**
     * @param stored_file $file
     * @param $submission
     * @param $context
     * @param $filename
     * @param $mimetype
     * @return string
     */
    protected function help_render_assignment_file_embedded(stored_file $file, $submission, $context, $filename, $mimetype) {
        global $CFG, $PAGE;
        require_once($CFG->libdir . '/resourcelib.php');
        //Code from modified from mod/resource/locallib.php
        //make the url to the file
        $fullurl = moodle_url::make_pluginfile_url($context->id, 'local_joulegrader', 'gradingarea', $submission->id, '/mod_assignment_submission/', $filename);

        //title is not used
        $title = '';

        //clicktopen
        $clicktoopen = get_string('clicktoopen2', 'resource', "<a href=\"$fullurl\">$filename</a>");

        //get the extension
        $extension = resourcelib_get_extension($file->get_filename());

        if (in_array($mimetype, array('image/gif','image/jpeg','image/png'))) {  // It's an image
            $html = resourcelib_embed_image($fullurl, $title);

        } else if ($mimetype === 'application/pdf') {
            // PDF document -- had to pull this out from resourcelib b/c of the javascript
            $html = <<<EOT
<div class="resourcecontent resourcepdf">
  <object id="resourceobject" data="$fullurl" type="application/pdf" width="800" height="600">
    <param name="src" value="$fullurl" />
    $clicktoopen
  </object>
</div>
EOT;
            // the size is hardcoded in the boject obove intentionally because it is adjusted by the following function on-the-fly
            $PAGE->requires->js_init_call('M.local_joulegrader.init_maximised_embed', array('resourceobject'), true
                    , array('name' => 'local_joulegrader', 'fullpath' => '/local/joulegrader/javascript.js'));

        } else if ($mimetype === 'audio/mp3') {
            // MP3 audio file
            $html = resourcelib_embed_mp3($fullurl, $title, $clicktoopen);

        } else if ($mimetype === 'video/x-flv' or $extension === 'f4v') {
            // Flash video file
            $html = resourcelib_embed_flashvideo($fullurl, $title, $clicktoopen);

        } else if ($mimetype === 'application/x-shockwave-flash') {
            // Flash file
            $html = resourcelib_embed_flash($fullurl, $title, $clicktoopen);

        } else if (substr($mimetype, 0, 10) === 'video/x-ms') {
            // Windows Media Player file
            $html = resourcelib_embed_mediaplayer($fullurl, $title, $clicktoopen);

        } else if ($mimetype === 'video/quicktime') {
            // Quicktime file
            $html = resourcelib_embed_quicktime($fullurl, $title, $clicktoopen);

        } else if ($mimetype === 'video/mpeg') {
            // Mpeg file
            $html = resourcelib_embed_mpeg($fullurl, $title, $clicktoopen);

        } else if ($mimetype === 'audio/x-pn-realaudio') {
            // RealMedia file
            $html = resourcelib_embed_real($fullurl, $title, $clicktoopen);

        } else {
            // anything else - just try object tag enlarged as much as possible
            $html = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
        }

        return $html;
    }

    /**
     * @param $assignment
     * @param null $submission
     * @return string
     */
    protected function help_render_assignment_dates($assignment, $submission = NULL) {
        global $OUTPUT, $CFG;

        //check to make sure there is something to include in the date box
        if (empty($assignment->assignment->timeavailable) && empty($assignment->assignment->timedue)
                && empty($submission)) {
            return '';
        }

        $html = $OUTPUT->box_start('generalbox boxaligncenter', 'dates');
        $html .= html_writer::start_tag('table');
        if ($assignment->assignment->timeavailable) {
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', get_string('availabledate', 'assignment'), array('class' => 'c0'));
            $html .= html_writer::tag('td', userdate($assignment->assignment->timeavailable), array('class' => 'c1'));
            $html .= html_writer::end_tag('tr');
        }
        if ($assignment->assignment->timedue) {
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', get_string('duedate', 'assignment'), array('class' => 'c0'));
            $html .= html_writer::tag('td', userdate($assignment->assignment->timedue), array('class' => 'c1'));
            $html .= html_writer::end_tag('tr');
        }

        if (!empty($submission)) {
            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('td', get_string('lastedited'), array('class' => 'c0'));
            $html .= html_writer::start_tag('td', array('class' => 'c1'));
            $html .= userdate($submission->timemodified);

            /// Decide what to count
            if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
                $html .= ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')';
            } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                $html .= ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')';
            }

            $html .= html_writer::end_tag('td');
            $html .= html_writer::end_tag('tr');
        }
        $html .= html_writer::end_tag('table');
        $html .= $OUTPUT->box_end();

        return $html;
    }
}