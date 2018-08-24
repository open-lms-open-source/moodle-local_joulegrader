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
 * Open Grader Behat step definitions.
 *
 * Copied and modified from behat_gradingform_rubric class.
 *
 * @package    local_joulegrader
 * @author     Sam Chaffee
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Open Grader Behat step definitions.
 *
 * Copied and modified from behat_gradingform_rubric class.
 *
 * @package    local_joulegrader
 * @copyright  Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_joulegrader extends behat_base {

    /**
     * Grades filling the current page rubric. Set one line per criterion and for each criterion set "| Criterion name | Points | Remark |".
     *
     * @When /^I grade by filling the Open Grader rubric with:$/
     *
     * @param TableNode $rubric
     * @throws Exception
     * @throws ExpectationException
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function i_grade_by_filling_the_open_grader_rubric_with(TableNode $rubric) {
        $criteria = $rubric->getRowsHash();

        $stepusage = '"I grade by filling the rubric with:" step needs you to provide a table where each row is a criterion' .
            ' and each criterion has 3 different values: | Criterion name | Number of points | Remark text |';

        // First element -> name, second -> points, third -> Remark.
        foreach ($criteria as $name => $criterion) {

            // We only expect the points and the remark, as the criterion name is $name.
            if (count($criterion) !== 2) {
                throw new ExpectationException($stepusage, $this->getSession());
            }

            // Numeric value here.
            $points = $criterion[0];
            if (!is_numeric($points)) {
                throw new ExpectationException($stepusage, $this->getSession());
            }

            // Selecting a value.
            // When JS is disabled there are radio options, with JS enabled divs.
            $selectedlevelxpath = $this->get_level_xpath($points);
            if ($this->running_javascript()) {

                // Only clicking on the selected level if it was not already selected.
                $levelnode = $this->find('xpath', $selectedlevelxpath);

                // Using in_array() as there are only a few elements.
                if (!$levelnode->hasClass('checked')) {
                    $levelnodexpath = $selectedlevelxpath . "//div[contains(concat(' ', normalize-space(@class), ' '), ' score ')]";
                    $this->execute('behat_general::i_click_on_in_the',
                        array($levelnodexpath, "xpath_element", $this->escape($name), "table_row")
                    );
                }

            } else {

                // Getting the name of the field.
                $radioxpath = $this->get_criterion_xpath($name) .
                    $selectedlevelxpath . "/descendant::input[@type='radio']";
                $radionode = $this->find('xpath', $radioxpath);
                // which will delegate the process to the field type.
                $radionode->setValue($radionode->getAttribute('value'));
            }

            // Setting the remark.

            // First we need to get the textarea name, then we can set the value.
            $textarea = $this->get_node_in_container('css_element', 'textarea', 'table_row', $name);
            $this->execute('behat_forms::i_set_the_field_to', array($textarea->getAttribute('name'), $criterion[1]));
        }
    }

    /**
     * Returns the xpath representing a selected level.
     *
     * It is not including the path to the criterion.
     *
     * It is the xpath when grading a rubric or viewing a rubric,
     * it is not the same xpath when editing a rubric.
     *
     * @param int $points
     * @return string
     */
    protected function get_level_xpath($points) {
        return "//td[contains(concat(' ', normalize-space(@class), ' '), ' level ')]" .
            "[./descendant::span[@class='scorevalue'][text()='$points']]";
    }

    /**
     * Returns the xpath representing the selected criterion.
     *
     * It is the xpath when grading a rubric or viewing a rubric,
     * it is not the same xpath when editing a rubric.
     *
     * @param string $criterionname Literal including the criterion name.
     * @return string
     */
    protected function get_criterion_xpath($criterionname) {
        $literal = behat_context_helper::escape($criterionname);
        return "//tr[contains(concat(' ', normalize-space(@class), ' '), ' criterion ')]" .
            "[./descendant::td[@class='description'][text()=$literal]]";
    }
}
