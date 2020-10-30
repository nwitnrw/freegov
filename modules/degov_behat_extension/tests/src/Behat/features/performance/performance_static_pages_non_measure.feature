@api @drupal @performance @performance_editor
Feature: nrwGOV - Static pages performance without measuring

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content    |
      | degov_behat_extension |

  Scenario: I visit all static pages
    Given I have dismissed the cookie banner if necessary
    And I am warming the cache of the static pages
    And I am logged in as a user with the "editor" role
    And I visit static pages
