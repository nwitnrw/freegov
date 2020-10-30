@api @drupal @javascript
Feature: deGov - Toolbar

  Scenario: Verify that clicking on a toolbar toggle does not open a URL
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on the homepage
    Then I click "Manage" via translation
    And I should be on the homepage

  Scenario: Verify that the toolbar is sticky
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on the homepage
    And I scroll to bottom
    Then element ".toolbar-bar" has the style attribute "position" with value "fixed"
    And element ".toolbar-bar" has the style attribute "top" with value "0px"
