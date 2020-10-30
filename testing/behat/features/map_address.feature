@api @drupal @entities
Feature: deGov - Display address in map popups

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_media_address |
      | styled_google_map   |

  Scenario: I prove the media address type contains the expected fields in the media creation form
    Given I have dismissed the cookie banner if necessary
    Then I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/address/fields"
    And I should see text matching "Link"
    And I should see text matching "field_address_link"

  Scenario: Google Maps contains the expected fields in the address popup
    Given I have dismissed the cookie banner if necessary
    Then I am logged in as a user with the "administrator" role
    And I open node edit form by node title "Page with map paragraph"
    And I choose "Inhalt" from tab menu
    And I press the "field_content_paragraphs_0_edit" button
    And I should see HTML content matching "field_content_paragraphs[0][subform][field_map_address_view_mode]" after a while
    Then I select "Google Map" by name "field_content_paragraphs[0][subform][field_map_address_view_mode]"
    And I press "Speichern"
    And I am on "/degov-demo-content/page-map-paragraph"
    And I click by selector ".dismissButton" via JavaScript

  Scenario: Open Street Maps contains the expected fields in the address popup
    Given I have dismissed the cookie banner if necessary
    Then I am logged in as a user with the "administrator" role
    And I open node edit form by node title "Page with map paragraph"
    And I choose "Inhalt" from tab menu
    And I press the "field_content_paragraphs_0_edit" button
    And I should see HTML content matching "field_content_paragraphs[0][subform][field_map_address_view_mode]" after a while
    Then I select "OSM Map" by name "field_content_paragraphs[0][subform][field_map_address_view_mode]"
    And I press "Speichern"
    And I am on "/degov-demo-content/page-map-paragraph"
