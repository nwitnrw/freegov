@api @degov_password_policy @access
Feature: deGov - Password Policy

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_password_policy |

  Scenario: Check password length
    Given I am logged in as a user with the "administrator" role
    And I am on "/user/1/edit"
    And I fill in "test" for "Passwort"
    And I fill in "test" for "Passwort bestätigen"
    And I press the "Speichern" button
    Then I should see text matching "Password should be at least 12 characters long" via translated text
    And I should see text matching "Password should contain at least one digit" via translated text
    And I should see text matching "Password should contain at least one special character" via translated text
    And I should see text matching "Password should contain at least one upper-case letter" via translated text
    And I should not see text matching "Password should contain at least one lower-case letter" via translated text

  Scenario: Check letters
    Given I am logged in as a user with the "administrator" role
    And I am on "/user/1/edit"
    And I fill in "TestPassword" for "Passwort"
    And I fill in "TestPassword" for "Passwort bestätigen"
    And I press the "Speichern" button
    Then I should not see text matching "Password should be at least 12 characters long" via translated text
    And I should not see text matching "Password should contain at least one lower-case letter" via translated text
    And I should not see text matching "Password should contain at least one upper-case letter" via translated text
    And I should see text matching "Password should contain at least one digit" via translated text
    And I should see text matching "Password should contain at least one special character" via translated text

  Scenario: Check all policies together
    Given I am logged in as a user with the "administrator" role
    And I am on "/user/1/edit"
    And I fill in "Test!Passw0rd" for "Passwort"
    And I fill in "Test!Passw0rd" for "Passwort bestätigen"
    And I press the "Speichern" button
    Then I should not see text matching "Password should be at least 12 characters long" via translated text
    And I should not see text matching "Password should contain at least one digit" via translated text
    And I should not see text matching "Password should contain at least one lower-case letter" via translated text
    And I should not see text matching "Password should contain at least one upper-case letter" via translated text
    And I should not see text matching "Password should contain at least one special character" via translated text

  Scenario: Password older than 35 days - Message should appear
    Given users:
      | name      | status | uid   | field_password_expiration | field_last_password_reset |
      | behatuser | 1      | 99999 | message                   | 2019-09-25T01:01:01       |
    When I am logged in as "behatuser"
    And I am on "/"
    Then I should see text matching "Your password will expire soon, please update it!" via translated text

  Scenario: Password older than 40 days - Redirect
    Given users:
      | name      | status | uid   | field_password_expiration | field_last_password_reset |
      | behatuser | 1      | 99999 | redirect                  | 2019-09-25T01:01:01       |
    When I am logged in as "behatuser"
    And I am on "/"
    Then I should be on "/user/99999/edit"
    And I should see text matching "This site requires that you change your password every 40 days. Please change your password to proceed." via translated text

  Scenario: Password can not be reused by a user
    Given users:
      | name      | status | uid   | pass          | mail                              |
      | behatuser | 1      | 99999 | Test!Passw0rd | test.password.history@example.com |
    When I am logged in as "behatuser"
    And I am on "/user/99999/edit"
    And I fill in "Current password" via translated text with "Test!Passw0rd"
    And I fill in "Password" via translated text with "Test!Passw0rd"
    And I fill in "Confirm password" via translated text with "Test!Passw0rd"
    And I press the "Speichern" button
    Then I should see text matching "Password has been used already. Choose a different password." via translated text
