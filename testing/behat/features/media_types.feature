@api @drupal @media_types
Feature: deGov - Media types

  Background:
    Given I proof that the following Drupal modules are installed:
      | degov_paragraph_media_reference |
      | degov_media_video_mobile        |

  Scenario: Checking available media types
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media"
    Then I should see text matching "Adresse"
    And I should see text matching "Audio"
    And I should see text matching "Bild"
    And I should see text matching "Bildergalerie"
    And I should see text matching "Dokument"
    And I should see text matching "Instagram"
    And I should see text matching "Kontakt"
    And I should see text matching "Person"
    And I should see text matching "Tweet"
    And I should see text matching "Video"
    And I should see text matching "Video Upload"
    And I should see text matching "Responsives Video"
    And I should see text matching "Zitat"

  Scenario: Media type address has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/address/fields"
    Then I should see text matching "field_address_address"
    And I should see text matching "field_address_email"
    And I should see text matching "field_address_fax"
    And I should see text matching "field_media_generic"
    And I should see text matching "field_address_location"
    And I should see text matching "field_include_search"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_address_phone"
    And I should see text matching "field_address_title"
    And I should see text matching "field_title"

  Scenario: Media type audio has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/audio/fields"
    Then I should see text matching "field_audio_caption"
    And I should see text matching "field_description"
    And I should see text matching "field_copyright"
    And I should see text matching "field_media_generic_1"
    And I should see text matching "field_allow_download"
    And I should see text matching "field_media_duration"
    And I should see text matching "field_include_search"
    And I should see text matching "field_audio_mp3"
    And I should see text matching "field_audio_ogg"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_media_transcription"
    And I should see text matching "field_audio_preview"
    And I should see text matching "field_title"
    And I should see text matching "field_media_publish_date"
    Then I am on "/admin/structure/media/manage/audio/fields/media.audio.field_media_publish_date"
    And the "edit-required" checkbox should be checked

  Scenario: Media type video_mobile has all required fields
    Given I am logged in as a user with the "administrator" role
    Then I am installing the "degov_media_video_mobile" module
    Then I am on "/admin/structure/media/manage/video_mobile/fields"
    Then I should see text matching "field_media_accessibility"
    And I should see text matching "field_description"
    And I should see text matching "field_copyright"
    And I should see text matching "field_fullhd_video_mobile_mp4"
    And I should see text matching "field_media_generic_9"
    And I should see text matching "field_hdready_video_mobile_mp4"
    And I should see text matching "field_allow_download"
    And I should see text matching "field_allow_download_mobile"
    And I should see text matching "field_allow_download_hdready"
    And I should see text matching "field_allow_download_fullhd"
    And I should see text matching "field_allow_download_4k"
    And I should see text matching "field_media_duration"
    And I should see text matching "field_include_search"
    And I should see text matching "field_mobile_video_mobile_mp4"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_media_language"
    And I should see text matching "field_video_mobile_mp4"
    And I should see text matching "field_media_transcription"
    And I should see text matching "field_ultrahd4k_video_mobile_mp4"
    And I should see text matching "field_video_mobile_subtitle"
    And I should see text matching "field_subtitle"
    And I should see text matching "field_media_publish_date"
    And I should see text matching "field_video_mobile_caption"
    And I should see text matching "field_video_mobile_preview"
    And I should see text matching "field_title"
    Then I am on "/admin/structure/media/manage/video_mobile/fields/media.video_mobile.field_video_mobile_mp4"
    And the "edit-required" checkbox should be checked

  Scenario: Media type image has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/image/fields"
    Then I should see text matching "field_description"
    And I should see text matching "field_image_caption"
    And I should see text matching "field_image_width"
    And I should see text matching "field_copyright"
    And I should see text matching "field_media_publish_date"
    And I should see text matching "field_allow_download"
    And I should see text matching "field_image_height"
    And I should see text matching "image"
    And I should see text matching "field_include_search"
    And I should see text matching "field_image_mime"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_subtitle"
    And I should see text matching "field_title"
    Then I am on "/admin/structure/media/manage/image/fields/media.image.field_media_publish_date"
    And the "edit-required" checkbox should be checked

  Scenario: I verify that image media entities have copyright related fields
    Given I am logged in as an "Administrator"
    And I have dismissed the cookie banner if necessary
    And I am on "/media/add/image"
    And I choose "Beschreibung" from tab menu
    Then I should see 1 form element with the label "Copyright" and a required input field
    And I should see 1 form element with the label "Bild ist frei" and a "checkbox" field
    And I check checkbox with id "edit-field-royalty-free-value"
    Then I should see 0 form element with the label "Copyright" and a required input field

  Scenario: Media type gallery has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/gallery/fields"
    Then I should see text matching "field_description"
    And I should see text matching "field_media_generic_5"
    And I should see text matching "field_gallery_images"
    And I should see text matching "field_include_search"
    And I should see text matching "field_tags"
    And I should see text matching "field_gallery_title"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_title"
    And I should see text matching "field_media_publish_date"
    Then I am on "/admin/structure/media/manage/gallery/fields/media.gallery.field_media_publish_date"
    And the "edit-required" checkbox should be checked

  Scenario: Media type document has all required fields
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/document/fields"
    Then I should see text matching "field_section"
    And I should see text matching "field_description"
    And I should see text matching "field_document"
    And I should see text matching "field_include_search"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_document_preview"
    And I should see text matching "field_title"

  Scenario: Media type instagram has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/instagram/fields"
    Then I should see text matching "embed_code"
    And I should see text matching "field_include_search"
    And I should see text matching "field_tags"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_title"

  Scenario: Media type contact has all requried fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/contact/fields"
    Then I should see text matching "field_contact_email"
    And I should see text matching "field_contact_fax"
    And I should see text matching "field_media_generic_3"
    And I should see text matching "field_contact_image"
    And I should see text matching "field_include_search"
    And I should see text matching "field_contact_title"
    And I should see text matching "field_contact_position"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_contact_tel"
    And I should see text matching "field_title"

  Scenario: Media Type person has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/person/fields"
    Then I should see text matching "field_person_image"
    And I should see text matching "field_media_generic_6"
    And I should see text matching "field_person_info"
    And I should see text matching "field_include_search"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_title"

  Scenario: Media type tweet has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/tweet/fields"
    Then I should see text matching "field_include_search"
    And I should see text matching "field_tags"
    And I should see text matching "embed_code"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_title"

  Scenario: Media type video has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/video/fields"
    Then I should see text matching "field_description"
    And I should see text matching "field_video_caption"
    And I should see text matching "field_copyright"
    And I should see text matching "field_media_duration"
    And I should see text matching "field_include_search"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_media_transcription"
    And I should see text matching "field_media_video_embed_field"
    And I should see text matching "field_video_preview"
    And I should see text matching "field_title"
    And I should see text matching "field_media_publish_date"
    Then I am on "/admin/structure/media/manage/video/fields/media.video.field_media_publish_date"
    And the "edit-required" checkbox should be checked

   Scenario: Media type video_upload has all required fields
     Given I am logged in as a user with the "administrator" role
     And I am on "/admin/structure/media/manage/video_upload/fields"
     Then I should see text matching "field_description"
     And I should see text matching "field_copyright"
     And I should see text matching "field_media_generic_8"
     And I should see text matching "field_allow_download"
     And I should see text matching "field_media_duration"
     And I should see text matching "field_include_search"
     And I should see text matching "field_video_upload_mp4"
     And I should see text matching "field_video_upload_ogg"
     And I should see text matching "field_media_in_library"
     And I should see text matching "field_tags"
     And I should see text matching "field_media_transcription"
     And I should see text matching "field_video_upload_subtitle"
     And I should see text matching "field_video_upload_caption"
     And I should see text matching "field_video_upload_preview"
     And I should see text matching "field_video_upload_webm"
     And I should see text matching "field_title"
     And I should see text matching "field_media_publish_date"
     Then I am on "/admin/structure/media/manage/video_upload/fields/media.video_upload.field_media_publish_date"
     And the "edit-required" checkbox should be checked

  Scenario: Media type citation has all required fields
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/media/manage/citation/fields"
    Then I should see text matching "field_citation_date"
    And I should see text matching "field_media_generic_2"
    And I should see text matching "field_citation_image"
    And I should see text matching "field_include_search"
    And I should see text matching "field_media_in_library"
    And I should see text matching "field_tags"
    And I should see text matching "field_citation_text"
    And I should see text matching "field_citation_title"
    And I should see text matching "field_title"

  Scenario: Check that media entity types with publish date field have a default value in said field
    Given I am logged in as a user with the "administrator" role
    And I am on "/media/add/audio"
    Then I should see 2 elements with name matching "field_media_publish_date" and a not empty value
    And I am on "/media/add/image"
    Then I should see 2 elements with name matching "field_media_publish_date" and a not empty value
    And I am on "/media/add/gallery"
    Then I should see 2 elements with name matching "field_media_publish_date" and a not empty value
    And I am on "/media/add/video"
    Then I should see 2 elements with name matching "field_media_publish_date" and a not empty value
    And I am on "/media/add/video_upload"
    Then I should see 2 elements with name matching "field_media_publish_date" and a not empty value

  Scenario: I am visiting the media entity type configuration pages
    Given I am on "/admin/structure/media/manage/address"
    Then I am on "/admin/structure/media/manage/gallery"
    Then I am on "/admin/structure/media/manage/video_upload"
    Then I am on "/admin/structure/media/manage/person"
    Then I am on "/admin/structure/media/manage/audio"

  Scenario: I verify that the quality switcher works
    Given I am on "/degov-demo-content/page-responsive-video"
    And I should see 1 "video" elements
    And I prove css selector "video" has HTML attribute "src" that matches value "pexels-videos-1409899-standard"
    And I should see 5 ".video-mobile__quality select option" elements
    Then I select index 3 in dropdown named "video-mobile-quality"
    And I prove css selector "video" has HTML attribute "src" that matches value "pexels-videos-1409899-full-hd"

  Scenario: Check that we have the expected crop types installed
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/config/media/crop"
    Then I should see HTML content matching "1_to_1"
    Then I should see HTML content matching "2_to_1"
    Then I should see HTML content matching "4_to_1"
    Then I should see HTML content matching "8_to_3"
    Then I should see HTML content matching "9_to_3"
    Then I should see HTML content matching "16_to_9"
    Then I should see HTML content matching "12_to_5"
    Then I should see HTML content matching "freeform"

  Scenario: Check that crop types have fields for offsets visible
    Given I am logged in as a user with the "administrator" role
    Given I am on "/admin/config/media/crop/manage/16_to_9"
    Then I should see 12 elements with name matching "offsets" and a not empty value
    And I should see HTML content matching "Beim ersten Hochladen eines Bildes wird das Ausschnittfenster automatisch platziert."

  Scenario: Check that all known crop types can be used
    Given I am logged in as a user with the "administrator" role
    Given I am on "/admin/structure/media/manage/image/form-display"
    And I should see text matching "12_to_5, 16_to_9, 1_to_1, 2_to_1, 4_to_1, 8_to_3, 9_to_3, freeform" in "css" selector "#image .field-plugin-summary"
    Given I am on "/admin/structure/media/manage/image/form-display/media_browser"
    And I should see text matching "12_to_5, 16_to_9, 1_to_1, 2_to_1, 4_to_1, 8_to_3, 9_to_3, freeform" in "css" selector "#image .field-plugin-summary"

  Scenario: Check that "allow download" is disabled by default
    Given I have dismissed the cookie banner if necessary
    And I am logged in as a user with the "administrator" role
    Then I am on "/media/add/audio"
    And I should see 1 "#edit-field-allow-download-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-value:checked" elements via jQuery
    Then I am on "/media/add/image"
    And I should see 1 "#edit-field-allow-download-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-value:checked" elements via jQuery
    Then I am on "/media/add/video_upload"
    And I should see 1 "#edit-field-allow-download-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-value:checked" elements via jQuery
    Then I am on "/media/add/video_mobile"
    And I should see 1 "#edit-field-allow-download-mobile-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-mobile-value:checked" elements via jQuery
    And I should see 1 "#edit-field-allow-download-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-value:checked" elements via jQuery
    And I should see 1 "#edit-field-allow-download-hdready-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-hdready-value:checked" elements via jQuery
    And I should see 1 "#edit-field-allow-download-fullhd-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-fullhd-value:checked" elements via jQuery
    And I should see 1 "#edit-field-allow-download-4k-value" elements via jQuery
    And I should see 0 "#edit-field-allow-download-4k-value:checked" elements via jQuery
