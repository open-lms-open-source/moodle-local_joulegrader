# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# Tests that the "activities requiring grading" filter survives commenting in Open Grader
#
# @package   local_joulegrader
# @copyright Copyright (c) 2026 Open LMS (https://www.openlms.net)
# @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_joulegrader
Feature: The activities requiring grading filter is kept when commenting in Open Grader
  In order to keep grading the activities I filtered for
  As a teacher
  I need Open Grader to stay on the filtered view after I save a comment

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    # The advanced grading method is what gives each assignment a grading area for Open Grader to list,
    # and submissiondrafts must be off so that a saved submission counts as submitted rather than a draft.
    And the following "activities" exist:
      | activity | course | idnumber | name                          | intro | assignsubmission_onlinetext_enabled | submissiondrafts | advancedgradingmethod_submissions |
      | assign   | C1     | A1       | Assignment needing grading    | TA1   | 1                                   | 0                | rubric                            |
      | assign   | C1     | A2       | Assignment already up to date | TA2   | 1                                   | 0                | rubric                            |
    # Only the first assignment has a submission, so only it requires grading.
    And the following "mod_assign > submissions" exist:
      | assign | user     | onlinetext            |
      | A1     | student1 | Here is my submission |

  Scenario: Saving a comment keeps the activities requiring grading filter
    Given I am on the "C1" "local_joulegrader > Open Grader" page logged in as "teacher1"
    # Both activities are listed until the filter is applied.
    Then the "garea" select box should contain "Assignment needing grading"
    And the "garea" select box should contain "Assignment already up to date"
    When I press "Show Activities Requiring Grading"
    # The filter is on: the button now offers the way back, and the up to date activity is gone.
    Then I should see "Show All Activities"
    And the "garea" select box should contain "Assignment needing grading"
    And the "garea" select box should not contain "Assignment already up to date"
    When I set the field "Comment" to "Please take another look at question 2"
    And I press "Save comment"
    Then I should see "Please take another look at question 2" in the ".local_joulegrader_commentloop_comments" "css_element"
    # The filter must still be on, rather than the teacher being dropped back on the unfiltered view.
    And I should see "Show All Activities"
    And I should not see "Show Activities Requiring Grading"
    And the "garea" select box should contain "Assignment needing grading"
    And the "garea" select box should not contain "Assignment already up to date"
