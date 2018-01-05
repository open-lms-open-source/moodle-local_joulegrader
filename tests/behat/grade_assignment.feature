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
# Moodlerooms Grader grade student assignment feature
#
# @package   local_joulegrader
# @copyright Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
# @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


@local @local_joulegrader
Feature: Grade assignments in Moodlerooms Grader
  In order to save time
  As a teacher
  I need to be able to grade assignments in Moodlerooms Grader

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "activities" exist:
      | activity | course | idnumber | name                   | intro | advancedgradingmethod_submissions |
      | assign   | C1     | A1       | Test assignment 1 name | TA1   | rubric                            |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student        |
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
    And I navigate to "Moodlerooms Grader" in current page administration
    And I wait until the page is ready
    And I select "Student 1" from the "guser" singleselect
    And I click on "Grade with rubric" "button"
    And I grade by filling the Moodlerooms Grader rubric with:
      | Criterion 1 | 12 | Very good |
      | Criterion 2 | 22 | Mmmm, you can do it better |
    And I click on "Save grade" "button"
    Then I should see "Grade successfully updated"