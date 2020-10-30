@api @drupal @entities
Feature: deGov - Views

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content          |
    Given I proof that the following Drupal modules are installed:
      | degov_node_event            |

  Scenario: I verify that the latest events view only displays current events
    Given I have dismissed the cookie banner if necessary
    And I am on "/degov-demo-content/page-events-views-reference"
    Then I should see 4 ".view-latest-events .event" elements
    And I should see text matching "An event in the future" after a while
    And I should see text matching "Another event in the future" after a while
    And I should see text matching "This is a future event" after a while
    And I should see text matching "Here goes a future event" after a while
    And I should not see text matching "An event in the past" after a while
