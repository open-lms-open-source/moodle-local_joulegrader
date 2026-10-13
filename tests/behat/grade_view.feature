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
# Open Grader pane width feature
#
# @package   local_joulegrader
# @copyright Copyright (c) 2026 Open LMS (https://www.openlms.net)
# @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@local @local_joulegrader
Feature: Open Grader pane width in Boost
  In order to have enough room to grade
  As a teacher
  I need the grade pane to take half of the Open Grader width in Boost

  Scenario: Grade pane spans half the panes container in Boost
    Given the following config values are set as admin:
      | theme | boost |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "activities" exist:
      | activity | course | name         | advancedgradingmethod_submissions |
      | assign   | C1     | Assignment 1 |                                   |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    When I am on the "C1" "local_joulegrader > Open Grader" page logged in as "teacher1"
    Then the "class" attribute of "#local-joulegrader-gradepane" "css_element" should contain "yui3-u-1-2"
