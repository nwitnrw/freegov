@api @drupal @content
Feature: deGov - Content types

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_node_overrides       |
      | degov_node_normal_page     |
      | degov_paragraph_webform    |
      | degov_paragraph_slideshow  |
      | degov_paragraph_header     |
      | degov_node_external_teaser |
    Given I am installing the following Drupal modules:
      | degov_node_event |
    Given I proof content type "normal_page" has set the following fields for display:
      | field_teaser_title             |
      | field_teaser_text              |
      | content_moderation_control     |
      | field_header_paragraphs        |
      | field_sidebar_right_paragraphs |
      | title                          |

  Scenario: Content type normal_page has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/types/manage/normal_page/fields"
    Then I should see text matching "field_header_paragraphs"
    And I should see text matching "field_tags"
    And I should see text matching "field_sidebar_right_paragraphs"
    And I should see text matching "field_social_media"
    And I should see text matching "field_teaser_image"
    And I should see text matching "field_teaser_text"
    And I should see text matching "field_teaser_title"
    And I should see text matching "field_teaser_sub_title"

  Scenario: Content type event has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/types/manage/event/fields"
    Then I should see text matching "field_event_date_end"
    And I should see text matching "field_content_paragraphs"
    And I should see text matching "field_internal_title"
    And I should see text matching "field_header_paragraphs"
    And I should see text matching "field_tags"
    And I should see text matching "field_sidebar_right_paragraphs"
    And I should see text matching "field_social_media"
    And I should see text matching "field_teaser_image"
    And I should see text matching "field_teaser_text"
    And I should see text matching "field_teaser_title"
    And I should see text matching "field_teaser_sub_title"

  Scenario: Content type normal page references specified content types in field_content_paragraphs
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/normal_page"
    And I choose "Content" via translation from tab menu
    And I press the "edit-field-content-paragraphs-add-more-add-modal-form-area-add-more" button
    And I should see HTML content matching "FAQ" after a while
    And I should see HTML content matching "FAQ / Akkordion Liste"
    And I should see HTML content matching "Banner"
    And I should see HTML content matching "Inhaltsreferenz"
    And I should see HTML content matching "Slide"
    And I should see HTML content matching "Slideshow"
    And I should see HTML content matching "Inhaltsreferenz"
    And I should see HTML content matching "Text"
    And I should see HTML content matching "Untertitel"
    And I should see HTML content matching "Webform"

  Scenario: Admin Content page
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    And I see the button "Filter"
    And I set the window size to "960px" width and "900px" height
    And I scroll to element with id "views-form-content-page-1"
    And I click by selector "button.tableresponsive-toggle" via JavaScript
    And I should see text matching "Title" via translated text in uppercase
    And I should see text matching "Content type" via translated text in uppercase
    And I should see HTML content matching "Autor"
    And I should see text matching "Updated" via translated text in uppercase
    And I should see text matching "Operations" via translated text in uppercase
    And I should see "Interner Titel"
    And I should not see the text "Undefined index"

  Scenario: I have no mismatched entity definitions
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/reports/status"
    Then I should not see text matching "Mismatched entity and/or field definitions" via translated text

  Scenario: External Teaser field_link has only one url
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/types/manage/external_teaser/fields/node.external_teaser.field_link/storage"
    And I should see HTML content matching "Limited" via translated text
    Then I verify that field value of '#edit-cardinality-number' matches "1"

  Scenario: External teaser is redicected to destinnation url
    Given I am not logged in
    And I am on "/degov-demo-content/external-teaser"
    Then I am redirected to "https://publicplan.de"

