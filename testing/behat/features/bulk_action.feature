@api @drupal @content
Feature: deGov - Bulk Action

  Background:
    Given I proof that Drupal module "node_action" is installed
    Given I am installing the following Drupal modules:
      | degov_demo_content |

  Scenario: Unpublish a bunch of nodes as manager
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Unpublish content" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Not published" via translated text
    And I should not see text matching "Action was performed successfully." via translated text

  Scenario: Approach to unpublish a bunch of nodes as editor should be restricted.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Unpublish content" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should be on "/admin/content"

  Scenario: Approach to change moderation state of published nodes via editor should be restricted.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should not see text matching "Action was performed successfully." via translated text

  Scenario: Try to set moderation state of a bunch of nodes immediately as a manager.
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which moderation state would you like to set?" via translated text after a while
    Then I select "draft" in "edit-moderation-state"
    And I submit the form
    Then I should be on "/admin/content"
    And I should see text matching "Action was performed successfully." via translated text

  Scenario: Try to set moderation state of a bunch of nodes on a date as a manager.
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which moderation state would you like to set?" via translated text after a while
    Then I select "draft" in "edit-moderation-state"
    And I fill in "01012199" for "edit-date-date"
    And I fill in "010000AM" for "edit-date-time"
    And I submit the form
    Then I should be on "/admin/content"
    And I should see text matching "Action was performed successfully." via translated text

  Scenario: Fail on setting moderation state due to date in past as a manager.
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which moderation state would you like to set?" via translated text after a while
    Then I select "draft" in "edit-moderation-state"
    And I fill in "01012010" for "edit-date-date"
    And I fill in "010000AM" for "edit-date-time"
    And I submit the form
    Then I should be not on "/admin/content"
    And I should see text matching "The date must be in the future." via translated text
    And I should not see text matching "Action was performed successfully." via translated text

  Scenario: As an editor I am not allowed to delete content from others.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Delete content" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "No Access" via translated text

  Scenario: As an editor I want to be able to change the author of nodes.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change the author" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which author would you like to set?" via translated text after a while
    Then I select "admin" in "edit-author"
    And I submit the form
    Then I should see text matching "Action was performed successfully." via translated text

  Scenario: As an administrator I do not want to have unwanted actions in my content administration
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    Then I should not see the option "node_make_sticky_action" in "edit-action"
    Then I should not see the option "node_make_unsticky_action" in "edit-action"
    Then I should not see the option "node_promote_action" in "edit-action"
    Then I should not see the option "node_save_action" in "edit-action"
    Then I should not see the option "node_unpromote_action" in "edit-action"
    Then I should not see the option "pathauto_update_alias_node" in "edit-action"

  Scenario: Uninstall permissions_by_term and rerun previous tests
    Given I uninstall the "permissions_by_term" module
    # Scenario: Unpublish a bunch of nodes as manager
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Unpublish content" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Not published" via translated text
    And I should not see text matching "Action was performed successfully." via translated text
    # Scenario: Approach to unpublish a bunch of nodes as editor should be restricted.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Unpublish content" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should be on "/admin/content"
    # Scenario: Approach to change moderation state of published nodes via editor should be restricted.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should not see text matching "Action was performed successfully." via translated text
    # Scenario: Try to set moderation state of a bunch of nodes immediately as a manager.
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which moderation state would you like to set?" via translated text after a while
    Then I select "draft" in "edit-moderation-state"
    And I submit the form
    Then I should be on "/admin/content"
    And I should see text matching "Action was performed successfully." via translated text
    # Scenario: Try to set moderation state of a bunch of nodes on a date as a manager.
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which moderation state would you like to set?" via translated text after a while
    Then I select "draft" in "edit-moderation-state"
    And I fill in "01012199" for "edit-date-date"
    And I fill in "010000AM" for "edit-date-time"
    And I submit the form
    Then I should be on "/admin/content"
    And I should see text matching "Action was performed successfully." via translated text
    # Scenario: Fail on setting moderation state due to date in past as a manager.
    Given I am logged in as a user with the "manager" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change moderation state" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which moderation state would you like to set?" via translated text after a while
    Then I select "draft" in "edit-moderation-state"
    And I fill in "01012010" for "edit-date-date"
    And I fill in "010000AM" for "edit-date-time"
    And I submit the form
    Then I should be not on "/admin/content"
    And I should see text matching "The date must be in the future." via translated text
    And I should not see text matching "Action was performed successfully." via translated text
    # Scenario: As an editor I am not allowed to delete content from others.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Delete content" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "No Access" via translated text
    # Scenario: As an editor I want to be able to change the author of nodes.
    Given I am logged in as a user with the "editor" role
    And I am on "/admin/content"
    And I check checkbox with id "edit-node-bulk-form-0"
    And I check checkbox with id "edit-node-bulk-form-1"
    And I check checkbox with id "edit-node-bulk-form-2"
    And I select "Change the author" in "edit-action" via translated text
    Then I click by selector "#edit-submit--2" via JavaScript
    And I should see text matching "Which author would you like to set?" via translated text after a while
    Then I select "admin" in "edit-author"
    And I submit the form
    Then I should see text matching "Action was performed successfully." via translated text
