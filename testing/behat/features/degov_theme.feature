@api @drupal @content
Feature: deGov - deGov Theme

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content |
    Given I am on homepage

  Scenario: Verify that Searchbox exists in the nav
    And I should not see the element with css selector ".search-form-wrapper"
    And I should see the "i.fas.fa-search" in ".top-header-wrapper nav .search-form-icon button"
    When I click by selector ".top-header-wrapper nav .search-form-icon button" via JavaScript
    Then I should see "div.search-form-wrapper" element visible on the page
    When I click by selector ".top-header-wrapper nav .search-form-icon button" via JavaScript
    Then I should not see the element with css selector ".search-form-wrapper"

  Scenario: Verify that "Teaser social media" blocks are exist in the Content region
    Given I am installing the following Drupal modules:
      | degov_devel |
    Then I turn on development mode
    And I have dismissed the cookie banner if necessary
    And I should see ".top-header-wrapper .navbar a.js-social-media-settings-open" element visible on the page

    Then I should not see ".block--degov-social-media-instagram" in the "main" element
    Then I should not see ".block--degov-social-media-twitter" in the "main" element
    Then I should not see ".block--degov-social-media-youtube" in the "main" element

    Then I click by CSS class "js-social-media-settings-open"
    Then I should see "div#social-media-settings" element visible on the page

    And I check checkbox by value "twitter" via JavaScript
    And I check checkbox by value "youtube" via JavaScript
    And I check checkbox by value "instagram" via JavaScript
    And I click by CSS class "js-social-media-settings-save"

    Then I should see 1 "main .block--degov-social-media-instagram" element
    And I should see text matching "Teaser social media" in "css" selector ".block--degov-social-media-instagram .paragraph__header h2"
    And I should see text matching "Instagram Teaser" in "css" selector ".block--degov-social-media-instagram .paragraph__header h3.sub-title"
    And I should see 4 ".block--degov-social-media-instagram .paragraph__content article" elements
    And I should see the "img" in ".block--degov-social-media-instagram .paragraph__content article a.img-top"

    Then I should see 1 "main .block--degov-social-media-twitter" element
    And I should see text matching "Teaser social media" in "css" selector ".block--degov-social-media-twitter .paragraph__header h2"
    And I should see text matching "Twitter Teaser" in "css" selector ".block--degov-social-media-twitter .paragraph__header h3.sub-title"
    And I should see 4 ".block--degov-social-media-twitter .paragraph__content div.tweet" elements
    And I should see the "img" in ".block--degov-social-media-twitter .paragraph__content .tweet .tweet__avatar a"

    Then I should see 1 "main .block--degov-social-media-youtube" element
    And I should see text matching "Teaser social media" in "css" selector ".block--degov-social-media-youtube .paragraph__header h2"
    And I should see text matching "Youtube Teaser" in "css" selector ".block--degov-social-media-youtube .paragraph__header h3.sub-title"
    And I should see 4 ".block--degov-social-media-youtube .paragraph__content div.teaser-social-media-youtube" elements
    And I should see the "div.play-icon" in ".block--degov-social-media-youtube .paragraph__content .teaser-social-media-youtube .teaser-image a"
    Then I uninstall the "degov_devel" module

  Scenario: Verify that "Sitemap" block exists in the Footer region
    And I should see the "#block-footer" in "footer.site-footer"
    And I should see "Sitemap" in the "footer.site-footer #block-footer h2" element
    And I should see the "a" in "footer.site-footer #block-footer > ul.row > li"

  Scenario: Verify that "Below Footer Menu" block exists in the Footer Bottom region
    Then I should see the "#block-belowfootermenu" in ".footer-bottom-wrapper"
    And I should see 3 "#block-belowfootermenu > ul > li > a" elements
