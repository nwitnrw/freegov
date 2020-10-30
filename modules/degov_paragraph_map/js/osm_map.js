/**
 * @file
 * map.js
 *
 * Open Street Map for Address paragraph.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * Initializes the Map with Leaflet.
   */
  Drupal.behaviors.address = {
    attach: function (context, drupalSettings) {

      $('.osm-map', context).each(function (i, mapElement) {

        let map = L.map($('.osm-map#' + mapElement.id).not('.leaflet-container')[0], {
          scrollWheelZoom: true,
          zoomControl: false,
          center: [0, 0],
          zoom: 18
        });

        const mapAddresses = drupalSettings.degov_media_address[mapElement.id];

        if (mapAddresses) {

          let markers = [];

          mapAddresses.forEach(function(addressElement) {

            const popup = Drupal.theme('degovMediaAddressPopup', addressElement.address),
              customPin = new L.Icon({iconUrl: addressElement.pin});

            L.tileLayer('http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
              attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://cartodb.com/attributions">CartoDB</a>'
            }).addTo(map);

            let marker = L.marker([addressElement.lat, addressElement.lon], {icon: customPin});

            // Use the following line, if you want to open the address popup initially.
            // marker.bindPopup(popup, {autoClose:false}).addTo(map).openPopup();
            marker.bindPopup(popup, {autoClose:false}).addTo(map);

            map.panTo(new L.LatLng(addressElement.lat, addressElement.lon));

            markers.push(marker);
          });

          let group = new L.featureGroup(markers);
          map.fitBounds(group.getBounds());

        }
      });
    }
  };

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
