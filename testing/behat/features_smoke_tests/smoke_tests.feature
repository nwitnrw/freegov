@api @drupal
Feature: deGov - Smoke tests

  Scenario: I can visit the recent log messages page with necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/reports/dblog"
    And I should see text matching "The Database Logging module logs system events in the Drupal database. Monitor your site or debug site problems on this page." via translated text
    And I should see text matching "Recent log messages" via translated text
    And I should see text matching "Type" via translated text
    And I should see text matching "Severity" via translated text

  Scenario: Content administration overview contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I should be on "/user/1"
    And I am on "/admin/content"
    Then I should see text matching "Title" via translated text
    And I should see text matching "Content type" via translated text
    And I should see text matching "Published status" via translated text
    And I should see text matching "Language" via translated text
    And I should see text matching "Action" via translated text
    And I should see text matching "Content" via translated text
    And I should see text matching "Files" via translated text
    And I should see text matching "Newsletter issues" via translated text
    And I should see text matching "Media" via translated text

  Scenario: Block administration page contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/structure/block"
    And I should see text matching "Top Header" after a while
    And I should see text matching "Bottom Header" after a while
    And I should see text matching "Breadcrumbs" via translated text
    And I should see text matching "Main content" via translated text
    And I click by CSS id "edit-blocks-region-top-header-title"
    Then I should see text matching "Add custom block" via translated text after a while

  Scenario: User administration page contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/people"
    And I should see text matching "List" via translated text
    And I should see text matching "Permissions" via translated text
    And I should see text matching "Roles" via translated text
    And I should see text matching "Subscribers" via translated text
    And I should see text matching "Add user" via translated text
    And I click by XPath "//*[@id='block-seven-local-actions']/ul/li/a"
    Then I should see text matching "This web page allows administrators to register new users. Users' email addresses and usernames must be unique." via translated text

  Scenario: I can visit a status page with necessary items
    Given I am logged in as user with the account details from Behat config file
    And I set the privacy policy page for all languages
    And I am on "/admin/reports/status"
    And I should see text matching "Status report" via translated text
    And I should see text matching "Last Cron Run" via translated text
    And I should see text matching "deGov version" via translated text in uppercase

  Scenario: I can visit the views administration page with necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/structure/views"
    And I should see text matching "Mediathek"
    And I should see text matching "Inhalt"
    And I should see text matching "Medien"
    And I should see text matching "Systemprotokoll"
    And I should see text matching "Abonnenten"

  Scenario: Paragraphs types administration page contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/structure/paragraphs_type"
    And I should see text matching "Icon" via translated text in uppercase
    And I should see text matching "Label" via translated text in uppercase
    And I should see text matching "Machine name" via translated text in uppercase
    And I should see text matching "Description" via translated text in uppercase
    And I should see text matching "Operations" via translated text in uppercase

  Scenario: Media types administration page contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/structure/media"
    And I should see HTML content matching "Name"
    And I should see text matching "Description" via translated text in uppercase
    And I should see text matching "Operations" via translated text in uppercase
    And I should see text matching "Add media type" via translated text

  Scenario: Taxonomy administration page contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/structure/taxonomy"
    And I should see text matching "Bereich"
    And I should see text matching "Thema"
    And I should see HTML content matching "Copyright"
    And I should see text matching "Content type" via translated text
    And I should see HTML content matching "Medien Barrierefreiheit"
    And I should see HTML content matching "Medien Sprachen"
    And I should see HTML content matching "Schlagworte"

  Scenario: Content types administration page contains necessary items
    Given I am logged in as user with the account details from Behat config file
    And I am on "/admin/structure/types"
    And I should see HTML content matching "Blog-Artikel"
    And I should see HTML content matching "FAQ"
    And I should see HTML content matching "Inhaltsseite"
    And I should see HTML content matching "Pressemitteilung"
    And I should see HTML content matching "Veranstaltung"
    And I should see text matching "Newsletter Issue" via translated text
