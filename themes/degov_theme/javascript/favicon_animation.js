/**
 * @file
 */

(function ($) {
    $(document).ready(function () {
        if (typeof favicon_animation_frames !== undefined) {
            var image_counter = 0;
            setInterval(function () {
                $("link[rel='icon']").remove();
                $("link[rel='shortcut icon']").attr('href', favicon_animation_frames[image_counter]);

                image_counter++;
                if (image_counter === favicon_animation_frames.length) {
                    image_counter = 0;
                }
            }, 150);
        }
    });
}(jQuery));
