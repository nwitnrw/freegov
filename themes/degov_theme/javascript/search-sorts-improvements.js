/**
 * @file
 */

(function ($) {
  'use strict';

  /**
   * Drupal js behaviour callback.
   *
   * @type {{attach: Drupal.behaviors.SearchSortsImprovements.attach}}
   */
  Drupal.behaviors.SearchSortsImprovements = {
    attach: function (context) {
      var $dropdownControl = $('.dropdown-title-holder', context);
      $dropdownControl.once('dropdown-title-holder').each(function (i) {
        $('.search-api-sorts a').click(function () {
          // Mute table sort text.
          $('.tablesort', $(this)).html('');
          $('.dropdown-title-holder-text', $dropdownControl).text($(this).text());
        });
      });
    }
  };
}(jQuery));
