/**
 * @file
 */

import Navi from './navi';
import SpeedChecker from './speed_checker';
import UserAgentChecker from './user_agent_checker';

/**
 * Defines the behavior of the media bundle video mobile.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Behavior for Video Transcript acordion.
   */
  Drupal.behaviors.videoMobile = {
    attach: function (context, settings) {
      $('.video-mobile__transcription').once('video-mobile-js').each(function () {
        $('.video-mobile__transcription__header').click(function () {
          $('.video-mobile__transcription__body').slideToggle();
          $('i', this).toggleClass('fa-caret-right fa-caret-down');
        });
      });

      $('.video-mobile__quality').once('video-mobile-js').each(function () {
        if ($(this).find('select > option').length > 2) {
          $(this).find('select').on('change', function (event) {
            let selected_file = $(this).val();
            Drupal.behaviors.videoMobile.setVideoSource('#' + $(this).data('for-video'), selected_file);
            Drupal.behaviors.videoMobile.syncVideoSourceAndQualitySwitcher($(this).data('for-video'));
          });
          $(this).show();
        }
      });
    },
    setVideoSource: function (selector, value) {
      $(selector).replaceWith(function () {
        return $(this).attr('src', value);
      });
    },
    syncVideoSourceAndQualitySwitcher: function (videoId) {
      let videoSource = $('#' + videoId).attr('src');
      $('.video-mobile__quality select[data-for-video=' + videoId + ']').val(videoSource);
    }
  }

  Drupal.behaviors.checkCellular = {
    attach: function (context, settings) {
      let videos = settings.degov_media_video_mobile;

      for (let id in videos) {
        let video = videos[id];
        if (Drupal.behaviors.checkCellular.check() && typeof video['files']['video_mobile'] === 'string') {
          Drupal.behaviors.videoMobile.setVideoSource('#' + video['id'], video['files']['video_mobile']);
        }
        Drupal.behaviors.videoMobile.syncVideoSourceAndQualitySwitcher(video['id']);
      }
    },
    check: function () {
      let navi_connection = new Navi(navigator),
        connection = navi_connection.getConnection(),
        isCellular,
        speedChecker = new SpeedChecker(window),
        userAgentChecker = new UserAgentChecker(window);

      if (connection) {
        if (typeof connection.type !== 'undefined') {
          isCellular = (connection.type === 'cellular');
        }
else if (typeof connection.effectiveType !== 'undefined') {
          isCellular = (connection.effectiveType !== '4g');
        }
      }
else {
        isCellular = userAgentChecker.isMobile();

        if (!isCellular) {
          isCellular = speedChecker.checkSlowLoadTime();
        }
      }

      return isCellular;
    }
  }
})(jQuery, Drupal, drupalSettings);
