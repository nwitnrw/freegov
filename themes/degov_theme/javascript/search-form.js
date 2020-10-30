/**
 * @file
 */

(function ($) {
  'use strict';

  /**
   * Drupal js behaviour callback.
   *
   * @type {{attach: Drupal.behaviors.SearchForm.attach}}
   */
  Drupal.behaviors.SearchForm = {
    attach: function (context) {
      var $searchFormContainer = $('.search-form-wrapper', context);
      $searchFormContainer.once('search-form').each(function () {
        var that = this;
        $('.search-form-icon button', context).click(function (event) {
          $(that).toggleClass('d-none');
          $('input[type="text"]', $(that)).focus();
        });
        $('.x-close', context).click(function (event) {
          $(that).toggleClass('d-none');
        });
      });
    }
  };
}(jQuery));
