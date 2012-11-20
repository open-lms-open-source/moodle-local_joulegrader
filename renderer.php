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
        $html = html_writer::tag('div', $commentshtml . $mformhtml, array('id' => $id, 'class' => 'local_joulegrader_commentloop'));

        $module = $this->get_js_module();
        $PAGE->requires->js_init_call('M.local_joulegrader.init_commentloop', array('id' => $id), true, $module);

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
        $content = file_rewrite_pluginfile_urls($comment->get_content(), 'pluginfile.php', $comment->get_context()->id
                , 'local_joulegrader', 'comment', $comment->get_id());
        $content = $this->filter_kaltura_video(format_text($content, FORMAT_HTML));
        $commentcontent = html_writer::tag('div', $content, array('class' => 'local_joulegrader_comment_content'));

        //coment body
        $commentbody = $commenttime;
        $commentdeleted = $comment->get_deleted();

        if ($commentdeleted) {
            //comment has been deleted, check for admin capability
            if (has_capability('moodle/site:config', context_system::instance())) {
                //this is an admin viewing, they can see the content of the comment still
                $commentbody .= $commentcontent;
            }
            //everyone sees who deleted the comment and when
            $commentbody .= get_string('commentdeleted', 'local_joulegrader'
                    , array('deletedby' => fullname($commenter), 'deletedon' => userdate($commentdeleted, '%d %B %H:%M')));
        } else {
            //comment has not been deleted, add the comment content
            $commentbody .= $commentcontent;
        }

        //comment body
        $commentbody = html_writer::tag('div', $commentbody, array('class' => 'local_joulegrader_comment_body'));

        //delete button
        $deletebutton = '';
        if ($comment->user_can_delete()) {
            $deleteurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $COURSE->id, 'action' => 'deletecomment'
                    , 'commentid' => $comment->get_id(), 'sesskey' => sesskey()));
            $deletebutton = $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete'
                , get_string('deletecomment', 'local_joulegrader')));
        }
        $deletebutton = html_writer::tag('div', $deletebutton, array('class' => 'local_joulegrader_comment_delete'));

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
     * Helper method to make kaltura video smaller
     *
     * @param string $content
     * @return string - filtered comment content
     */
    protected function filter_kaltura_video($content) {
        // See if there is a kaltura_player, if not return the content
        if (strpos($content, 'kaltura_player') === false) {
            return $content;
        }
        $errors = libxml_use_internal_errors(true);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML($content);

        libxml_clear_errors();
        libxml_use_internal_errors($errors);

        $changes = false;
        foreach ($doc->getElementsByTagName('object') as $objecttag) {
            $objid = $objecttag->getAttribute('id');
            if (strpos($objid, 'kaltura_player') !== false) {
                // set the width and height
                $objecttag->setAttribute('width', '200px');
                $objecttag->setAttribute('height', '166px');

                $changes = true;
                break;
            }
        }

        if ($changes) {
            // only change $content if the attributes were changed above
            $content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $doc->saveHTML()));
        }
        return $content;
    }

    /**
     * Renders grade pane
     *
     * @param local_joulegrader_lib_pane_grade_mod_assignment_submission_class $gradepane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_grade_mod_assignment_submission_class(local_joulegrader_lib_pane_grade_mod_assignment_submission_class $gradepane) {
        return $this->help_render_gradepane($gradepane);
    }

    /**
     * @param local_joulegrader_lib_pane_grade_mod_hsuforum_posts_class $gradepane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_grade_mod_hsuforum_posts_class(local_joulegrader_lib_pane_grade_mod_hsuforum_posts_class $gradepane) {
        return $this->help_render_gradepane($gradepane);
    }

    /**
     * @param local_joulegrader_lib_pane_grade_mod_assign_submissions_class $gradepane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_grade_mod_assign_submissions_class(local_joulegrader_lib_pane_grade_mod_assign_submissions_class $gradepane) {
        return $this->help_render_gradepane($gradepane);
    }

    /**
     * @param local_joulegrader_lib_pane_grade_abstract $gradepane
     * @return string
     */
    protected function help_render_gradepane($gradepane) {
        global $PAGE, $CFG;

        $html = '';
        $modalhtml = '';

        if (!$gradepane->has_grading()) {
            //no grade for this assignment
            $html .= html_writer::tag('div', get_string('notgraded', 'local_joulegrader'), array('class' => 'local_joulegrader_notgraded'));
        } else if ($gradepane->has_teachercap()) {
            $posturl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $gradepane->get_courseid()
            , 'garea' => $gradepane->get_gradingarea()->get_areaid(), 'guser' => $gradepane->get_gradingarea()->get_guserid(), 'action' => 'process'));

            if ($needsgrading = optional_param('needsgrading', 0, PARAM_BOOL)) {
                $posturl->param('needsgrading', 1);
            }

            $mrhelper = new mr_helper();

            require_once($CFG->dirroot . '/local/joulegrader/form/gradepaneform.php');
            // Teacher view of the grading pane
            if ($gradepane->has_modal()) {
                require_once($CFG->dirroot . '/local/joulegrader/form/grademodalform.php');

                // Render current grade and modal button.
                $html .= $this->help_render_currentgrade($gradepane);
                $html .= $this->help_render_modalbutton($gradepane);

                // Load up the modal form
                $modalform = new local_joulegrader_form_grademodalform($posturl, $gradepane);

                // Render the form
                $modalhtml .= $mrhelper->buffer(array($modalform, 'display'));
            }

            if ($gradepane->has_paneform()) {
                // Render the pane form for simple grading / overall feedback / file feedback.
                $paneform = new local_joulegrader_form_gradepaneform($posturl, $gradepane);

                $panehtml = $mrhelper->buffer(array($paneform, 'display'));
                $html .= html_writer::tag('div', $panehtml, array('class' => 'local_joulegrader_simplegrading'));
            }

            //advanced grading error warning
            if ($advancedgradingerror = $gradepane->get_advancedgradingerror()) {
                $html .= $advancedgradingerror;
            }

        } else {
            // Student view of the grading pane
            if ($gradepane->has_modal()) {
                //this is for a student
                $options = $gradepane->get_controller()->get_options();

                // which grading method
                $gradingmethod = $gradepane->get_gradingarea()->get_active_gradingmethod();

                //get grading info
                $item = $gradepane->get_gradinginfo()->items[0];
                $grade = $item->grades[$gradepane->get_gradingarea()->get_guserid()];

                // check to see if this we should generate based on settings and grade
                if (empty($options['alwaysshowdefinition']) && (empty($grade->grade) || !empty($grade->hidden))) {
                    return $html;
                }

                // Render current grade and modal button.
                $html .= $this->help_render_currentgrade($gradepane);
                $html .= $this->help_render_modalbutton($gradepane);

//                if ((!$grade->grade === false) && empty($grade->hidden)) {
//                    $gradestr = '<div class="grade">'. get_string("grade").': '.$grade->str_long_grade. '</div>';
//                } else {
//                    $gradestr = '';
//                }
                $gradestr = $this->help_render_currentgrade($gradepane);
                $controller = $gradepane->get_controller();

                if (!$gradepane->has_active_gradinginstances()) {
                    $renderer = $controller->get_renderer($PAGE);
                    $options = $controller->get_options();
                    switch ($gradingmethod) {
                        case 'rubric':
                            $criteria = $controller->get_definition()->rubric_criteria;
                            $modalhtml = $renderer->display_rubric($criteria, $options, $controller::DISPLAY_VIEW, 'rubric');
                            break;
                        case 'checklist':
                            $groups = $controller->get_definition()->checklist_groups;
                            $modalhtml = $renderer->display_checklist($groups, $options, $controller::DISPLAY_VIEW, 'checklist');
                            break;
                        case 'guide':
                            $criteria = $controller->get_definition()->guide_criteria;
                            $modalhtml = $renderer->display_guide($criteria, '', $options, $controller::DISPLAY_VIEW, 'guide');
                            break;
                    }
                } else {
                    $controller->set_grade_range(make_grades_menu($gradepane->get_grade()));
                    $modalhtml = $controller->render_grade($PAGE, $gradepane->get_agitemid(), $item, $gradestr, false);
                }
            } else {
                $grade = -1;

                $gradinginfo = $gradepane->get_gradinginfo();
                if (!empty($gradinginfo->items[0]) and !empty($gradinginfo->items[0]->grades[$gradepane->get_gradingarea()->get_guserid()])
                    and !is_null($gradinginfo->items[0]->grades[$gradepane->get_gradingarea()->get_guserid()]->grade)) {
                    $grade = $gradinginfo->items[0]->grades[$gradepane->get_gradingarea()->get_guserid()]->str_grade;
                }

                //start the html
                $html = html_writer::start_tag('div', array('id' => 'local-joulegrader-gradepane-grade'));
                if ($gradepane->get_grade() < 0) {
                    $html .= get_string('grade') . ': ';
                    if ($grade != -1) {
                        $html .= $grade;
                    } else {
                        $html .= get_string('nograde');
                    }
                } else {
                    //if grade isn't set yet then, make is blank, instead of -1
                    if ($grade == -1) {
                        $grade = ' - ';
                    }
                    $html .= get_string('gradeoutof', 'local_joulegrader', $gradepane->get_grade()) . ': ';
                    $html .= $grade;
                }
                $html .= html_writer::end_tag('div');
            }
        }

        if (!empty($modalhtml)) {
            //wrap it in the proper modal html
            $modalhtml = html_writer::tag('div', $modalhtml, array('class' => 'yui3-widget-bd'));
            $modalhtml = html_writer::tag('div', $modalhtml, array('id' => 'local-joulegrader-gradepane-panel', 'class' => 'dontshow'));

            $html .= $modalhtml;
        }

        $module = $this->get_js_module();
        $jsoptions = array(
            'id' => 'local-joulegrader-gradepane-panel',
            'grademethod' => $gradepane->get_gradingarea()->get_active_gradingmethod(),
        );

        $PAGE->requires->js_init_call('M.local_joulegrader.init_gradepane_panel', array($jsoptions), false, $module);

        return $html;
    }

    protected function help_render_currentgrade($gradepane) {
        // Current grade.
        $grade = $gradepane->get_gradinginfo()->items[0]->grades[$gradepane->get_gradingarea()->get_guserid()];
        if ((!$grade->grade === false) && empty($grade->hidden)) {
            $gradeval = $grade->str_long_grade;
        } else {
            $gradeval = '-';
        }

        return '<div class="grade">'. get_string('grade').': '.$gradeval. '</div>';
    }

    protected function help_render_modalbutton($gradepane) {
        $gradingmethod = $gradepane->get_gradingarea()->get_active_gradingmethod();
        $teachercap = $gradepane->has_teachercap();

        $buttonatts = array('type' => 'button', 'id' => 'local-joulegrader-preview-button');
        $role = !empty($teachercap) ? 'teacher' : 'student';
        $viewbutton = html_writer::tag('button', get_string('view' . $gradingmethod . $role, 'local_joulegrader'), $buttonatts);

        $html = html_writer::tag('div', $viewbutton, array('id' => 'local-joulegrader-viewpreview-button-con'));

        // needsupdate?
        if ($gradepane->get_needsupdate()) {
            $html .= html_writer::tag('div', get_string('needregrademessage', 'gradingform_' . $gradingmethod), array('class' => "gradingform_$gradingmethod-regrade"));
        }

        return $html;
    }

    /**
     * Get js module for js_init_calls
     *
     * @return array
     */
    protected function get_js_module() {
        $a = new stdClass();
        $a->criterianame = '##SHORTNAME##';
        $a->maxscore = '##MAXSCORE##';

        return array(
            'name' => 'local_joulegrader',
            'fullpath' => '/local/joulegrader/javascript.js',
            'requires' => array(
                'base',
                'node',
                'event',
                'io',
                'panel',
                'dd-plugin',
                'json-parse'
            ),
            'strings' => array(
                array('rubric', 'local_joulegrader'),
                array('checklist', 'local_joulegrader'),
                array('close', 'local_joulegrader'),
                array('rubricerror', 'local_joulegrader'),
                array('guideerror', 'local_joulegrader'),
                array('err_scoreinvalid', 'gradingform_guide', $a),
            ),
        );
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
        $previd = $navwidget->get_previd();
        if (!is_null($previd)) {
            $linkurl->param($navwidget->get_param(), $previd);
            $prevlink = $OUTPUT->action_icon($linkurl, new pix_icon('t/left', get_string('previous', 'local_joulegrader', strtolower($navwidget->get_label()))));
        }

        //select menu
        $formid = "local-joulegrader-{$widgetname}nav-menu";
        $select = new single_select($widgeturl, $navwidget->get_param(), $navwidget->get_options()
            , $navwidget->get_currentid(), '', $formid);

        //set some select attributes
        $select->set_help_icon($widgetname.'nav', 'local_joulegrader');
        $select->tooltip = get_string($widgetname.'nav', 'local_joulegrader');

        //render the select form
        $selectform = $OUTPUT->render($select);

        //next link
        $nextlink = '';
        $nextid = $navwidget->get_nextid();
        if (!is_null($nextid)) {
            $linkurl->param($navwidget->get_param(), $nextid);
            $nextlink = $OUTPUT->action_icon($linkurl, new pix_icon('t/right', get_string('next', 'local_joulegrader', strtolower($navwidget->get_label()))));
        }

        return html_writer::tag('div', $prevlink . $selectform . $nextlink, array('class' => 'local_joulegrader_navwidget'));
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
            $html .= $OUTPUT->box_start('generalbox boxaligncenter', 'online');
            if (!empty($submission)) {
                $text = file_rewrite_pluginfile_urls($submission->data1, 'pluginfile.php', $gacontext->id, 'mod_assignment', $assignment->filearea, $submission->id);
                $html .= format_text($text, $submission->data2, array('overflowdiv'=>true));

            } else {
                $html .= html_writer::tag('h3', $viewpane->get_emptymessage(), array('class' => 'main'));
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
            }

            if ($assignment->notes_allowed() && !empty($submission) && !empty($submission->data1)) {
                $html .= $OUTPUT->heading(get_string('notes', 'assignment'));
                $html .= $OUTPUT->box(format_text($submission->data1, FORMAT_HTML, array('overflowdiv'=>true)), 'generalbox boxaligncenter boxwidthwide');
            }

            if (empty($fileareatree) && (!$assignment->notes_allowed() || empty($submission) || empty($submission->data1))) {
                //nothing to display
                $html .= html_writer::tag('h3', $viewpane->get_emptymessage());
            }

        }

        return $html;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_hsuforum_posts_class $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_hsuforum_posts_class(local_joulegrader_lib_pane_view_mod_hsuforum_posts_class $viewpane) {
        global $PAGE;

        $context = $viewpane->get_gradingarea()->get_gradingmanager()->get_context();
        $cm      = get_coursemodule_from_id('hsuforum', $context->instanceid, 0, false, MUST_EXIST);

        /** @var $renderer mod_hsuforum_renderer */
        $renderer = $PAGE->get_renderer('mod_hsuforum');

        $html = $renderer->user_posts_overview($viewpane->get_gradingarea()->get_guserid(), $cm);

        if (empty($html)) {
            return html_writer::tag('h3', $viewpane->get_emptymessage());
        }
        return $html;
    }

    /**
     * @param local_joulegrader_lib_pane_view_mod_assign_submissions_class $viewpane
     * @return string
     */
    public function render_local_joulegrader_lib_pane_view_mod_assign_submissions_class(local_joulegrader_lib_pane_view_mod_assign_submissions_class $viewpane) {
        global $USER;
        $html = '';

        $gradingarea = $viewpane->get_gradingarea();
        $gacontext = $gradingarea->get_gradingmanager()->get_context();
        $guserid   = $gradingarea->get_guserid();

        //need the assignment
        $assignment = $gradingarea->get_assign();

        //need the submission
        $submission = $gradingarea->get_submission();

        $hasstudentcap = has_capability($gradingarea::get_studentcapability(), $gacontext);
        $hasteachercap = has_capability($gradingarea::get_teachercapability(), $gacontext);

        //check capabilities
        if ($hasteachercap || ($hasstudentcap && $USER->id == $guserid)) {
            // Determine if we need to display a late submission message.
            if (!empty($submission) && (!empty($submission->timemodified)) && !empty($assignment->get_instance()->duedate)
                    && ($assignment->get_instance()->duedate < $submission->timemodified)) {
                // Format the lateness time and get the message.
                $lateby = format_time($submission->timemodified - $assignment->get_instance()->duedate);
                $html .= html_writer::tag('div', get_string('assign23-latesubmission', 'local_joulegrader', $lateby));
            }

            if (!empty($submission)) {
                $submissionplugins = $assignment->get_submission_plugins();
                foreach ($submissionplugins as $plugin) {
                    $pluginclass = get_class($plugin);
                    // First make sure that the submission plugin is supported by joule Grader.
                    if (!in_array($pluginclass, $gradingarea->get_supported_plugins())) {
                        // Submission plugin not currently supported by joule Grader, just continue to next plugin.
                        continue;
                    }
                    if ($plugin->is_enabled() && $plugin->is_visible() && !$plugin->is_empty($submission)) {
                        $rendermethod = 'help_render_' . $pluginclass;

                        $pluginhtml = html_writer::tag('div', $plugin->get_name(), array('class' => 'local_joulegrader_assign23_submission_name'));
                        $pluginhtml .= $this->$rendermethod($plugin, $assignment, $submission);

                        $attributes = array('class' => 'local_joulegrader_assign23_submission');
                        $html .= html_writer::tag('div', $pluginhtml, $attributes);
                    }
                }
            }
        }

        if (empty($html)) {
            return html_writer::tag('h3', $viewpane->get_emptymessage());
        }

        return $html;
    }

    /**
     * @param $plugin
     * @param assign $assignment
     * @param $submission
     * @return string
     */
    public function help_render_assign_submission_file($plugin, $assignment, $submission) {
        $context = $assignment->get_context();
        $fs = get_file_storage();
        $filetree = $fs->get_area_tree($context->id, 'assignsubmission_file', 'submission_files', $submission->id);
        $this->preprocess_filetree($assignment, $submission, $filetree);

        $htmlid = 'local_joulegrader_assign_files_tree_'.uniqid();
        $this->page->requires->js_init_call('M.mod_assign.init_tree', array(true, $htmlid));
        $treehtml = html_writer::start_tag('div', array('id' => $htmlid));
        $treehtml .= $this->help_htmllize_assign_submission_file_tree($context, $submission, $filetree);
        $treehtml .= html_writer::end_tag('div');

        $moodleurl = new moodle_url('/local/joulegrader/view.php', array('action' => 'downloadall', 's' => $submission->id
            , 'courseid' => $assignment->get_instance()->course));
        $html = $treehtml . html_writer::link($moodleurl, get_string('downloadall', 'local_joulegrader'));
        return $html;
    }

    /**
     * Preprocesses the file tree for assignsubmission_file plugin to add necessary links.
     *
     * Modified from mod/assign/renderable.php's assign_files::preprocess() method
     * @param $assignment
     * @param $submission
     * @param $filetree
     */
    protected function preprocess_filetree($assignment, $submission, $filetree) {
        static $downloadstr = null;
        if (is_null($downloadstr)) {
            $downloadstr = '('.get_string('download', 'local_joulegrader').')';
        }
        static $viewinlinestr = null;
        if (is_null($viewinlinestr)) {
            $viewinlinestr = '('.get_string('viewinline', 'local_joulegrader').')';
        }

        foreach ($filetree['subdirs'] as $subdir) {
            $this->preprocess_filetree($assignment, $submission, $subdir);
        }

        foreach ($filetree['files'] as $file) {
            $filename = $file->get_filename();
            $filepath = $file->get_filepath();

            $fileurl = moodle_url::make_pluginfile_url($assignment->get_context()->id, 'assignsubmission_file', 'submission_files', $submission->id, $filepath, $filename, true);
            $file->viewinlinelink = $this->get_viewinline_link($assignment, $submission, $viewinlinestr);
            $file->downloadlink = html_writer::link($fileurl, $downloadstr);
        }
    }

    /**
     * @param $assignment
     * @param $submission
     * @param $viewinlinestr
     * @return string
     */
    protected function get_viewinline_link($assignment, $submission, $viewinlinestr) {
        $viewinlineurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $assignment->get_course()->id, 's' => $submission->id, ));
        $viewinlinelink = html_writer::link($viewinlineurl, $viewinlinestr);
        return '';
    }

    /**
     * Creates html necessary for YUI treeview for the assignsubmission_file plugin's file tree
     * Modified from mod/assign/render.php's htmllize() method
     *
     * @param $context
     * @param $submission
     * @param $dir
     * @return string
     */
    protected function help_htmllize_assign_submission_file_tree($context, $submission, $dir) {
        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }

        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(), $subdir['dirname'], 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.s($subdir['dirname']).'</div> '
                    .$this->help_htmllize_assign_submission_file_tree($context, $submission, $subdir).'</li>';
        }

        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();

            $image = $this->output->pix_icon(file_file_icon($file), $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.$filename. ' ' . $file->viewinlinelink . ' ' . $file->downloadlink.'</div></li>';
        }

        $result .= '</ul>';

        return $result;
    }

    /**
     * @param $plugin
     * @param $assignment
     * @param $submission
     * @return string
     */
    public function help_render_assign_submission_onlinetext($plugin, $assignment, $submission) {
        return $plugin->view($submission);
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

        $mediarenderer = $PAGE->get_renderer('core', 'media');
        $embedoptions = array(
            core_media::OPTION_TRUSTED => true,
            core_media::OPTION_BLOCK => true,
        );

        if (file_mimetype_in_typegroup($mimetype, 'web_image')) {  // It's an image
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
            // the size is hardcoded in the object above intentionally because it is adjusted by the following function on-the-fly
            $PAGE->requires->js_init_call('M.local_joulegrader.init_maximised_embed', array('resourceobject'), true, $this->get_js_module());

        } else if ($mediarenderer->can_embed_url($fullurl, $embedoptions)) {
            // Media (audio/video) file.
            $html = $mediarenderer->embed_url($fullurl, $title, 0, 0, $embedoptions);

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
                && (empty($submission) || empty($submission->timemodified))) {
            return '';
        }

        $html = $OUTPUT->box_start('generalbox boxaligncenter', 'dates');

        $availableon = '';
        if ($assignment->assignment->timeavailable) {
            $availableon = ' '.get_string('on', 'local_joulegrader', userdate($assignment->assignment->timeavailable));
        }

        $availableuntil = '';
        if ($assignment->assignment->timedue) {
            $availableuntil = ' '.get_string('until', 'local_joulegrader', userdate($assignment->assignment->timedue));
        }

        $availablestring = '';
        if (!empty($availableon) || !empty($availableuntil)) {
            $availablestring = get_string('assignmentavailable', 'local_joulegrader') . $availableon . $availableuntil.'. ';
        }

        $lastedited = '';
        if (!empty($submission) && !empty($submission->timemodified)) {
            // last edited string
            $lastedited = get_string('lastedited', 'local_joulegrader', userdate($submission->timemodified));

            // determine if there is a reason to do a word count
            $wordcount = '';
            if ($assignment->type == 'online') {
                /// Decide what to count
                if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_WORDS) {
                    $wordcount = ' ('.get_string('numwords', '', count_words(format_text($submission->data1, $submission->data2))).')';
                } else if ($CFG->assignment_itemstocount == ASSIGNMENT_COUNT_LETTERS) {
                    $wordcount = ' ('.get_string('numletters', '', count_letters(format_text($submission->data1, $submission->data2))).')';
                }
            }

            // add the word count
            $lastedited .= $wordcount;
            $lastedited .= '.';
        }

        $html .= $availablestring.$lastedited;
        $html .= $OUTPUT->box_end();

        return $html;
    }
}