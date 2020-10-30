@api @drupal @view_mode
Feature: deGov - home page teasers

  Background:
    Given I am installing the following Drupal modules:
      | degov_demo_content |
    Given I reset the demo content
    Given I clear the cache
    Given I am on the homepage
    Given I have dismissed the cookie banner if necessary

  Scenario: Confirm that Homepage Slider, Teasers and Citation are in place
    Then I should see the "div#degov-slider" in ".block-block-content"
    And I should see 3 "div#degov-slider div.carousel-item img" elements
    And I should see 2 "div#degov-slider a.carousel-control" elements

    Then I should see the "h2" in ".paragraph-node-reference-node-view-mode-small-image .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-small-image .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-small-image .paragraph__content .teaser-content"
    And I should see the "[class*=teaser-text]" in ".paragraph-node-reference-node-view-mode-small-image .paragraph__content .teaser-content .teaser-content-inner"
    And I should see the ".learn-more.bg-blue" in ".paragraph-node-reference-node-view-mode-small-image .paragraph__content"
    And I should see the "img" in ".paragraph-node-reference-node-view-mode-small-image .small-image:first-child .teaser-image"
    And I should not see "degov_demo_content" in the ".paragraph-node-reference-node-view-mode-small-image" element

    And I should see the "h2" in ".paragraph-node-reference-node-view-mode-long-text .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-long-text .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-long-text .paragraph__content .teaser-content"
    And I should see the "[class*=teaser-text]" in ".paragraph-node-reference-node-view-mode-long-text .paragraph__content .teaser-content .teaser-content-inner"
    And I should see the ".learn-more.bg-blue" in ".paragraph-node-reference-node-view-mode-long-text .paragraph__content"
    And I should see the "img" in ".paragraph-node-reference-node-view-mode-long-text .long-text:first-child .teaser-image"
    And I should not see "degov_demo_content" in the ".paragraph-node-reference-node-view-mode-long-text" element

    And I should see the "h2" in ".paragraph-node-reference-node-view-mode-preview .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-preview .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-preview .paragraph__content .teaser-content"
    And I should see the "[class*=teaser-text]" in ".paragraph-node-reference-node-view-mode-preview .paragraph__content .teaser-content"
    And I should see the ".learn-more.bg-blue" in ".paragraph-node-reference-node-view-mode-preview .paragraph__content"
    And I should see the "img" in ".paragraph-node-reference-node-view-mode-preview .preview:first-child .teaser-image"
    And I should not see "degov_demo_content" in the ".paragraph-node-reference-node-view-mode-preview" element

    And I should see the "h2" in ".paragraph-node-reference-node-view-mode-slim .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-slim .paragraph__header"
    And I should see the "h3" in ".paragraph-node-reference-node-view-mode-slim .paragraph__content .teaser-content"
    And I should see the ".learn-more.bg-blue" in ".paragraph-node-reference-node-view-mode-slim .paragraph__content"
    And I should see the "img" in ".paragraph-node-reference-node-view-mode-slim .slim:first-child .teaser-image"
    And I should not see "degov_demo_content" in the ".paragraph-node-reference-node-view-mode-slim" element

    Then I should see the "img" in ".paragraph.media-reference .citation-image .image__image"
    And I should see the "h3.citation-title" in ".paragraph.media-reference blockquote.citation-content"
    And I should see the "p.citation-text" in ".paragraph.media-reference blockquote.citation-content"
