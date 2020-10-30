@api @drupal @entities
Feature: deGov - Multilingual

  Scenario: I am on paragraphs type page and homepage breadcrumb is translated
    Given I am logged in as a user with the "administrator" role
    Then I am installing the "degov_breadcrumb" module
    And I proof that Drupal module "degov_breadcrumb" is installed
    And I am on "/admin/structure/paragraphs_type"
    And I should see text matching "Startseite"
    Then I am on "/admin/config/regional/language/add"
    And I select "en" in "edit-predefined-langcode"
    And I press button with label "Add language" via translated text
    Then I should see text matching "Add language" via translation after a while
    And I am on "/en/admin/structure/paragraphs_type"
    And I should see text matching "Home"
    And I am on "/admin/config/regional/language/delete/en"
    And I press button with label "Delete" via translated text
    And I should see text matching "Die Sprache English (en) wurde entfernt." after a while

  Scenario: I verify custom translation can not be overriden by contrib translation.
    Given I am logged in as a user with the "administrator" role
    Then I am on "/admin/reports/status"
    And I should see text matching "Statusbericht"
    Then I am installing the "degov_custom_translation_test" module
    And I proof that Drupal module "degov_custom_translation_test" is installed
    And I clear the cache
    And I reload the page
    And I should see text matching "Statusbericht CUSTOM DE"
    And I should see text matching "Speicherbegrenzung"
    Then I am installing the "degov_contrib_translation_test" module
    And I proof that Drupal module "degov_contrib_translation_test" is installed
    And I clear the cache
    And I reload the page
    And I should see text matching "Statusbericht CUSTOM DE"
    And I should not see text matching "Statusbericht CONTRIB DE"
    And I should see text matching "Speicherbegrenzung CONTRIB DE"
