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
# @copyright  Copyright (c) 2017 Open LMS
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_joulegrader @_file_upload
Feature: Teachers see the plagiarism plugin info in Joule Grader.
  Background:
    Given the following config values are set as admin:
      | enableplagiarism | 1 |
    And the following "courses" exist:
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
      | activity | course | idnumber | name             | intro                         | section | advancedgradingmethod_submissions | assignsubmission_file_enabled | assignsubmission_file_maxfiles | assignsubmission_file_maxsizebytes | duedate     |
      | assign   | C1     | assign1  | Test assignment1 | Test assignment description 1 | 1       | rubric                            | 1                             | 1                              | 1000000                            |  1388534400 |

  @javascript @testing
  Scenario: The teacher sees the plagiarism plugin info in Joule Grader.
    Given I skip because "I will be reviewed on INT-20670"
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment1"
    When I press "Add submission"
    And I upload "lib/tests/fixtures/empty.txt" file to "File submissions" filemanager
    And I press "Save changes"
    Then I press "Submit assignment"
    Then I press "Continue"
    And I should see "Submitted for grading"
    Then I log out
    Then I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Test assignment1"
    When I press "Add submission"
    And I upload "lib/tests/fixtures/empty.txt" file to "File submissions" filemanager
    And I press "Save changes"
    Then I press "Submit assignment"
    Then I press "Continue"
    And I should see "Submitted for grading"
    And I log out
    Given I log in as "teacher1"
    And I am on the course with shortname "C1"
    Then I click on ".secondary-navigation li[data-region='openlmsmenu']" "css_element"
    And I follow "Open Grader"
    And I wait until the page is ready
    Then I should see "Plagiarism plugin info placeholder"
    And I press "Show Activities Requiring Grading"
    Then I should see "Plagiarism plugin info placeholder"
    Then I press "Return to course"
    And I log out
    Given the following config values are set as admin:
      | theme | snap |
    And I log in as "teacher1"
    And I am on the course with shortname "C1"
    And I click on "#admin-menu-trigger" "css_element"
    And I click on "//p/a[contains(text(),'Open Grader')]" "xpath_element"
    And I wait until the page is ready
    Then I should see "Plagiarism plugin info placeholder"
    And I press "Show Activities Requiring Grading"
    Then I should see "Plagiarism plugin info placeholder"
    Then I press "Return to course"
    And I click on "#snap-pm-trigger" "css_element"
    And I click on "#snap-pm-logout" "css_element"
    And I log in as "teacher1"
    And I am on the course with shortname "C1"
    And I follow "Topic 1"
    And I click on ".snap-asset-link a" "css_element"
    Then I should see "Needs grading"
    And I click on "#admin-menu-trigger" "css_element"
    And I expand "Course administration" node
    And I click on "//p/a[contains(text(),'Open Grader')]" "xpath_element"
    And I wait until the page is ready
    Then I should see "Plagiarism plugin info placeholder"
    And I press "Show Activities Requiring Grading"
    Then I should see "Plagiarism plugin info placeholder"

  @javascript @testing
  Scenario: If plagiarism is not enabled, the plagiarism plugin info should not be visible
    Given the following config values are set as admin:
      | enableplagiarism | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test assignment1"
    When I press "Add submission"
    And I upload "lib/tests/fixtures/empty.txt" file to "File submissions" filemanager
    And I press "Save changes"
    Then I press "Submit assignment"
    Then I press "Continue"
    And I should see "Submitted for grading"
    Then I log out
    Then I log in as "teacher1"
    And I am on the course with shortname "C1"
    Then I click on ".secondary-navigation li[data-region='openlmsmenu']" "css_element"
    And I follow "Open Grader"
    And I wait until the page is ready
    Then I should not see "Plagiarism plugin info placeholder"
    And I press "Show Activities Requiring Grading"
    Then I should not see "Plagiarism plugin info placeholder"
