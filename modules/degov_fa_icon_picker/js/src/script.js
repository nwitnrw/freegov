/**
 * @file
 */

import "core-js/stable";
import "regenerator-runtime/runtime";
import 'fontawesome';

(($) => {

  'use strict';

  /**
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.iconPicker = {
    attach: (context, settings) => {
      jQuery('.form-item-link-0-options-attributes-class > input', context).iconpicker().show(() => {  });
    }
  };

})(jQuery);
