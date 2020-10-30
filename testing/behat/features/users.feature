@api @drupal @entities
Feature: deGov - Users

  Background:
    Given I proof that Drupal module "degov_users_roles" is installed
    Given I proof that Drupal module "degov_simplenews_references" is installed

  Scenario: I am on the frontpage
    Given I am on the homepage
    Then I should not see text matching "Error"
    Then I should not see text matching "Warning"

  Scenario: I am installing the degov user roles module
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    Then I am installing the "degov_users_roles" module
    Then I am on "/admin/people/roles"
    And I should see "Chefredakteur"
    And I should see "Redakteur"
    And I should see "Benutzerverwaltung"

  Scenario: I try to create a new user without an email but with a Simplenews subscription
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/people/create"
    And I fill in "Username" via translated text with "test"
    And I fill in "Password" via translated text with "Test!Passw0rd"
    And I fill in "Confirm password" via translated text with "Test!Passw0rd"
    And I press button with label "Create new account" via translated text
    Then I should be on "/admin/people/create"
    And I should see 1 ".messages--status" element

  Scenario: I prove that only editors, managers, and admins may access the media library
    Given I have dismissed the cookie banner if necessary
    Then I am on "/admin/content/media"
    And I should see text matching "You are not authorized to access this page." via translation after a while
    Then I am logged in as a user with the "usermanager" role
    And I am on "/admin/content/media"
    And I should see text matching "You are not authorized to access this page." via translation after a while
    Then I am logged in as a user with the "editor" role
    And I am on "/admin/content/media"
    And I should not see text matching "You are not authorized to access this page." via translated text
    And I am on "/node/add/normal_page"
    And I choose "Preview" via translation from tab menu
    And I click by CSS id "edit-field-teaser-image-entity-browser-entity-browser-open-modal"
    Then I should see text matching "Select entities" via translated text after a while
    And I should see HTML content matching '<iframe src="/entity-browser/modal/media_browser' after a while
    And I wait for AJAX to finish
    And I switch to the "entity_browser_iframe_media_browser" frame
    Then I should see text matching "Library" after a while
    And I go back to the main window
    And I press the "Close" button
    Then I am logged in as a user with the "manager" role
    And I am on "/admin/content/media"
    And I should not see text matching "You are not authorized to access this page." via translated text
    And I am on "/node/add/normal_page"
    And I choose "Preview" via translation from tab menu
    And I click by CSS id "edit-field-teaser-image-entity-browser-entity-browser-open-modal"
    Then I should see text matching "Select entities" via translated text after a while
    And I should see HTML content matching '<iframe src="/entity-browser/modal/media_browser' after a while
    And I wait for AJAX to finish
    And I switch to the "entity_browser_iframe_media_browser" frame
    Then I should see text matching "Library" after a while
    And I go back to the main window

  Scenario: I prove that I do not see the user profile page as a guest user
    Given I have dismissed the cookie banner if necessary
    And I am on "/user/1"
    And I should see text matching "You are not authorized to access this page." via translated text
