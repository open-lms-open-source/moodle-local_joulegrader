<?php
/**
 * Renderer
 *
 * @author Sam Chaffee
 * @package local/mrooms
 */

defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');


class local_joulegrader_renderer extends plugin_renderer_base {

    /**
     * Renders a navigation widget containing a previous link, a next link, and a select menu
     *
     * @param local_joulegrader_navigation_widget $navwidget
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
}