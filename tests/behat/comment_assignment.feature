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
# @copyright  Copyright (c) 2015 Open LMS (https://www.openlms.net)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_joulegrader
Feature: Teachers and students can comment on a student's assignment.
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
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
      | activity | course | idnumber | name             | intro                         | section | advancedgradingmethod_submissions |
      | assign   | C1     | assign1  | Test assignment1 | Test assignment description 1 | 1       | rubric                            |

  @javascript
  Scenario: A student can comment on their own assignment:
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    Then I click on ".secondary-navigation li[data-region='openlmsmenu']" "css_element"
    And I follow "Open Grader"
    And I wait until the page is ready
    And I set editable div ".local_joulegrader_commentloop .editor_atto_content" "css_element" to "Here is a comment on my paper"
    And I press "Save comment"
    Then I should see "Here is a comment on my paper" in the ".local_joulegrader_commentloop_comments" "css_element"
    And I should not see "Here is a comment on my paper" in the ".local_joulegrader_commentloop .editor_atto_content" "css_element"

  @javascript
  Scenario: A teacher can comment on a student's assignment
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then I click on ".secondary-navigation li[data-region='openlmsmenu']" "css_element"
    And I follow "Open Grader"
    And I wait until the page is ready
    And I select "Student 1" from the "guser" singleselect
    And I set editable div ".local_joulegrader_commentloop .editor_atto_content" "css_element" to "Good job on your paper"
    And I press "Save comment"
    Then I should see "Good job on your paper" in the ".local_joulegrader_commentloop_comments" "css_element"
    And I should not see "Good job on your paper" in the ".local_joulegrader_commentloop .editor_atto_content" "css_element"
    And I should see "Good job on your paper" in the ".local_joulegrader_comment_content .text_to_html" "css_element"
    And I click on ".local_joulegrader_comment_delete .action-icon" "css_element"
    And I should not see "Good job on your paper"