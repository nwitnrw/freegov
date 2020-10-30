/**
 * @file
 * degov-media-address-popup.js
 *
 * Popup for maps.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * @see degov_media_address_get_js_fileds()
   * @param data
   * @return {string}
   */
  Drupal.theme.degovMediaAddressPopup = function (data) {
    if (data) {
      let html = data.title + '<br />' +
        data.organization + '<br />' +
        data.address_line1 + '<br />';
      if (data.address_line2) {
        html += data.address_line2 + '<br />';
      }
      html += data.postal_code + ' ' + data.locality + '<br />';
      if (data.phone_number) {
        html += Drupal.t('Telephone') + ': ' + data.phone_number + '<br />';
      }
      if (data.fax_number) {
        html += Drupal.t('Fax') + ': ' + data.fax_number + '<br />';
      }
      if (data.email) {
        html += Drupal.t('Email') + ': <a href="mailto:' + data.email + '">' + data.email + '</a><br />';
      }
      if (data.link_title && data.link_uri) {
        html += Drupal.t('Link') + ': <a href="' + data.link_uri + '" title="' + data.link_title + '" target="_blank">' + data.link_title + '</a>';
      }
      if (!data.link_title && data.link_uri) {
        html += Drupal.t('Link') + ': <a href="' + data.link_uri + '" title="' + data.link_uri + '" target="_blank">' + data.link_uri + '</a>';
      }
      return html;
    }
    return '';
  };

})(jQuery, Drupal, drupalSettings);
