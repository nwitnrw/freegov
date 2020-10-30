@install
Feature: Installation via webbrowser

  Scenario: I want to install deGov via webbrowser
    Given I am on "/core/install.php"
    And I should see 1 ".installer-background-images-css" element
    And I should see 1 ".favicon-animation-frames-array-javascript" element
    Then I should see HTML content matching "themes/degov_theme"
    And the HTML title should show the page title and the distribution title
    And I select "Deutsch" in "edit-langcode"
    And I press the "Save and continue" button
    Then task "Sprache auswählen" is done
    And the HTML title should show the page title and the distribution title
    And task "Systemvoraussetzungen überprüfen" is done
    Then I should see text matching "Datenbankkonfiguration" after a while
    Then I fill in "edit-mysql-database" with "testing"
    Then I fill in "edit-mysql-username" with "testing"
    Then I fill in "edit-mysql-password" with "testing"
    Then I fill in "edit-mysql-host" with "127.0.0.1"
    Then I submit the form
    And task "Datenbank einrichten" is done
    And the HTML title should show the page title and the distribution title
    And task "Website installieren" is done
    And the HTML title should show the page title and the distribution title
    And task "Übersetzungen konfigurieren" is done
    And the HTML title should show the page title and the distribution title
    Then I should see text matching "WEBSITE-INFORMATIONEN" after a while
    And the HTML title should show the page title and the distribution title
    And I fill in "site_name" with "Some site name"
    And I fill in "site_mail" with "site@example.com"
    And I fill in "edit-account-name" with "admin"
    And I fill in "edit-account-pass-pass1" with "password"
    And I fill in "edit-account-pass-pass2" with "password"
    And I fill in "edit-account-mail" with "admin@example.com"
    And I select "Deutschland" in "edit-site-default-country"
    And I submit the form
    Then task "Website konfigurieren" is done
    And the HTML title should show the page title and the distribution title
    And task "Install deGov - Theme" is done
    And task "Install deGov - Base" is done
    And the HTML title should show the page title and the distribution title
    And task "Install deGov - Media" is done
    And the HTML title should show the page title and the distribution title
    And task "Install deGov - Theme" is done
    And the HTML title should show the page title and the distribution title
    Then I should see text matching "deGov wurde erfolgreich installiert." after a while
