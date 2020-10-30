/**
 * @file
 */

(function ($) {
  'use strict';

  Drupal.behaviors.degov_media_image = {
    toggle_field_selector: '[id*=-field-royalty-free-value]',
    attach: function (context, settings) {
      Drupal.behaviors.degov_media_image.toggle_copyright_required_indicators();
      $(Drupal.behaviors.degov_media_image.toggle_field_selector).on('click', function () {
        Drupal.behaviors.degov_media_image.toggle_copyright_required_indicators();
      });
    },
    toggle_copyright_required_indicators: function () {
      let required_class = 'form-required',
        details_container_id = $('[name*=field_copyright][name*=target_id]').closest('details').attr('id'),
        copyright_field = $('[name*=field_copyright][name*=target_id]'),
        copyright_field_form_item = copyright_field.closest('.form-item'),
        copyright_field_closest_label = copyright_field_form_item.children('label'),
        vertical_tab = $('.vertical-tabs__menu').find('a[href="#' + details_container_id + '"]').find('.vertical-tabs__menu-item-title');

      if ($(Drupal.behaviors.degov_media_image.toggle_field_selector + ':checked').length === 0) {
        copyright_field.prop('disabled', false).prop('required', 'required').addClass('required');
        copyright_field_form_item.removeClass('form-disabled');
        copyright_field_closest_label.addClass(required_class);
        vertical_tab.addClass(required_class);
      }
else {
        copyright_field.val('').prop('required', false).removeClass('required').prop('disabled', 'disabled');
        copyright_field_form_item.addClass('form-disabled');
        copyright_field_closest_label.removeClass(required_class);
        vertical_tab.removeClass(required_class);
      }
    }
  };

}(jQuery));
