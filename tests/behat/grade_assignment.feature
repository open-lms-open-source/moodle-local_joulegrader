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
# Open Grader grade student assignment feature
#
# @package   local_joulegrader
# @copyright Copyright (c) 2018 Open LMS (https://www.openlms.net)
# @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_joulegrader
Feature: Grade assignments in Open Grader
  In order to save time
  As a teacher
  I need to be able to grade assignments in Open Grader

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
      | student2 | Student | 2 | student2@example.com |
      | student3 | Student | 3 | student3@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "activities" exist:
      | activity | course | idnumber | name                   | intro | advancedgradingmethod_submissions |assignfeedback_comments_enabled |
      | assign   | C1     | A1       | Test assignment 1 name | TA1   | rubric                            |1							   |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student        |
      | student2 | C1 | student        |
      | student3 | C1 | student        |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Test assignment 1 name" "link"
    And I go to "Test assignment 1 name" advanced grading definition page
    And I set the following fields to these values:
      | Name | Assignment 1 rubric |
      | Description | Assignment 1 description |
    And I define the following rubric:
      | Criterion 1 | Level 11 | 11 | Level 12 | 12 |
      | Criterion 2 | Level 21 | 21 | Level 22 | 22 |
    And I press "Save rubric and make it ready"

  @javascript
  Scenario: Teacher grades a student's assignment
    Given I am on "Course 1" course homepage
    Then I click on ".secondary-navigation li[data-region='openlmsmenu']" "css_element"
    And I follow "Open Grader"
    And I wait until the page is ready
    And I select "Student 1" from the "guser" singleselect
    And I click on "Grade with rubric" "button"
    And I grade by filling the Open Grader rubric with:
      | Criterion 1 | 12 | Very good |
      | Criterion 2 | 22 | Mmmm, you can do it better |
   	And "#id_assignfeedbackcomments_editoreditable" "css_element" should exist in the "#fitem_id_assignfeedbackcomments_editor" "css_element"
   	And ".atto_recordrtc_button_audio" "css_element" should exist in the ".atto_group.files_group" "css_element"
   	And ".atto_recordrtc_button_video" "css_element" should exist in the ".atto_group.files_group" "css_element"
    And I click on "Save grade" "button"
    Then I should see "Grade successfully updated"

  @javascript @_file_upload
  Scenario: Teacher has the same amount of students needing grade, both in the assignment activity and in Open Grader
    Given I log out
    # Create an assignment specific for this test
    And the following "activity" exists:
      | activity                           | assign                 |
      | course                             | C1                     |
      | idnumber                           | A2                     |
      | name                               | Test assignment 2 name |
      | intro                              | TA2                    |
      | grade[modgrade_type]               | point                  |
      | grade[modgrade_point]              | 100                    |
      | advancedgradingmethod_submissions  | rubric                 |
      | assignfeedback_comments_enabled    | 1                      |
      | assignsubmission_file_enabled      | 1                      |
      | assignsubmission_file_maxfiles     | 1                      |
      | assignsubmission_file_maxsizebytes | 1000000                |
    # Add 2 student submissions, both of which need grading
    And I am on the "Test assignment 2 name" "assign activity" page logged in as "student1"
    And I press "Add submission"
    And I upload "lib/tests/fixtures/empty.txt" file to "File submissions" filemanager
    And I press "Save changes"
    And I press "Submit assignment"
    And I press "Continue"
    And I log out
    And I am on the "Test assignment 2 name" "assign activity" page logged in as "student2"
    And I press "Add submission"
    And I upload "lib/tests/fixtures/empty.txt" file to "File submissions" filemanager
    And I press "Save changes"
    And I press "Submit assignment"
    And I press "Continue"
    And I log out
    And I am on the "Test assignment 2 name" "assign activity" page logged in as "teacher1"
    And I follow "View all submissions"
    And I click on "Grade" "link" in the "Student 1" "table_row"
    # Add a feedback comment, but don't grade the submission
    And I set the field "Feedback comments" to "Ungraded submission Student 1"
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I follow "Test assignment 2 name"
    # The 2 student submissions need grading when seen in the assignment
    Then I should see "2" in the "Needs grading" "table_row"
    And I am on "Course 1" course homepage
    Then I click on ".secondary-navigation li[data-region='openlmsmenu']" "css_element"
    And I follow "Open Grader"
    And I press "Show Activities Requiring Grading"
    And I click on "garea" "select"
    And I click on "Test assignment 2 name" "option"
    # The 2 student submissions need grading when seen in Open Grader
    Then I should see "Student 1"
    Then I should see "Student 2"
