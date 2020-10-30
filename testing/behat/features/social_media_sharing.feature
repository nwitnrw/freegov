@api @drupal @javascript @social_media_sharing
Feature: deGov Social Media Sharing
  To ensure that user's privacy is protected
  As an administrator
  I check that we offer privacy-conscious sharing

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content                |

  Scenario: Confirm that "Social media settings" block is in place
    Given I am on homepage
    Then I have dismissed the cookie banner if necessary
    And I should see ".top-header-wrapper .navbar a.js-social-media-settings-open" element visible on the page
    Then I click by CSS class "js-social-media-settings-open"
    And I should see "div#social-media-settings" element visible on the page

  Scenario: I check that 2-click-sharing is enabled by default
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    Then I open node edit form by node title "Page with text paragraph"
    And I choose "General" via translation from tab menu
    Then I check checkbox with id "edit-field-social-media-value"
    And I click by CSS id "edit-submit"
    Then I should see HTML content matching "alert-status" after a while
    And I should see 2 "li.shariff-button" elements via JavaScript

  Scenario: I confirm the working functionality of the 2-click-sharing button
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/config/services/shariff"
    And I uncheck checkbox with id "edit-enable-1-click-sharing"
    And I click by CSS id "edit-submit"
    And I am on "/degov-demo-content/page-text-paragraph"
    Then I should see 1 "li.shariff-button:first-child .sharing-overlay" elements after a while
    And I click by selector "li.shariff-button:first-child .sharing-overlay" via JavaScript
    Then I should not see 1 "li.shariff-button:first-child .sharing-overlay" elements after a while
    And there should be a total of 1 window
    Then I click by selector "li.shariff-button:first-child a" via JavaScript
    And there should be a total of 2 windows

  Scenario: I check that I can disable 2-click-sharing
    Given I have dismissed the cookie banner if necessary
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/config/services/shariff"
    And I check checkbox with id "edit-enable-1-click-sharing"
    And I click by CSS id "edit-submit"
    Then I should see text matching "The configuration options have been saved." via translation after a while
    Then I open node edit form by node title "Page with text paragraph"
    And I choose "General" via translation from tab menu
    Then I check checkbox with id "edit-field-social-media-value"
    And I click by CSS id "edit-submit"
    Then I should see HTML content matching "alert-status" after a while
    And I should see 2 "li.shariff-button" elements via JavaScript

  Scenario: I see no error on updates page for degov_tweets
    Given I am logged in as a user with the "administrator" role
    Given I am on "/admin/reports/updates"
    And I should not see HTML content matching " Unbekannt"
