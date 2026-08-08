/*
 * Lightbox adapter — prettyPhoto API in, Magnific Popup out.
 *
 * prettyPhoto is GPLv2-only and cannot be redistributed with an AGPL product.
 * Magnific Popup (MIT) replaces it. The app calls `$(sel).prettyPhoto(opts)`
 * from a dozen places and marks up links with `rel="prettyPhoto[<id>]"`, so
 * this keeps that surface rather than rewriting every call site and template.
 *
 * Load order: jquery.magnific-popup.min.js, then this file.
 */
(function ($) {
    'use strict';

    if (!$ || !$.fn) {
        return;
    }

    $.fn.prettyPhoto = function () {
        if (!$.fn.magnificPopup) {
            return this;
        }

        return this.each(function () {
            var $link = $(this);
            var rel = $link.attr('rel') || '';
            // rel="prettyPhoto[123]" groups a gallery; bare rel does not.
            var grouped = rel.indexOf('[') !== -1 && rel.indexOf('[]') === -1;

            $link.magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                mainClass: 'mfp-img-mobile',
                gallery: { enabled: grouped },
                image: { verticalFit: true }
            });
        });
    };

    // A few call sites use this helper name instead.
    if (typeof window.bindPrettyview !== 'function') {
        window.bindPrettyview = function (relPrefix) {
            $("a[rel^='" + (relPrefix || 'prettyPhoto') + "']").prettyPhoto();
        };
    }
}(window.jQuery));
