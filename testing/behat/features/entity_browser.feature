@api @drupal @entities
Feature: deGov - Entity browser

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_node_normal_page      |

  Scenario: Entity browser has only expected tabs
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/node/add/normal_page"
    And I press the "edit-field-teaser-image-entity-browser-entity-browser-open-modal" button
    Then I should see HTML content matching '<iframe src="/entity-browser/modal/media_browser' after a while
    When I wait for AJAX to finish
    And I switch to the "entity_browser_iframe_media_browser" frame
    Then I should see 6 ".eb-tabs > ul > li" elements
    And I should see text matching "Library"
    And I should see text matching "Bilder Hochladen"
    And I should see text matching "Hochladen"
    And I should see text matching "Dokumente Hochladen"
    And I should see text matching "Create embed"
    And I should see text matching "Audio hochladen"

    And I should see 2 ".view-filters input[type=text]" elements
    # A hidden filter of entity_browser because the target bundle is set to image
    And I should see 1 ".view-filters input[type=hidden]" elements
    And I should see 1 ".view-filters input[value=image]" elements

    And I should see text matching "Interner Titel"
    And I should see text matching "Öffentlicher Titel"
    And I should see 1 ".view-filters input[value=Filter]" elements

    # The Library widget should only display images.
    And I should see 6 ".views-row img" elements

  Scenario: Verify that the CKEditor entity browser has the media libary view set
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/config/content/entity_browser/ckeditor_media_browser/widgets"
    Then I should see 1 "option[value='media.entity_browser_1'][selected]" elements

  Scenario: Verify that the entity browser widgets (document upload) has proper file extensions
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "admin/structure/media/manage/document/fields/media.document.field_document"
    Then I verify that field value of "#edit-settings-file-extensions" matches "doc, docx, xls, xlsx, csv, ppt, pptx, pdf, odt, ods, odp"
    Given I am on "admin/config/content/entity_browser/ckeditor_media_browser/widgets"
    Then I verify that field value of "#edit-table-88a662a5-10c0-4229-b807-07b76e36b771-form-extensions" matches "doc docx xls xlsx csv ppt pptx pdf odt ods odp"
    Given I am on "admin/config/content/entity_browser/media_browser/widgets"
    Then I verify that field value of "#edit-table-88a662a5-10c0-4229-b807-07b76e36b771-form-extensions" matches "doc docx xls xlsx csv ppt pptx pdf odt ods odp"
