/**
 * @file
 * Initiates map(s) for the Styled Google Map module.
 *
 * A single or multiple Styled Google Maps will be initiated.
 * Drupal behaviors are used to make sure ajax called map(s) are correctly
 *   loaded.
 */

(function ($, Drupal) {
  Drupal.behaviors.styled_google_maps = {
    attach: function (context) {
      $('.styled-google-map', context).each(function (i, mapElement) {
        const conf = drupalSettings.styled_google_map[mapElement.id];
        if (conf) {
          const elm = $(mapElement);
          const markers = [];
          const bounds = new google.maps.LatLngBounds();
          let map_center;

          elm.css({
            height: conf.settings.height
          });

          const map = new google.maps.Map(mapElement, {
            gestureHandling: conf.settings.gestureHandling,
            zoom: parseInt(conf.settings.zoom.default),
            mapTypeId: google.maps.MapTypeId[conf.settings.style.maptype],
            disableDefaultUI: !conf.settings.ui,
            maxZoom: parseInt(conf.settings.zoom.max),
            minZoom: parseInt(conf.settings.zoom.min),
            styles: conf.settings.style.style.length ? JSON.parse(conf.settings.style.style) : [],
            mapTypeControl: conf.settings.maptypecontrol,
            scaleControl: conf.settings.scalecontrol,
            rotateControl: conf.settings.rotatecontrol,
            streetViewControl: conf.settings.streetviewcontrol,
            zoomControl: conf.settings.zoomcontrol,
            draggable: $(window).width() > 480 ? conf.settings.draggable : conf.settings.mobile_draggable
          });

          let infoBubble = [];

          conf.locations.forEach(function (loc, i) {
            // Currently there is only one marker/address per map possible
            // therefore all markers have same content.
            const popupHtml = Drupal.theme('degovMediaAddressPopup', conf.addresses[i]);

            infoBubble[i] = new InfoBubble({
              shadowStyle: parseInt(conf.settings.popup.shadow_style),
              padding: parseInt(conf.settings.popup.padding),
              borderRadius: parseInt(conf.settings.popup.border_radius),
              borderWidth: parseInt(conf.settings.popup.border_width),
              borderColor: conf.settings.popup.border_color,
              backgroundColor: conf.settings.popup.background_color,
              minWidth: conf.settings.popup.min_width,
              maxWidth: conf.settings.popup.max_width,
              maxHeight: conf.settings.popup.min_height,
              minHeight: conf.settings.popup.max_height,
              arrowStyle: parseInt(conf.settings.popup.arrow_style),
              arrowSize: parseInt(conf.settings.popup.arrow_size),
              arrowPosition: parseInt(conf.settings.popup.arrow_position),
              disableAutoPan: parseInt(conf.settings.popup.disable_auto_pan),
              disableAnimation: parseInt(conf.settings.popup.disable_animation),
              hideCloseButton: parseInt(conf.settings.popup.hide_close_button),
              backgroundClassName: conf.settings.popup.classes.background,
            });
            // Set extra custom classes for easy styling.
            infoBubble[i].bubble_.className = 'sgmpopup sgmpopup-' + this.category;
            // infoBubble.close_.src = conf.settings.style.active_pin;.
            infoBubble[i].contentContainer_.className = conf.settings.popup.classes.container;
            infoBubble[i].arrow_.className = conf.settings.popup.classes.arrow;
            infoBubble[i].arrowOuter_.className = conf.settings.popup.classes.arrow_outer;
            infoBubble[i].arrowInner_.className = conf.settings.popup.classes.arrow_inner;

            const marker = new google.maps.Marker({
              position: new google.maps.LatLng(loc.lat , loc.lon),
              map: map,
              html: popupHtml,
              icon: drupalSettings.pin_path,
              original_icon: loc.pin,
              active_icon: loc.pin,
              category: loc.category
            });
            markers.push(marker);

            if (popupHtml) {
              infoBubble[i].setContent(popupHtml);
              google.maps.event.addListener(marker, 'click', (function (map) {
                return function () {
                  markers.forEach(function (m) {
                    m.setIcon(m.original_icon);
                  });
                  this.setIcon(this.active_icon);
                  infoBubble.open(map, this);
                };
              }(map)));
              // Uncomment the following line, if you want to open the bubble initially.
              // infoBubble[i].open(map, marker);
            }
            bounds.extend(marker.getPosition());
          });

          // Currently there is only one marker/address per map possible
          // therefore all markers have same content -> no clustering.
          /**
          if (conf.settings.style.cluster_pin) {
            var mcOptions = {
              gridSize: 60,
              styles: [
                {
                  textColor: 'white',
                  url: conf.settings.style.cluster_pin,
                  height: 73,
                  width: 73,
                  textSize: 17
                }
              ],
              minimumClusterSize: 2,
            };
            const markerCluster = new MarkerClusterer(map, markers, mcOptions);
          }
          **/

          if (conf.settings.map_center.center_coordinates) {
            map_center = new google.maps.LatLng(
              conf.settings.map_center.center_coordinates.lat,
              conf.settings.map_center.center_coordinates.lon
            );
            bounds.extend(map_center);
          }
          else {
            map_center = bounds.getCenter()
          }
          map.setCenter(map_center);

          // This is needed to set the zoom after fitbounds,.
          google.maps.event.addListener(map, 'zoom_changed', function () {
            zoomChangeBoundsListener = google.maps.event.addListener(map, 'bounds_changed', function () {
              const current_zoom = this.getZoom();
              if (current_zoom > parseInt(conf.settings.zoom.default) && map.initialZoom) {
                // Change max/min zoom here.
                this.setZoom(parseInt(conf.settings.zoom.default) - 1);
                map.initialZoom = false;
              }
              google.maps.event.removeListener(zoomChangeBoundsListener);
            });
          });
          map.initialZoom = true;
          map.fitBounds(bounds);
        }
      });
    }
  };

})(jQuery, Drupal);
