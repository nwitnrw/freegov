@api @drupal @entities
Feature: deGov - Content type form

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_node_normal_page |
      | pathauto               |
      | degov_pathauto         |

  Scenario: Check if all vertical tabs and advanced widgets are available
    Given I am logged in as a user with the "administrator" role
    Then I am on "/node/add/normal_page"
    And I should see HTML content matching "Title" via translated text
    And I should see HTML content matching "Preview" via translated text
    And I should see HTML content matching "General" via translated text
    And I should see HTML content matching "Header"
    And I should see HTML content matching "Seitenleiste rechts"
    And I should see HTML content matching "Content"
    And I should see HTML content matching "Vorschau Titel"
    And I should see HTML content matching "Vorschau Text"
    And I should see HTML content matching "Text format" via translated text
    And I should see text matching "VORSCHAU BILD"
    And I should see HTML content matching "Vorschau Untertitel"
    And I should see HTML content matching "Save as" via translated text
    And I should see HTML content matching "Last saved" via translated text
    And I should see HTML content matching "Revision log message" via translated text
    And I should see HTML content matching "Menu settings" via translated text
    And I should see HTML content matching "URL alias" via translated text
    And I should see HTML content matching "Authoring information" via translated text
    And I should see HTML content matching "Promotion options" via translated text

  Scenario: Add content to normal page
    Given I am logged in as a user with the "administrator" role
    Then I am on "/node/add/normal_page"
    And I fill in "title[0][value]" with "aabbcc"
    And I fill in "field_teaser_title[0][value]" with "preview__aaabbcc"
    And I fill in Textarea with "TEST TEST TEST"
    And I choose "General" via translation from tab menu
    Then I should see text matching "Schlagworte"
    And I choose "Header" via translation from tab menu
    And I choose "Seitenleiste rechts" from tab menu
    And I choose "Content" via translation from tab menu
    And I click on togglebutton
    Then I should see text matching "URL alias" via translated text in uppercase after a while
    And I select "URL alias" via translation in uppercase from rightpane
    Then I should see text matching "Generate automatic URL alias" via translated text after a while
    And I fill in "path[0][alias]" with "/aabbcc"
    And I select "Published" in "edit-moderation-state-0-state" via translated text
    And I press the "edit-submit" button
    Then I should be on "/aabbcc"
