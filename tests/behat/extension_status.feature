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
# Tests commenting in Joule Grader
#
# @package    local_joulegrader
# @copyright  Copyright (c) 2016 Moodlerooms Inc. (http://www.moodlerooms.com)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_joulegrader
Feature: Teachers see the correct extension and late status.
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "activities" exist:
      | activity | course | idnumber | name             | intro                         | section | advancedgradingmethod_submissions | assignsubmission_onlinetext_enabled | duedate    |
      | assign   | C1     | assign1  | Test assignment1 | Test assignment description 1 | 1       | rubric                            | 1                                   | 1388534400 |

  @javascript @testing
  Scenario: The teacher sees the correct extension status when grading
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment1"
    And I navigate to "View all submissions" in current page administration
    And I click on "Edit" "link" in the "Student 1" "table_row"
    And I follow "Grant extension"
    And I set the field "Enable" to "1"
    And I set the field "extensionduedate[year]" to "2050"
    And I press "Save changes"
    Then I should see "Extension granted until:" in the "Student 1" "table_row"
    And I am on "Course 1" course homepage
    And I navigate to "Open Grader" in current page administration
    And I wait until the page is ready
    And I select "Student 1" from the "guser" singleselect
    Then I should see "Extension granted until:"
    And I select "Student 2" from the "guser" singleselect
    Then I should not see "Extension granted until:"
    And I should see "Nothing to Display"
    And I press "Exit full screen mode"
    When I log out 
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment1"
    And I press "Add submission"
    And I set the following fields to these values:
      | Online text | I'm the student1 submission |
    And I press "Save changes"
    And I log out 
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test assignment1"
    And I press "Add submission"
    And I set the following fields to these values:
      | Online text | I'm the student2 submission |
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Open Grader" in current page administration
    And I wait until the page is ready
    And I select "Student 1" from the "guser" singleselect
    Then I should see "Extension granted until:"
    And I should not see "This submission was late by"
    And I select "Student 2" from the "guser" singleselect
    Then I should not see "Extension granted until:"
    And I should see "This submission was late by"
