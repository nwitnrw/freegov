@api @drupal @content @search_priority_keywords
Feature: deGov - Search priority keywords

  Background:
    Given I am installing the "degov_search_priority_keywords" module

  Scenario: Search priority keywords
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am at "/node/add/normal_page"
    And I fill in "Titel" with "SearchPriorityKeywords A"
    And I fill in "Vorschau Titel" with "SearchPriorityKeywords A"
    And I select "published" by name "moderation_state[0][state]"
    And I press button with label "Save" via translated text
    Then I am at "/node/add/normal_page"
    And I fill in "Titel" with "SearchPriorityKeywords B"
    And I fill in "Vorschau Titel" with "SearchPriorityKeywords B"
    And I choose "General" via translation from tab menu
    And I fill in "field_preferred_search_terms[0][value]" with "SearchPriorityKeywords"
    And I select "published" by name "moderation_state[0][state]"
    And I press button with label "Save" via translated text
    And I clear all search indexes and index all search indexes
    When I am at "/suche?volltext=SearchPriorityKeywords"
    Then I should see text matching "SearchPriorityKeywords B" in "xpath" selector "(//div[contains(@class, 'view-search-content')]//article)[1]"
