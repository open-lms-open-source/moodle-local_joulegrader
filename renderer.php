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

        $commentloop->init();

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

        $commentlegend = html_writer::tag('legend', get_string('activitycomments', 'local_joulegrader'));

        $id = uniqid('local-joulegrader-commentloop-con-');
        $html = html_writer::tag('div', $commentshtml . $mformhtml, array('id' => $id, 'class' => 'local_joulegrader_commentloop'));
        $html = html_writer::tag('fieldset', $commentlegend . $html, array('class' => 'fieldset'));

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

        //commenter picture
        $userpic = html_writer::tag('div', $comment->get_avatar(), array('class' => 'local_joulegrader_comment_commenter_pic'));
        $username = html_writer::tag('div', $comment->get_user_fullname(), array('class' => 'local_joulegrader_comment_commenter_fullname'));

        //comment timestamp
        $commenttime = html_writer::tag('div', userdate($comment->get_timecreated(), $comment->get_dateformat()), array('class' => 'local_joulegrader_comment_time'));
        $fullnametime =  html_writer::tag('div', $username . $commenttime, array('class' => 'local_joulegrader_comment_fullnametime'));

        //comment content
//        $content = file_rewrite_pluginfile_urls($comment->get_content(), 'pluginfile.php', $comment->get_context()->id
//                , 'local_joulegrader', 'comment', $comment->get_id());
        $content = $this->filter_kaltura_video($comment->get_content());
        $commentbody = html_writer::tag('div', $content, array('class' => 'local_joulegrader_comment_content'));

        //comment body
        $commentbody = html_writer::tag('div', $commentbody, array('class' => 'local_joulegrader_comment_body'));

        //delete button
        $deletebutton = '';
        if ($comment->can_delete()) {
            $deleteparams = array(
                'courseid' => $COURSE->id,
                'action' => 'deletecomment',
                'commentid' => $comment->get_id(),
                'sesskey' => sesskey(),
                'garea' => $comment->get_gareaid(),
                'guser' => $comment->get_guserid()
            );
            $deleteurl = new moodle_url('/local/joulegrader/view.php', $deleteparams);
            $deletebutton = $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete'
                , get_string('deletecomment', 'local_joulegrader', userdate($comment->get_timecreated(), '%d %B %H:%M:%S'))));
        }
        $deletebutton = html_writer::tag('div', $deletebutton, array('class' => 'local_joulegrader_comment_delete'));

        //determine classes for comment
        $commentclasses = array('local_joulegrader_comment');

        $commenttopbar = html_writer::tag('div', $userpic . $fullnametime . $deletebutton, array('class' => 'local-joulegrader-comment-topbar'));

        //put it all together
        $html = html_writer::tag('div', $commenttopbar . $commentbody, array('class' => implode(' ', $commentclasses)));

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
        global $PAGE;

        $html = '';
        $modalhtml = '';

        if (!$gradepane->has_grading()) {
            //no grade for this assignment
            $html .= html_writer::tag('div', get_string('notgraded', 'local_joulegrader'), array('class' => 'local_joulegrader_notgraded'));
        } else if ($gradepane->has_teachercap() and !$gradepane->read_only()) {
            $mrhelper = new mr_helper();

            // Teacher view of the grading pane
            if ($gradepane->has_modal()) {

                // Render current grade and modal button.
                $html .= $this->help_render_currentgrade($gradepane);
                $html .= $this->help_render_modalbutton($gradepane);

                // Load up the modal form
                $modalform = $gradepane->get_modalform();

                // Render the form
                $modalhtml .= $mrhelper->buffer(array($modalform, 'display'));
            }

            if ($gradepane->has_paneform()) {
                // Render the pane form for simple grading / overall feedback / file feedback.
                $paneform = $gradepane->get_paneform();

                $panehtml = $mrhelper->buffer(array($paneform, 'display'));
                $html .= html_writer::tag('div', $panehtml, array('class' => 'local_joulegrader_simplegrading'));
            }

            //advanced grading error warning
            if ($advancedgradingerror = $gradepane->get_advancedgradingerror()) {
                $html .= $advancedgradingerror;
            }

        } else {
            // Student view of the grading pane
            if ($feedback = $gradepane->get_overall_feedback()) {
                $feedback = html_writer::tag('div', get_string('overallfeedback', 'local_joulegrader') . ': ' . $feedback);
            }

            if ($filefeedback = $gradepane->get_file_feedback()) {
                $filefeedback = html_writer::tag('div', get_string('filefeedback', 'local_joulegrader') . ': ' . $filefeedback);
            }

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
                    $modalhtml .= $feedback;
                    $modalhtml .= $filefeedback;
                }
            } else {
                //start the html
                $html = html_writer::start_tag('div', array('id' => 'local-joulegrader-gradepane-grade'));
                $html .= $this->help_render_currentgrade($gradepane);
                $html .= $feedback;
                $html .= $filefeedback;
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
        $gradeoutof = $gradepane->get_grade();
        $gradegrade = $gradepane->get_gradebook_grade();
        $activitygrade = $gradepane->get_activity_grade();
        if ($gradeoutof < 0) {
            //Scale grade.
            $gradeinfo = $gradepane->get_gradinginfo();
            $scale = $gradeinfo->items[0]->scaleid;
            $scale = grade_scale::fetch(array('id' => $scale));
            $scale = $scale->load_items();

            // Current gradebook grade.
            if (!empty($gradegrade) && (!$gradegrade->grade === false) && empty($gradegrade->hidden)) {
                $gbval = $scale[$gradegrade->str_long_grade];
            } else {
                $gbval = get_string('nograde');
            }

            // Activity grade.
            if ($activitygrade !== null) {
                if ($activitygrade < 0) {
                    $activitygrade = get_string('nograde');
                } else {
                    $activitygrade = $scale[(int) $activitygrade];
                }
            }
        } else {
            // Not a scale
            // Current gradebook grade.
            if (!empty($gradegrade) && (!$gradegrade->grade === false) && empty($gradegrade->hidden)) {
                $gbval = $gradegrade->str_long_grade;
            } else {
                $gbval = ' - ';
            }
            // Activity grade.
            if ($activitygrade !== null) {
                if ($activitygrade < 0) {
                    $activitygrade = ' - ';
                } else {
                    $activitygrade = $gradepane->format_gradevalue($activitygrade);
                }
            }
        }

        $currentgradestr = html_writer::tag('div', get_string('gradebookgrade', 'local_joulegrader').': '.$gbval, array('class' => 'grade'));

        if ($activitygrade !== null) {
            $currentgradestr .= html_writer::tag('div', $gradepane->get_activity_grade_label().': '.$activitygrade, array('class' => 'grade'));
        }

        return $currentgradestr;
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
    public function get_js_module() {

        return array(
            'name' => 'local_joulegrader',
            'fullpath' => '/local/joulegrader/javascript.js',
            'requires' => array(
                'base',
                'node',
                'event',
                'io',
                'dd-drag',
                'dd-constrain',
                'panel',
                'dd-plugin',
                'json-parse',
                'moodle-local_mr-accessiblepanel'
            ),
            'strings' => array(
                array('rubric', 'local_joulegrader'),
                array('checklist', 'local_joulegrader'),
                array('guide', 'local_joulegrader'),
                array('close', 'local_joulegrader'),
                array('rubricerror', 'local_joulegrader'),
                array('guideerror', 'local_joulegrader'),
                array('download', 'local_joulegrader'),
                array('err_scoreinvalid', 'gradingform_guide'),
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
        $select->set_label(get_string('navviewlabel', 'local_joulegrader', $navwidget->get_label()), array('class' => 'accesshide'));

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
        global $PAGE, $OUTPUT, $COURSE;

        /** @var local_joulegrader_lib_gradingarea_mod_hsuforum_posts_class $gradingarea */
        $gradingarea = $viewpane->get_gradingarea();
        $context = $viewpane->get_gradingarea()->get_gradingmanager()->get_context();
        $cm      = get_coursemodule_from_id('hsuforum', $context->instanceid, 0, false, MUST_EXIST);

        /** @var $renderer mod_hsuforum_renderer */
        $renderer = $PAGE->get_renderer('mod_hsuforum');

        $showonlypreference = new stdClass();
        $showonlypreference->preference = 1;
        $showonlypreference->button = '';
        if (has_capability($gradingarea::get_teachercapability(), $context)) {
            $preference = $gradingarea->get_showpost_preference();
            $buttonlabel = $gradingarea->get_showpost_preference_label($preference);
            $urlparams = array(
                'courseid' => $COURSE->id,
                'guser' => $gradingarea->get_guserid(),
                'garea' => $gradingarea->get_areaid(),
                'showposts' => !$preference,

            );
            $preferenceurl = new moodle_url('/local/joulegrader/view.php', $urlparams);
            $singlebutton = $OUTPUT->single_button($preferenceurl, $buttonlabel, 'get');

            $showonlypreference->preference = $preference;
            $showonlypreference->button = html_writer::tag('div', $singlebutton,
                    array('class' => 'local_joulegrader-hsuforum-showposts'));
        }
        $html = $renderer->user_posts_overview($gradingarea->get_guserid(), $cm, $showonlypreference);

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
        global $USER, $OUTPUT;
        $html = '';

        /** @var local_joulegrader_lib_gradingarea_mod_assign_submissions_class $gradingarea */
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

            $attemptmenu = '';
            $attemptstatus = '';
            if ($gradingarea->allows_multiple_attempts()) {
                $attempts = $gradingarea->get_all_submissions();
                $numattempts = count($attempts);
                if ($numattempts > 1) {
                    $options = array();
                    foreach ($attempts as $attempt) {
                        $a = new stdClass();
                        $a->attemptnumber = $attempt->attemptnumber + 1;
                        $a->attempttime   = userdate($attempt->timemodified);
                        $options[$attempt->attemptnumber] = get_string('attemptnumber', 'local_joulegrader', $a);
                    }
                    $selected = $gradingarea->get_attemptnumber();
                    $mostrecent = array_pop($options);
                    $options[-1] = $mostrecent;

                    $url = new moodle_url('/local/joulegrader/view.php', array('courseid' => $assignment->get_course()->id,
                            'garea' => $gradingarea->get_areaid(), 'guser' => $gradingarea->get_guserid()));

                    $select = new single_select($url, 'attempt', $options, $selected, false);
                    $select->set_label(get_string('viewingattempt', 'local_joulegrader') . ':');
                    $attemptmenu = $OUTPUT->render($select);
                }

                $submittedcount = 0;
                foreach ($attempts as $attempt) {
                    if ($attempt->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                        $submittedcount++;
                    }
                }

                $maxattempts = $assignment->get_instance()->maxattempts;
                if ($maxattempts == ASSIGN_UNLIMITED_ATTEMPTS) {
                    $maxattempts = get_string('unlimited', 'local_joulegrader');
                }

                $a = new stdClass();
                $a->number = $submittedcount;
                $a->outof  = $maxattempts;

                $attemptstatus = html_writer::tag('div', get_string('attemptstatus', 'local_joulegrader', $a));

            }

            $html .= $attemptmenu;

            // Determine if we need to display a late submission message.
            $lateby = '';
            if (!empty($submission) && (!empty($submission->timemodified)) && !empty($assignment->get_instance()->duedate)
                    && ($assignment->get_instance()->duedate < $submission->timemodified)) {
                // Format the lateness time and get the message.
                $lateby = format_time($submission->timemodified - $assignment->get_instance()->duedate);
                $lateby = html_writer::tag('div', get_string('assign23-latesubmission', 'local_joulegrader', $lateby));
            }

            if (!empty($attemptstatus) or !empty($lateby)) {
                $assignmentstatus = html_writer::tag('legend', get_string('assignmentstatus', 'local_joulegrader'));
                $html .= html_writer::tag('fieldset', $assignmentstatus . $attemptstatus . $lateby, array('class' => 'fieldset'));
            }

            if (!empty($submission)) {
                $submissionplugins = $assignment->get_submission_plugins();
                /** @var assign_submission_plugin $plugin */
                foreach ($submissionplugins as $plugin) {
                    $pluginclass = get_class($plugin);
                    // First make sure that the submission plugin is supported by joule Grader.
                    if (!in_array($pluginclass, $gradingarea->get_supported_plugins())) {
                        // Submission plugin not currently supported by joule Grader, just continue to next plugin.
                        continue;
                    }
                    if ($plugin->is_enabled() && $plugin->is_visible() && !$plugin->is_empty($submission)) {
                        $rendermethod = 'help_render_' . $pluginclass;

                        $pluginhtml = html_writer::tag('legend', $plugin->get_name(), array('class' => 'local_joulegrader_assign23_submission_name'));
                        $pluginhtml .= $this->$rendermethod($plugin, $assignment, $submission);

                        $attributes = array('class' => 'local_joulegrader_assign23_submission, fieldset', 'id' => 'local-joulegrader-assign23-' . $pluginclass);
                        $html .= html_writer::tag('fieldset', $pluginhtml, $attributes);
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
     * Generates HTML for the calculating grid widths/positions for drag and drop grade pane resize.
     *
     * @return string
     */
    public function help_render_dummygrids() {
        global $OUTPUT;

        $dummythirds = $OUTPUT->container('', 'yui3-u-2-3');
        $dummythirds .= $OUTPUT->container('', 'yui3-u-1-3   local-joulegrader-dummy');
        $output = $OUTPUT->container($dummythirds, 'yui3-u-1');

        $dummyhalves = $OUTPUT->container('', 'yui3-u-1-2');
        $dummyhalves .= $OUTPUT->container('', 'yui3-u-1-2   local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummyhalves, 'yui3-u-1');

        $dummyfifths = $OUTPUT->container('', 'yui3-u-4-5');
        $dummyfifths .= $OUTPUT->container('', 'yui3-u-1-5  local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummyfifths, 'yui3-u-1');

        $dummysixths = $OUTPUT->container('', 'yui3-u-5-6');
        $dummysixths .= $OUTPUT->container('', 'yui3-u-1-6 local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummysixths, 'yui3-u-1');

        $dummyfourths = $OUTPUT->container('', 'yui3-u-3-4');
        $dummyfourths .= $OUTPUT->container('', 'yui3-u-1-4 local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummyfourths, 'yui3-u-1');

        $dummyeighths = $OUTPUT->container('', 'yui3-u-5-8');
        $dummyeighths .= $OUTPUT->container('', 'yui3-u-3-8 local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummyeighths, 'yui3-u-1');

        $dummytwelfths = $OUTPUT->container('', 'yui3-u-7-12');
        $dummytwelfths .= $OUTPUT->container('', 'yui3-u-5-12 local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummytwelfths, 'yui3-u-1');

        $dummy24ths = $OUTPUT->container('', 'yui3-u-13-24');
        $dummy24ths .= $OUTPUT->container('', 'yui3-u-11-24 local-joulegrader-dummy');
        $output .= $OUTPUT->container($dummy24ths, 'yui3-u-1');

        return $output;
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

        $htmlid = 'local_joulegrader_assign23_files_tree_'.uniqid();
        $this->page->requires->js_init_call('M.mod_assign.init_tree', array(true, $htmlid));
        $treehtml = html_writer::start_tag('div', array('id' => $htmlid));
        $treehtml .= $this->help_htmllize_assign_submission_file_tree($context, $submission, $filetree);
        $treehtml .= html_writer::end_tag('div');

        $moodleurl = new moodle_url('/local/joulegrader/view.php', array('action' => 'downloadall', 's' => $submission->id
            , 'courseid' => $assignment->get_instance()->course));
        $html = $treehtml . html_writer::link($moodleurl, get_string('downloadall', 'local_joulegrader'));
        $html = html_writer::tag('div', $html, array('id' => 'local-joulegrader-assign23-treecon'));

        $ctrlfilename = html_writer::tag('div', '', array('id' => 'local-joulegrader-assign23-ctrl-filename', 'class' => 'control'));
        $ctrldownload = html_writer::tag('div', '', array('id' => 'local-joulegrader-assign23-ctrl-download', 'class' => 'control'));

        $jgurl = new moodle_url('/local/joulegrader/view.php', array('courseid' => $assignment->get_course()->id));

        $ctrlprevious = html_writer::link($jgurl, $this->output->pix_icon('t/left', get_string('previous')));
        $ctrlprevious = html_writer::tag('div', $ctrlprevious, array('id' => 'local-joulegrader-assign23-ctrl-previous', 'class' => 'control'));

        $labelctrlselect = html_writer::label(get_string('files'), 'menufileselect', true, array('class' => 'local_joulegrader_hidden'));
        $ctrlselect = html_writer::select(array(0 => get_string('allfiles', 'local_joulegrader')), 'fileselect', 0, false);
        $ctrlselect = html_writer::tag('div', $labelctrlselect.$ctrlselect, array('id' => 'local-joulegrader-assign23-ctrl-select', 'class' => 'control'));

        $ctrlnext = html_writer::link($jgurl, $this->output->pix_icon('t/right', get_string('next')));
        $ctrlnext = html_writer::tag('div', $ctrlnext, array('id' => 'local-joulegrader-assign23-ctrl-next', 'class' => 'control'));

        $ctrlclose = html_writer::link($jgurl, $this->output->pix_icon('all', get_string('allfiles', 'local_joulegrader'), 'local_joulegrader'));
        $ctrlclose = html_writer::tag('div', $ctrlclose, array('id' => 'local-joulegrader-assign23-ctrl-close', 'class' => 'control'));
        $controlshtml = $ctrlfilename.$ctrldownload.$ctrlprevious.$ctrlselect.$ctrlnext.$ctrlclose;
        $controlshtml = html_writer::tag('div', $controlshtml, array('id' => 'local-joulegrader-assign23-ctrl-con'));
        $html .= html_writer::tag('div', $controlshtml, array('id' => 'local-joulegrader-assign23-files-inline', 'class' => 'local_joulegrader_hidden'));

        $this->page->requires->js_init_call('M.local_joulegrader.init_viewinlinefile',
                array('courseid' => $assignment->get_instance()->course), true, $this->get_js_module());
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
            $downloadstr = get_string('download', 'local_joulegrader');
        }
        static $viewinlinestr = null;
        if (is_null($viewinlinestr)) {
            $viewinlinestr = get_string('viewinline', 'local_joulegrader');
        }

        foreach ($filetree['subdirs'] as $subdir) {
            $this->preprocess_filetree($assignment, $submission, $subdir);
        }

        foreach ($filetree['files'] as $file) {
            $filename = $file->get_filename();
            $filepath = $file->get_filepath();

            $fileurl = moodle_url::make_pluginfile_url($assignment->get_context()->id, 'assignsubmission_file', 'submission_files', $submission->id, $filepath, $filename, true);
            $file->viewinlinelink = $this->get_viewinline_link($file, $assignment, $submission, $viewinlinestr);
            $file->downloadlink = html_writer::link($fileurl, $downloadstr);
        }
    }

    /**
     * @param stored_file $file
     * @param assign $assignment
     * @param stdClass $submission
     * @param string $viewinlinestr
     * @return string
     */
    protected function get_viewinline_link($file, $assignment, $submission, $viewinlinestr) {
        $viewinlinelink = '';
        if ($this->can_embed_file($file)) {
            $fileurl = moodle_url::make_pluginfile_url($assignment->get_context()->id, 'assignsubmission_file'
                    , 'submission_files', $submission->id, $file->get_filepath(), $file->get_filename(), true);
            $viewinlinelink = html_writer::link($fileurl, $viewinlinestr, array('id' => $file->get_pathnamehash(), 'class' => 'local_joulegrader_assign23_inlinefile'));
        }
        return $viewinlinelink;
    }

    /**
     * @param stored_file $file
     * @return bool
     */
    protected function can_embed_file($file) {
        $canembed = false;
        $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml', 'image/bmp',       // images
            'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
            'video/quicktime', 'video/mpeg', 'video/mp4',
            'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats
            'application/pdf', 'text/html', 'text/plain', 'application/xml',
        );

        if (in_array($file->get_mimetype(), $embed)) {
            $canembed = true;
        }
        return $canembed;
    }

    /**
     * @param stored_file $file
     * @return string
     */
    public function help_render_assign23_file_inline(stored_file $file) {
        global $PAGE, $CFG, $COURSE;
        require_once($CFG->libdir . '/resourcelib.php');

        $html = '';

        $filename = $file->get_filename();
        $contextid = $file->get_contextid();
        $mimetype = $file->get_mimetype();

        //Code from modified from mod/resource/locallib.php
        //make the url to the file
        $fullurl = moodle_url::make_pluginfile_url($contextid, 'local_joulegrader', 'gradingarea', $file->get_itemid()
                , '/mod_assign_submissions' . $file->get_filepath(), $filename);

        $downloadurl = clone($fullurl);
        $downloadurl->param('forcedownload', 1);

        //title is not used
        $title = '';

        //clicktopen
        $clicktoopen = get_string('clicktoopen2', 'resource', "<a href=\"$downloadurl\">$filename</a>");

        $mediarenderer = $PAGE->get_renderer('core', 'media');
        $embedoptions = array(
            core_media::OPTION_TRUSTED => true,
            core_media::OPTION_BLOCK => true,
        );

        if (file_mimetype_in_typegroup($mimetype, 'web_image') || $mimetype == 'image/bmp') {  // It's an image
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
        } else if ($mediarenderer->can_embed_url($fullurl, $embedoptions)) {
            // Media (audio/video) file.
            $html = $mediarenderer->embed_url($fullurl, $title, 0, 0, $embedoptions);

        } else if (in_array($mimetype, array('application/xml', 'text/html', 'text/plain'))) {
            $html = html_writer::start_tag('div', array('class' => 'resourcecontent'));
            $text = $file->get_content();

            if ($mimetype == 'text/html') {
                $options = new stdClass();
                $options->noclean = false;
                $options->filter = false;
                $options->nocache = true; // temporary workaround for MDL-5136
                $html .= format_text($text, FORMAT_HTML, $options, $COURSE->id);
            } else if ($mimetype == 'text/plain') {
                // only filter text if filter all files is selected
                $options = new stdClass();
                $options->newlines = false;
                $options->noclean = false;
                $options->filter = false;
                $html .= '<pre>'. format_text($text, FORMAT_MOODLE, $options, $COURSE->id) .'</pre>';
            } else if ($mimetype == 'application/xml') {
                $html .= '<pre>' . htmlspecialchars($text) . '</pre>';
            }

            $html .= html_writer::end_tag('div');

        } else {
            // anything else - just try object tag enlarged as much as possible
            $html = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
        }

        return $html;
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
            $viewinlinelink = empty($file->viewinlinelink) ? '' : '('.$file->viewinlinelink.')';
            $image = $this->output->pix_icon(file_file_icon($file), $filename, 'moodle', array('class'=>'icon'));
            $result .= '<li yuiConfig=\''.json_encode($yuiconfig).'\'><div>'.$image.' '.$filename. ' ' . $viewinlinelink . ' (' . $file->downloadlink.')</div></li>';
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
        global $CFG, $PAGE, $COURSE;
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

        } else if ($mimetype == 'text/html' ) {
            $html = html_writer::start_tag('div', array('class' => 'resourcecontent'));
            $text = $file->get_content();

            $options = new stdClass();
            $options->noclean = false;
            $options->filter = false;
            $options->nocache = true; // temporary workaround for MDL-5136
            $html .= format_text($text, FORMAT_HTML, $options, $COURSE->id);

            $html .= html_writer::end_tag('div');

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