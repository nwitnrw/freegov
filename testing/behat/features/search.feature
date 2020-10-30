@api @drupal @form
Feature: deGov - Search

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_search_content       |
      | degov_search_media         |

  # We do this here instead of Background, because constantly rebuilding the demo content caused a massive overhead
  Scenario: I set up the required modules
    And I am installing the following Drupal modules:
      | degov_search_media_manager |
      | degov_demo_content         |
    And I reset the demo content
    And I rebuild the "search_content" index
    And I rebuild the "search_media" index

  Scenario: Verify that search is configured for partial word matching
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/config/search/search-api/server/database/edit"
    Then I should see the input with the name "backend_config[matching]" and the value "partial" checked

  Scenario: I verify that the node content type filter shows labels, not machine names
    Then I am logged in as a user with the "administrator" role
    And I have dismissed the cookie banner if necessary
    And I rebuild the "search_content" index
    And I clear the cache
    Then I am on "/suche"
    And I should see an ".facet-item__value" element with the content "Inhaltsseite"

  Scenario: Check if media search is working properly
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I open media edit form by media name "Demo image with a fixed title"
    And I choose "Allgemein" from tab menu
    And I scroll to element with id "edit-field-include-search-value"
    And I check the box "edit-field-include-search-value"
    And I scroll to element with id "edit-submit"
    And I press "Speichern"
    And I am on "/mediathek"
    Then I should see HTML content matching "Demo image with a fixed title" after a while
    And I open media edit form by media name "Demo image with a fixed title"
    And I choose "Allgemein" from tab menu
    And I scroll to element with id "edit-field-include-search-value"
    And I uncheck the box "edit-field-include-search-value"
    And I scroll to element with id "edit-submit"
    And I press "Speichern"
    And I am on "/mediathek"
    And I should not see "Demo image with a fixed title"

  Scenario: I verify that the media bundle filter shows labels, not machine names
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I open media edit form by media name "Demo image with a fixed title"
    And I choose "Allgemein" from tab menu
    And I scroll to element with id "edit-field-include-search-value"
    And I check the box "edit-field-include-search-value"
    And I scroll to element with id "edit-submit"
    And I press "Speichern"
    And I open media edit form by media name "A video upload with sound"
    And I choose "Allgemein" from tab menu
    And I scroll to element with id "edit-field-include-search-value"
    And I check the box "edit-field-include-search-value"
    And I scroll to element with id "edit-submit"
    And I press "Speichern"
    And I rebuild the "search_media" index
    And I clear the cache
    Then I am on "/mediathek"
    And I should see HTML content matching "Video Upload" after a while
    And I should see HTML content matching "Bild" after a while
    And I should see an ".facet-item__value" element with the content "Bild"
    And I should see an ".facet-item__value" element with the content "Video Upload"

  Scenario: I verify that no links are shown on search preview
    Given I have dismissed the cookie banner if necessary
    Given I proof that the following Drupal modules are installed:
      | degov_node_normal_page      |
    And I execute the following console command: "drush pm:uninstall degov_demo_content -y"
    And I am logged in as a user with the "administrator" role
    And I am on "/node/add/normal_page"
    And I should see "Titel"
    And I should see "Vorschau Text"
    And I fill in "Titel" with "Test1234"
    And I put 'Text with a link to the <a href="https://drupal.org">Homepage of Drupal</a>.' into CKEditor
    And I select "Veröffentlicht" by name "moderation_state[0][state]"
    And I scroll to bottom
    And I press button with label "Save" via translated text
    And I should see text matching "Test1234"
    And I should see text matching "Text with a link to the Homepage of Drupal."
    And I should see HTML content matching 'Text with a link to the <a href="https://drupal.org">Homepage of Drupal</a>.'
    And I rebuild the "search_content" index
    Then I am on "/suche"
    And I should see text matching "Test1234"
    And I should see text matching "Text with a link to the Homepage of Drupal."
    And I should not see HTML content matching 'Text with a link to the <a href="https://drupal.org">Homepage of Drupal</a>.'
