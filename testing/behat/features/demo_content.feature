@api @drupal @content
Feature: deGov - Demo Content

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content |

  Scenario: Check if all teasers will be displayed
    Given I am logged in as a user with the "administrator" role
    And I delete all content
    And I reset the demo content
    And I am on "/degov-demo-content/page-all-teasers"
    And I should see "Page with text paragraph"
    And I should see "Page with download paragraph"
    And I should see "Page with iframe paragraph"
    And I should see "Page with map paragraph"
    And I should see "Page with FAQ-List paragraph"
    And I should see "Page with video header"
    And I should see "Page with slideshow"
    And I should see "Page with banner"
    And I should see "TEASER - SMALL IMAGE"
    And I should see "TEASER - LONG TEXT"
    And I should see "TEASER - SLIM"
    And I should see "TEASER - PREVIEW"
    And I should see 145 ".paragraph__content article .image" elements
    And I should see 148 ".paragraph__content article .teaser-title" elements
    And I should see 111 ".paragraph__content article [class*=__teaser-text]" elements

  Scenario: Check for missing fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/degov-demo-content/page-banner"
    And I should see "Page with banner"
    And I should see "A page with an image header"
    And I should see "degov_demo_content"

  Scenario: Check page with video mobile
    Given I am logged in as a user with the "administrator" role
    And I am on "/degov-demo-content/page-responsive-video"
    Then I should see text matching "Page with responsive video"
    And I should see text matching "Choose quality:" via translated text
    And I should see text matching "Download" via translated text

  Scenario: Check that the transcription toggle is working correctly.
    Given I am on "/video-upload/video-upload-sound"
    Then I should see HTML content matching "fa-caret-right"
    And I should not see the element with css selector ".video-upload__transcription__body"
    When I click by selector ".video-upload__transcription__header" via JavaScript
    Then I should see HTML content matching "fa-caret-down"
    And I should see the element with css selector ".video-upload__transcription__body"

  Scenario: Check that generated video upload without sound does not have subtitles file attached
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I open media edit form by media name "The latest video upload"
    And I choose "Beschreibung" from tab menu
    Then I should see 0 "#field-video-upload-subtitle-values .paragraph-type-title" elements

  Scenario: Check that generated video upload has a subtitles file attached
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I open media edit form by media name "A video upload with sound"
    And I choose "Beschreibung" from tab menu
    Then I should see 1 "#field-video-upload-subtitle-values .paragraph-type-title" elements

  Scenario: I want to verify the embed functionality of the instagram media entity
    Given I have dismissed the cookie banner if necessary
    Then I click by selector ".social-media-settings--menu-item" via JavaScript
    And I check checkbox by value "instagram" via JavaScript
    And I click by selector ".social-media-settings__save" via JavaScript
    And I am on "/degov-demo-content/page-references-all-types-media"
    Then I should see text matching "Vorschau eines Instagram-Mediums" after a while
    And I switch to the "instagram-embed-0" frame
    Then I should see 1 ".Header" elements
    And I should see 1 ".Header .AvatarContainer" elements
    And I should see 1 ".Content.EmbedFrame" elements
    And I should see 1 ".Content.EmbedFrame .EmbeddedMedia" elements
    And I should see 1 ".HoverCard" elements
    And I should see 1 ".SocialProof" elements
    And I should see 1 ".Caption" elements
    And I should see 1 ".Footer" elements

  Scenario: I verify that long paragraph title links are not cut off
    Given I have dismissed the cookie banner if necessary
    And I am on "/degov-demo-content/page-text-paragraph"
    Then I should see HTML content matching "https://www.example.com/this-is-a-very-long-link-over-eighty-characters-that-should-not-be-cut-off"
