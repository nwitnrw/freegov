@api @drupal @form
Feature: deGov - GovBot FAQ

  Background:
    Given I am installing the following Drupal modules:
      | degov_govbot_faq |
    And I proof that the following Drupal modules are installed:
      | degov_govbot_faq      |
      | degov_paragraph_faq   |
      | degov_node_faq        |
      | degov_search_base     |
      | degov_search_content  |
      | locale                |
      | search_api            |

  Scenario: Paragraph type FAQ contains necessary fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/paragraphs_type/faq/fields"
    Then I should see text matching "field_faq_text"
    And I should see text matching "field_faq_title"
    And I should see text matching "field_govbot_answer"
    And I should see text matching "field_govbot_question"
    And I should see text matching "field_govbot_id"

  Scenario: Search content search index contains nested_faq_paragraphs field
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/config/search/search-api/index/search_content/fields"
    Then I should see HTML content matching "nested_faq_paragraphs"
