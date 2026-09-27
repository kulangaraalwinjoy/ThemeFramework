/**
 * WordPress Theme Framework - Media Field JS
 */
(function ($) {
    'use strict';

    function initMediaFields() {
        $(document).on('click', '.theme-framework-media-select, .theme-framework-media-replace, .theme-framework-media-upload', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.closest('.theme-framework-media-wrap');
            var $input = $wrap.find('.theme-framework-media-id');
            var $preview = $wrap.find('.theme-framework-media-preview');
            var $selectBtn = $wrap.find('.theme-framework-media-select, .theme-framework-media-upload');
            var $replaceBtn = $wrap.find('.theme-framework-media-replace');
            var $removeBtn = $wrap.find('.theme-framework-media-remove');

            var modalTitle = $wrap.data('title') || 'Select Media';
            var buttonText = $wrap.data('button') || 'Use Media';
            var allowedTypes = $wrap.data('allowed-types');

            var libraryOptions = {};
            if (allowedTypes) {
                if (Array.isArray(allowedTypes) && allowedTypes.length) {
                    var isOnlyImages = allowedTypes.every(function (t) {
                        return typeof t === 'string' && (t.indexOf('image/') === 0 || t === 'image');
                    });
                    if (isOnlyImages) {
                        libraryOptions.type = 'image';
                    } else {
                        libraryOptions.type = allowedTypes;
                    }
                } else if (typeof allowedTypes === 'string' && allowedTypes !== '') {
                    libraryOptions.type = allowedTypes;
                }
            }

            if (typeof wp === 'undefined' || !wp.media) {
                console.error('WordPress media modal (wp.media) is not available.');
                return;
            }

            var frame = wp.media({
                title: modalTitle,
                button: { text: buttonText },
                multiple: false,
                library: libraryOptions
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();

                $input.val(attachment.id).trigger('change');

                var previewHtml = '';
                if (attachment.type === 'image') {
                    var imgUrl = (attachment.sizes && attachment.sizes.thumbnail)
                        ? attachment.sizes.thumbnail.url
                        : attachment.url;
                    previewHtml = '<img src="' + imgUrl + '" alt="' + (attachment.alt || '') + '" style="max-width: 150px; max-height: 150px; height: auto; display: block; margin-bottom: 8px;" />';
                } else {
                    previewHtml = '<div class="theme-framework-media-file-info" style="margin-bottom: 8px;">' +
                        '<span class="dashicons dashicons-media-default"></span>' +
                        '<span class="theme-framework-media-filename">' + (attachment.filename || attachment.title) + '</span>' +
                        '</div>';
                }

                $preview.html(previewHtml).show();
                $selectBtn.hide();
                $replaceBtn.show();
                $removeBtn.show();
            });

            frame.open();
        });

        $(document).on('click', '.theme-framework-media-remove', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.closest('.theme-framework-media-wrap');
            var $input = $wrap.find('.theme-framework-media-id');
            var $preview = $wrap.find('.theme-framework-media-preview');
            var $selectBtn = $wrap.find('.theme-framework-media-select');
            var $replaceBtn = $wrap.find('.theme-framework-media-replace');

            $input.val('').trigger('change');
            $preview.empty().hide();
            $selectBtn.show();
            $replaceBtn.hide();
            $btn.hide();
        });
    }

    $(function () {
        initMediaFields();
        $(document).on('theme_framework_init', initMediaFields);
    });
})(jQuery);
