@api @drupal @performance @performance_guest
Feature: nrwGOV - Static pages performance with load duration measuring

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content    |
      | degov_behat_extension |

  Scenario: I visit all static pages and expect 90 percent of all pages loaded within a duration of 2 seconds
    Given I have dismissed the cookie banner if necessary
    And I am warming the cache of the static pages
    And I visit static pages and expect fulfillment of performance requirement
