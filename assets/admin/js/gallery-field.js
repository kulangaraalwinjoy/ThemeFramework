/**
 * WordPress Theme Framework - Gallery Field JS
 */
(function ($) {
    'use strict';

    function updateGalleryInput($wrap) {
        var ids = [];
        $wrap.find('.theme-framework-gallery-item').each(function () {
            var id = $(this).data('id');
            if (id) {
                ids.push(id);
            }
        });

        var $input = $wrap.find('.theme-framework-gallery-ids');
        $input.val(ids.join(',')).trigger('change');

        var $clearBtn = $wrap.find('.theme-framework-gallery-clear');
        if (ids.length > 0) {
            $clearBtn.show();
        } else {
            $clearBtn.hide();
        }
    }

    function initSortable($list) {
        if (typeof $.fn.sortable !== 'undefined') {
            $list.sortable({
                items: '.theme-framework-gallery-item',
                cursor: 'move',
                scrollSensitivity: 40,
                forcePlaceholderSize: true,
                placeholder: 'theme-framework-gallery-item ui-sortable-placeholder',
                stop: function () {
                    updateGalleryInput($list.closest('.theme-framework-gallery-wrap'));
                }
            });
        }
    }

    function initGalleryFields() {
        $('.theme-framework-gallery-list').each(function () {
            initSortable($(this));
        });

        // Add Images
        $(document).on('click', '.theme-framework-gallery-add', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.closest('.theme-framework-gallery-wrap');
            var $list = $wrap.find('.theme-framework-gallery-list');
            var modalTitle = $wrap.data('title') || 'Add Images to Gallery';
            var buttonText = $wrap.data('button') || 'Add to Gallery';
            var allowedTypes = $wrap.data('allowed-types');

            var libraryOptions = { type: 'image' };
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
                multiple: true,
                library: libraryOptions
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection');

                selection.each(function (attachmentModel) {
                    var att = attachmentModel.toJSON();
                    var attId = att.id;

                    // Prevent duplicate if already in list
                    if ($list.find('.theme-framework-gallery-item[data-id="' + attId + '"]').length) {
                        return;
                    }

                    var imgUrl = (att.sizes && att.sizes.thumbnail)
                        ? att.sizes.thumbnail.url
                        : att.url;

                    var itemHtml = '<li class="theme-framework-gallery-item" data-id="' + attId + '">' +
                        '<img src="' + imgUrl + '" alt="" />' +
                        '<button type="button" class="theme-framework-gallery-remove">&times;</button>' +
                        '</li>';

                    $list.append(itemHtml);
                });

                initSortable($list);
                updateGalleryInput($wrap);
            });

            frame.open();
        });

        // Remove single image
        $(document).on('click', '.theme-framework-gallery-remove', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.theme-framework-gallery-item');
            var $wrap = $item.closest('.theme-framework-gallery-wrap');

            $item.remove();
            updateGalleryInput($wrap);
        });

        // Clear gallery
        $(document).on('click', '.theme-framework-gallery-clear', function (e) {
            e.preventDefault();

            var $wrap = $(this).closest('.theme-framework-gallery-wrap');
            $wrap.find('.theme-framework-gallery-list').empty();
            updateGalleryInput($wrap);
        });
    }

    $(function () {
        initGalleryFields();
        $(document).on('theme_framework_init', initGalleryFields);
    });
})(jQuery);
