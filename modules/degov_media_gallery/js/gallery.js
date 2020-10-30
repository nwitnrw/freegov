/**
 * @file
 * gallery.js
 *
 * Defines the behavior of the media bundle gallery.
 *
 * Eslint
 *   global PhotoSwipe, PhotoSwipeUiDefault
 */
(function ($, Drupal) {
  'use strict';

  /**
   * Initializes the slideshow with Slick and PhotoSwipe.
   */
  Drupal.behaviors.gallery = {
    attach: function (context, settings) {
      $('.media-gallery-wrapper', context).once('initGallery').each(function (galleryIndex) {
        const
          galleryWrapper = $(this),
          pswp = (window.hasOwnProperty('PhotoSwipe') && window.hasOwnProperty('PhotoSwipeUiDefault') && galleryWrapper.prev('.pswp.pswp__media-gallery').length) ? galleryWrapper.prev('.pswp') : null,
          gallery = $('.media-gallery__images', galleryWrapper),
          slider = $('.slideshow__slides', gallery),
          gallerySettings = settings.degov_media_gallery.imagesDownloadLinks[galleryWrapper.data('uuid')],
          galleryControls = {
            download: $('.slick__download', galleryWrapper),
            counterCurrent: $('.slick__counter__current', galleryWrapper),
            counterTotal: $('.slick__counter__total', galleryWrapper),
            pause: $('.slick__pause', galleryWrapper),
            play:  $('.slick__play', galleryWrapper),
          };

        slider.on('init reInit afterChange', function (event, slick, currentSlide) {
          currentSlide = currentSlide || 0;
          const slide = {
            settings: gallerySettings[currentSlide],
            number: currentSlide + 1,
            hasDownload: !!gallerySettings[currentSlide].field_allow_download,
          };

          if (slide.hasDownload) {
            galleryControls.download.removeClass('is-hidden');
          }
          else {
            galleryControls.download.addClass('is-hidden');
          }
          galleryControls.counterCurrent.text(slide.number);
          galleryControls.download.find('.slick__download-link').prop('href', slide.hasDownload ? slide.settings.uri : '');
        });

        slider.once().slick({
          dots: false,
          autoplay: false,
          arrows: true,
          swipeToSlide: true,
          appendArrows: $('.l-slick-navi', this),
        });

        galleryControls.pause.on('click', function () {
          slider.slick('slickPause');
          $(this).hide().siblings('.slick__play').show().focus();
        }).hide();

        galleryControls.play.on('click', function () {
          slider.slick('slickNext').slick('slickPlay');
          $(this).hide().siblings('.slick__pause').show().focus();
        }).show();

        if (pswp) {
          // @see media--gallery--default.html.twig, degov_media_gallery/degov_media_gallery.libraries.yml
          $('.media-gallery-js-open-lightroom', gallery).on("click", function (event) {
            const images = slider.find('.slick-slide').not('.slick-cloned').find('img'),
              pswpUi = new PhotoSwipe(
                pswp[0], // Use DOM element.
                PhotoSwipeUiDefault,
                Drupal.behaviors.gallery.getPswpData(images, gallerySettings),
                {
                  index: parseInt(slider.slick('slickCurrentSlide'))
                }
              );
            pswpUi.init();
          });
        }


      });
    },
    getPswpData: function(images, settings) {
      let currentImages = [];
      $.each(images, function (index) {
        let $pswpItem;
        try {
          $pswpItem = {
            src: settings[index].uri,
            w: settings[index].width,
            h: settings[index].height,
          };
        }
        catch (e) {
          console.log(e.message);
          $pswpItem = [];
        }
        currentImages.push($pswpItem);
      });
      return currentImages;
    }
  };

  /**
   * Hide/show below slideshow copyright.
   */
  Drupal.behaviors.galleryCopyright = {
    attach: function(context) {
      $('.media-gallery.change-outer-copyright', context)
        .once('initSubSliderCopyright')
        .each(function(i, elm) {
          const wrapper = $(elm);
          const paragraphSelector = wrapper.find('.slideshow__slides');
          if (wrapper.length) {
            if (paragraphSelector.length > 0) {
              paragraphSelector.on({
                beforeChange: function(event, slick, currentSlide) {
                  const thisSlide = wrapper.find('.copyright-slide-' + currentSlide);
                  thisSlide.delay(200).toggleClass('is-visible');
                },
                afterChange: function(event, slick, currentSlide) {
                  const thisSlide = wrapper.find('.copyright-slide-' + currentSlide);
                  thisSlide.delay(600).toggleClass('is-visible');
                }
              });
            }
          }
        });
    }
  };

})(jQuery, Drupal);
