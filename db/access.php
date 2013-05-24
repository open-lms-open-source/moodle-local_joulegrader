<?php
defined('MOODLE_INTERNAL') or die();

/**
 * joule Grader capability definitions
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

$capabilities = array(
    'local/joulegrader:grade' => array(
        'riskbitmask' => RISK_PERSONAL | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
    ),

    'local/joulegrader:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW
        )
    ),
);