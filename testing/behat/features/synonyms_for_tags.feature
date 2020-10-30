@api @drupal @content @synonyms_for_tags
Feature: deGov - Synonyms for tags

  Background:
    Given I am installing the "degov_taxonomy_term_synonyms" module
    And I am installing the "degov_search_synonyms" module
    And I am installing the "degov_demo_content" module
    And I have dismissed the cookie banner if necessary

  Scenario: Confirm that the synonym field exists.
    Given I am logged in as a user with the "administrator" role
    And I am at "admin/structure/taxonomy/manage/tags/add"
    Then I should see HTML content matching "field_synonyms[0][target_id]"

  Scenario: Add tags to content by synonym.
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/structure/taxonomy/manage/synonyms/add"
    And I fill in "Name" via translated text with "Synonyms for tags test synonym"
    And I submit the form
    Then I am at "/admin/structure/taxonomy/manage/tags/add"
    And I fill in "Name" via translated text with "Synonyms for tags test tag"
    And I fill in the autocomplete 'input[name="field_synonyms[0][target_id]"]' with "Synonyms for tags test synonym" via javascript
    And I scroll to bottom
    And I press button with label "Save" via translated text
    Then I am at "/node/add/normal_page"
    And I fill in "Titel" with "Synonyms for tags test page"
    And I fill in the autocomplete 'input[name="field_tags[0][target_id]"]' with "Synonyms for tags test synonym" via javascript
    And I wait for AJAX to finish
    And I scroll to bottom
    And I press button with label "Save" via translated text
    And I open node edit form by node title "Synonyms for tags test page"
    And I choose "General" via translation from tab menu
    Then I verify that field value of 'input[name="field_tags[0][target_id]"]' matches "Synonyms for tags test tag"
    And I should see text matching "Synonyms for tags test synonym" in "css" selector "#field-tags-tag-synonym-wrapper"

  Scenario: Search by tag in content overview.
    Given I am logged in as a user with the "administrator" role
    And I am at "/admin/content"
    And I fill in the autocomplete 'input[name="field_tags_target_id"]' with "degov_demo_content" via javascript
    And I press button with label "Filter" via translated text
    Then I should not see text matching "No content available." via translated text

  Scenario: Confirm I can search by synonyms
    Given I am logged in as a user with the "administrator" role
    And I am at "/node/add/normal_page"
    And I fill in "Titel" with "SynonymsForTagsTestPageWithUniqueName"
    And I fill in "Vorschau Titel" with "SynonymsForTagsTestPageWithUniqueName"
    And I select "published" by name "moderation_state[0][state]"
    And I press button with label "Save" via translated text
    And I clear all search indexes and index all search indexes
    When I am at "/suche?volltext=SynonymsForTagsTestPageWithUniqueName+degov_demo_content_synonym"
    Then I should not see text matching "SynonymsForTagsTestPageWithUniqueName" via translated text in "css" selector "article"
    And I open node edit form by node title "SynonymsForTagsTestPageWithUniqueName"
    And I fill in the autocomplete 'input[name="field_tags[0][target_id]"]' with "degov_demo_content" via javascript
    And I wait for AJAX to finish
    And I press button with label "Save" via translated text
    And I clear all search indexes and index all search indexes
    When I am at "/suche?volltext=SynonymsForTagsTestPageWithUniqueName+degov_demo_content_synonym"
    Then I should see text matching "SynonymsForTagsTestPageWithUniqueName" in "css" selector "article"

  Scenario: Ignore synonyms when searching, results for tag
    Given I am logged in as a user with the "administrator" role
    And I have dismissed the cookie banner if necessary
    And I am at "/admin/structure/taxonomy/manage/synonyms/add"
    And I fill in "Name" via translated text with "SynonymsForTagsTestSynonym"
    And I submit the form
    Then I am at "/admin/structure/taxonomy/manage/tags/add"
    And I fill in "Name" via translated text with "SynonymsForTagsTestTag"
    And I fill in the autocomplete 'input[name="field_synonyms[0][target_id]"]' with "SynonymsForTagsTestSynonym" via javascript
    And I scroll to bottom
    And I press button with label "Save" via translated text
    Then I am at "/node/add/normal_page"
    And I fill in "Titel" with "SynonymAddedAsTag"
    And I fill in "Vorschau Titel" with "SynonymAddedAsTag"
    And I fill in the autocomplete 'input[name="field_tags[0][target_id]"]' with "SynonymsForTagsTestSynonym" via javascript
    And I select "published" by name "moderation_state[0][state]"
    And I press button with label "Save" via translated text
    Then I am at "/node/add/normal_page"
    And I fill in "Titel" with "SynonymsForTagsTestSynonym"
    And I fill in "Vorschau Titel" with "SynonymsForTagsTestSynonym"
    And I select "published" by name "moderation_state[0][state]"
    And I press button with label "Save" via translated text
    And I wait 10 seconds
    When I am at "/suche?volltext=SynonymsForTagsTestSynonym"
#    Then I should see text matching "SynonymsForTagsTestSynonym" after a while
#    And I should see text matching "SynonymAddedAsTag" after a while
#    And I should see text matching "Ergebnisse für: SynonymsForTagsTestTag" via translated text
#    And I should see text matching "Stattdessen nach SynonymsForTagsTestSynonym suchen" via translated text
#    When I click by CSS class "js-search-instead-for"
#    And I should see text matching "SynonymsForTagsTestSynonym" after a while
#    And I should not see text matching "SynonymAddedAsTag" after a while
#    When I am at "/suche?volltext=SynonymsForTagsTestSynonym"
#    And I click by CSS class "js-search-for-tag"
#    Then I should not see text matching "SynonymsForTagsTestSynonym" after a while
#    And I should see text matching "SynonymAddedAsTag" after a while
