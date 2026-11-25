@report @report_extdeps
Feature: See the external dependencies of the site and of a course
  In order to fix broken videos and packages
  As a manager or a teacher
  I need to see where each external dependency is used and how to edit it

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | manager1 | Manager   | One      | manager1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "role assigns" exist:
      | user     | role    | contextlevel | reference |
      | manager1 | manager | System       |           |
    And the following "activities" exist:
      | activity | course | name         | content                                                          |
      | page     | C1     | Video page   | <iframe src="https://www.youtube.com/embed/AAAAAAAAAAA"></iframe> |
      | page     | C2     | Other page   | <iframe src="https://player.vimeo.com/video/123456789"></iframe>  |
    And I run the scheduled task "\tool_extdeps\task\scan_dirty_courses"

  Scenario: The manager sees every course and filters the occurrences
    Given I log in as "manager1"
    When I navigate to "Reports > External dependencies" in site administration
    Then I should see "Course 1" in the "reportbuilder-table" "table"
    And I should see "Course 2" in the "reportbuilder-table" "table"
    And I click on "All occurrences" "link"
    # Only the broken ones by default; nothing was checked yet in this site.
    And I click on "Show the available ones too" "link"
    And I should see "Video page" in the "reportbuilder-table" "table"
    And I should see "Other page" in the "reportbuilder-table" "table"
    And I click on "Filters" "button"
    And I set the following fields in the "Provider" "core_reportbuilder > Filter" to these values:
      | Provider operator | Is equal to |
      | Provider value    | YouTube     |
    And I click on "Apply" "button" in the "[data-region='report-filters']" "css_element"
    And I should see "Video page" in the "reportbuilder-table" "table"
    And I should not see "Other page" in the "reportbuilder-table" "table"

  Scenario: The teacher sees only the own course, with the link to edit
    Given I log in as "teacher1"
    When I am on the "Course 1" "course" page
    And I navigate to "Reports > External dependencies" in current page administration
    And I click on "Show the available ones too" "link"
    Then I should see "Video page" in the "reportbuilder-table" "table"
    And I should not see "Other page"
    And "Edit" "link" should exist in the "Video page" "table_row"
    And I should see "Check this course now"
