/**
 * @file
 * media_reference.js
 *
 * Common helper functions used by the paragraph media reference module.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * React on data-entity-id values that appears in the media reference field.
   */
  Drupal.behaviors.filterMediaSelection = {
    attach: function (context, settings) {
      // Go over every data-entity-id value and filter the field view modes.
      $('.field--name-field-media-reference-media [data-entity-id]', context).each(function () {
        var media = $(this).attr('data-entity-id').split(":");
        var view_mode_selector = $(this).closest('.paragraphs-subform, .paragraph-form').find('.field--type-entity-reference-display .form-select');
        ajaxFilterViewModes(media[0], media[1], view_mode_selector.attr('data-drupal-selector'));
      });
    }
  };

  /**
   * Filters view mode field by the referenced entity allowed view modes.
   *
   * @param entity_type
   *   Entity type of the entity reference field.
   * @param entity_id
   *   Entity id of the entity reference field.
   * @param selector
   *   The selector so the closest paragraph subform can be found.
   */
  function ajaxFilterViewModes(entity_type, entity_id, selector) {
    // Ajax request to receive all allowed view modes for this entity.
    $.ajax({
      url: Drupal.url('ajax-load-entity/' + entity_type + '/' + entity_id),
      type: "POST",
      dataType: "json",
      success: function (response) {
        var view_mode_selector = "select[data-drupal-selector='" + selector + "']";
        // Find the view mode field in the paragraph subform.
        var view_mode_field = $(view_mode_selector);
        var view_mode_field_class = '#' + $(view_mode_field).attr('id');
        // Hide all options by default.
        $(view_mode_field_class + " option").hide();
        // Enable all options allowed.
        for (var i in response) {
          $(view_mode_field_class + " option[value='" + response[i] + "']").show();
        }
      }
    });
  }

}(jQuery, Drupal, drupalSettings));
