@api @drupal
Feature: deGov - Development

  Background:
    Given I am installing the following Drupal modules:
      | degov_devel |
    Given I have dismissed the cookie banner if necessary
    Given I am logged in as a user with the "administrator" role

  Scenario: Check that the webprofiler is visible
    And I proof that Drupal module "degov_devel" is installed
    Then I am on the homepage
    And I should see 1 ".sf-toolbar" elements
    Then I uninstall the "devel" module

  Scenario: DevMode Form testing
    And I am on "/admin/config/development/dev-mode"
    And I should see text matching "Development settings" via translated text
    And the "edit-dev-mode" checkbox should be checked
    Then I uncheck checkbox with id "edit-dev-mode"
    And I press button with label "Save configuration" via translated text
    And I wait 5 seconds
    And the "edit-dev-mode" checkbox should be unchecked
    Then I check checkbox with id "edit-dev-mode"
    And I press button with label "Save configuration" via translated text
