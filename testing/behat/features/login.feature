@api @drupal @access
Feature: deGov - Login

  Background:
    Given I proof that the following Drupal modules are installed:
      | mail_login        |
      | degov_email_login |
    Given users:
      | name      | surname     | mail            | pass     |
      | Test      | User        | test@degov.ext  | test123  |

  Scenario: I am on the login page and check for the username / email address label
    Given I am on "/user"
    Then I should see text matching "Benutzername oder E-Mail Adresse"
    And I should see 1 ".top-header-wrapper .navbar-nav .nav-item a i.fas.fa-user" element

  Scenario: I am on the login page and login with the users email address
    Given I am on "/user"
    Then I fill in "test@degov.ext" for "Benutzername oder E-Mail Adresse"
    And I fill in "test123" for "Passwort"
    And I press the "Anmelden" button
    Then I should see the text "Mitglied seit"
    And I should see 1 ".top-header-wrapper .navbar-nav .nav-item a i.fas.fa-user" element
    And I should see 1 ".top-header-wrapper .navbar-nav .nav-item a i.fas.fa-sign-out-alt" element
