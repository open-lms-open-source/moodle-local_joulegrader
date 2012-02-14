<?php
defined('MOODLE_INTERNAL') or die('Direct access to this script is forbidden.');

/**
 * joule Grader Default Controller
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */
class local_joulegrader_controller_default extends mr_controller {

    /**
     * Require capabilities
     */
    public function require_capability() {
        switch ($this->action) {
            case 'view':
            default:
                if (!has_capability('local/joulegrader:grade', $this->get_context())) {
                    require_capability('local/joulegrader:view', $this->get_context());
                }
        }
    }

    /**
     * Controller Initialization
     *
     */
    public function init() {
        global $PAGE;

        //no heading
        $this->heading->text = '';

        //add 'joule Grader' to bread crumb
        $PAGE->navbar->add(get_string('pluginname', 'local_joulegrader'));
    }

    /**
     * Main view action
     *
     * @return string - the html for the view action
     */
    public function view_action() {
        global $OUTPUT, $COURSE, $PAGE;
        //get the joule grader header info
        //link nav
        $linknav = $OUTPUT->action_link(new moodle_url('/course/view.php', array('id' => $COURSE->id)), get_string('course'));
        $linknav .= ' | ' . $OUTPUT->action_link(new moodle_url('/grade/report/index.php', array('id' => $COURSE->id))
                , get_string('gradebook', 'local_joulegrader'));
        $linknav = $OUTPUT->container($linknav, 'content');

        //button nav
        $buttonnav = '';

        //pull out the users helper and gradingareas helper
        $usershelper = $this->helper->users;

        //@var local_joulegrader_helper_gradingareas $gareashelper
        $gareashelper = $this->helper->gradingareas;

        //initialize the navigation
        $this->helper->navigation($usershelper, $gareashelper, $this->get_context());

        //activity navigation
        $activitynav = $this->helper->navigation->get_activity_navigation();
        $activitynav = $OUTPUT->container($activitynav, null, 'local-joulegrader-activitynav');

        //user navigation
        $usernav = $this->helper->navigation->get_users_navigation();
        $usernav = $OUTPUT->container($usernav, null, 'local-joulegrader-usernav');

        $menunav = $OUTPUT->container($activitynav . $usernav, 'content');

        $usernavcon = $OUTPUT->container($linknav, 'yui3-u-1-3', 'local-joulegrader-linknav');
        $buttonnavcon = $OUTPUT->container($buttonnav, 'yui3-u-1-3', 'local-joulegrader-buttonnav');
        $activitynavcon = $OUTPUT->container($menunav, 'yui3-u-1-3', 'local-joulegrader-menunav');

        $currentareaid = $gareashelper->get_currentarea();
        $currentuserid = $usershelper->get_currentuser();

        $panescontainer = '';
        //if the current user id and the current area id are not empty, load the class and get the pane contents
        if (!empty($currentareaid) && !empty($currentuserid)) {
            $renderer = $PAGE->get_renderer('local_joulegrader');

            //load the current area instance
            $gradeareainstance = $gareashelper::get_gradingarea_instance($currentareaid, $currentuserid);
            $viewhtml = $renderer->render($gradeareainstance->get_viewpane());

            //get the view pane contents
            $viewpane = '<div class="content">' . $viewhtml . '</div>';

            //get the grade pane contents
            $gradepane = '<div class="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus hendrerit luctus nibh vitae vehicula. Phasellus malesuada accumsan faucibus. Nunc at leo risus, et tempor lacus. Etiam euismod quam et massa dictum blandit sit amet in urna. Phasellus aliquet, lorem in pharetra malesuada, sapien lectus auctor lacus, eget imperdiet massa erat nec enim. Nullam blandit, nibh at convallis viverra, metus nunc pretium eros, consectetur elementum lectus leo ut risus. Suspendisse vitae sem id turpis consequat hendrerit. Aenean id fringilla quam. Nam dictum, nisl posuere condimentum iaculis, erat nisl molestie ipsum, quis accumsan lacus velit non risus. Etiam sollicitudin porttitor viverra. Morbi eget nibh eget ante varius rutrum id a sem. Morbi dictum sodales eros, sagittis ultrices metus auctor at. Sed sed mauris neque, ut laoreet sapien. Nullam ullamcorper dictum turpis consectetur fringilla.

Quisque interdum, turpis in volutpat placerat, metus augue fringilla quam, porta adipiscing mi ligula eu turpis. Nunc a interdum ipsum. Curabitur varius, ante quis euismod egestas, dolor nunc vestibulum velit, vel blandit est odio quis ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum faucibus, orci ut euismod adipiscing, diam justo tempus libero, vitae ultrices erat dui a nisi. Aenean pellentesque auctor nibh, eget rhoncus dui pharetra in. Maecenas scelerisque diam vitae ipsum pulvinar vehicula. In hac habitasse platea dictumst. Sed molestie feugiat ipsum, vitae suscipit nisi egestas at. Pellentesque nec augue at nibh vulputate congue.</div>';

            $panescontainer = $OUTPUT->container($viewpane, 'yui3-u-4-5', 'local-joulegrader-viewpane');
            $panescontainer .= $OUTPUT->container($gradepane, 'yui3-u-1-5', 'local-joulegrader-gradepane');
        } else {
            $panescontainer = $OUTPUT->container(html_writer::tag('h1', get_string('nothingtodisplay', 'local_joulegrader')), 'content');
        }

        //navigation container
        $output = $OUTPUT->container($usernavcon . $buttonnavcon . $activitynavcon, 'yui3-u-1', 'local-joulegrader-navigation');

        //panes container
        $output .= $OUTPUT->container($panescontainer, 'yui3-u-1', 'local-joulegrader-panes');

        //wrap it all up
        $output = $OUTPUT->container($output, 'yui3-g', 'local-joulegrader');

        //return all of that
        return $output;
    }
}


