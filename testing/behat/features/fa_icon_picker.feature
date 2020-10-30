@api @drupal @form
Feature: deGov - Font Awesome Icon Picker

  Background:
    Given I proof that Drupal module "degov_fa_icon_picker" is installed

  Scenario: Icon picker appears on clicking into link attributes class input field
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/structure/menu/manage/main/add"
    And I click by XPath "//*[@id='edit-link-0-options-attributes']/summary"
    And I click by XPath "//*[@id='class']"
    And I should see ".iconpicker-popover" element visible on the page
