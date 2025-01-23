<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Joule Grader lang file
 *
 * @author    Sam Chaffee
 * @package   local_joulegrader
 * @copyright Copyright (c) 2015 Open LMS (https://www.openlms.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Open Grader';

//access.php strings
$string['joulegrader:grade'] = 'Grade work via Open Grader';
$string['joulegrader:view'] = 'View graded work via Open Grader';

//default.php strings
$string['gradebook'] = 'Gradebook';
$string['nothingtodisplay'] = 'Nothing to Display';
$string['needsgrading'] = 'Show Activities Requiring Grading';
$string['allactivities'] = 'Show All Activities';
$string['mobilenotsupported'] = 'Open Grader does not currently support mobile browsers';
$string['exitfullscreen'] = 'Exit full screen mode';
$string['fullscreen'] = 'Full screen mode';
$string['returncourse'] = 'Return to course';
$string['grading']  = 'Grading';

// Navigation utility strings.
$string['nogradeableareas'] = 'No gradable activities';
$string['nogradeableusers'] = 'No gradable users';

//renderer.php strings
$string['showonlyuserposts'] = 'Show only user\'s posts';
$string['groupbydiscussion'] = 'Group by discussion';
$string['activity'] = 'Gradable activity';
$string['activitynav'] = 'Gradable activities';
$string['activitynav_help'] = 'Use this widget to select which gradable activity to grade.';
$string['group'] = 'Group';
$string['groupnav'] = 'Groups';
$string['groupnav_help'] = 'Use this widget to select a group.';
$string['user'] = 'User';
$string['usernav'] = 'Users';
$string['usernav_help'] = 'Use this widget to select which user to grade.';
$string['navviewlabel'] = 'View {$a}';
$string['commentdeleted'] = 'User {$a->deletedby} deleted post on {$a->deletedon}';
$string['deletecomment'] = 'Delete comment made on {$a}';
$string['previous'] = 'Previous {$a}';
$string['next'] = 'Next {$a}';
$string['assignmentavailable'] = 'Available';
$string['on'] = 'on {$a}';
$string['until'] = 'until {$a}';
$string['lastedited'] = 'Last edited on {$a}';
$string['assign23-latesubmission'] = 'This submission was late by {$a}.';
$string['assign23-userextensiondate'] = 'Extension granted until: {$a}';
$string['downloadall'] = 'Download all files';
$string['download'] = 'download';
$string['viewinline'] = 'view inline';
$string['activitycomments'] = 'Activity comments';
$string['activitycomment'] = 'Comment';
$string['overallfeedback'] = 'Overall feedback';
$string['filefeedback'] = 'File feedback';
$string['attemptnumber'] = 'Attempt {$a->attemptnumber}: {$a->attempttime}';
$string['viewingattempt'] = 'Viewing attempt';
$string['attemptstatus'] = 'Student has made {$a->number} out of {$a->outof} attempts.';
$string['assignmentstatus'] = 'Assignment status';
$string['unlimited'] = 'unlimited';
$string['gradebookgrade'] = 'Current grade in grade book';
$string['attemptgrade'] = 'Attempt grade';

// Form strings.
$string['gradeoutof'] = 'Grade (out of {$a})';
$string['gradeoutofrange'] = 'Grade is out range';
$string['overridetext'] = 'Previously, an instructor created a grade for this activity directly in the gradebook.  Check this box if you want to replace that grade, too.';
$string['save'] = 'Save grade';
$string['saveandnext'] = 'Save grade and next';
$string['gradingdisabled'] = 'This activity\'s grading is locked. To enable grading please unlock the grade via the Gradebook.';
$string['applytoall'] = 'Apply grades and feedback to entire group';
$string['applytoall_help'] = 'If "Yes" is selected all group members will receive the grade and feedback regardless of any existing grade or feedback in the gradebook.';

$string['criteria'] = 'Criteria';
$string['checklist'] = 'Checklist';
$string['gradesaved'] = 'Grade successfully updated';
$string['gradesavedx'] = '{$a} grades successfully updated';
$string['couldnotsave'] = 'Grade could not be updated';
$string['couldnotsavex'] = 'Grade for {$a} could not be updated';
$string['notgraded'] = 'Assignment Not Graded';
$string['viewchecklistteacher'] = 'Grade with checklist';
$string['viewrubricteacher'] = 'Grade with rubric';
$string['viewcheckliststudent'] = 'View grading checklist';
$string['viewrubricstudent'] = 'View grading rubric';
$string['viewguidestudent'] = 'View grading marking guide';
$string['viewguideteacher'] = 'Grade with marking guide';
$string['guide'] = 'Marking guide';
$string['rubric'] = 'Rubric';
$string['rubricerror'] = 'Please select one level for each criterion';
$string['guideerror'] = 'Please provide a valid grade for each criterion';
$string['score'] = 'Score';
$string['gradeoverriddenstudent'] = '(Override in Gradebook: {$a})';
$string['close'] = 'Close';
$string['allfiles'] = 'All files';

//form/comment.php strings
$string['add'] = 'Save comment';
$string['attachments'] = 'Attachments';
$string['commentrequired'] = 'Comment required';
$string['commentloop'] = 'Comment Loop';

$string['notreleased'] = 'Assignment grade not released yet';

// Event strings.
$string['eventgraderviewed']   = 'Open Grader viewed';
$string['eventactivitygraded'] = 'Activity graded in Open Grader';
$string['eventcommentdeleted'] = 'Comment deleted in Open Grader';
$string['eventcommentadded']   = 'Comment added in Open Grader';

// Privacy strings.
$string['privacy:metadata:preference:fullscreen'] = 'Whether or not a user have the grader in fullscreen';
$string['privacy:metadata:preference:showpostsgrouped'] = 'Whether or not a user groups the Open Forums when grading them';
$string['privacy:request:preference:fullscreenyes'] = 'The user prefers the Open Grader in fullscreen';
$string['privacy:request:preference:fullscreenno'] = 'The user prefers the Open Grader in normal view';
$string['privacy:request:preference:hsupostsgroupedyes'] = 'The user prefers the Open Forums to be grouped when grading them';
$string['privacy:request:preference:hsupostsgroupedno'] = 'The user prefers the Open Forums not to be grouped when grading them';
