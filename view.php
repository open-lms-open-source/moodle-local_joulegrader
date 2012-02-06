<?php

/**
 * View renderer
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

require_once('../../config.php');
require($CFG->dirroot.'/local/mr/bootstrap.php');

mr_controller::render('local/joulegrader', 'pluginname', 'local_joulegrader');