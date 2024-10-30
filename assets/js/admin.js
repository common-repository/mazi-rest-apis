(function ($) {
    // Add Color Picker to all inputs that have 'color-field' class
    $(function () {
        $('.color-field').wpColorPicker({
            palettes: mazi_vars.option.color_option
        });

        $('.iconpicker').iconpicker({
            title: false,
        });

        $('.iconpicker').on('iconpickerSelected', function (event) {
            $(event.target).prev().find("i").attr("class", event.iconpickerValue);
        });

        // Image Gallery Upload
        $("#edit-gallery").on("click", function (event) {
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if (typeof frame !== 'undefined') {
                frame.open();
                return;
            }

            // Create a new media frame
            frame = wp.media({
                title: 'Select Gallery Images',
                button: {
                    text: 'Use this media'
                },
                multiple: true  // Set to true to allow multiple files to be selected
            });

            // When an image is selected in the media frame...
            frame.on('select', function () {
                $('div.gallery-screenshot').html('');
                var element, preview_html = '', preview_img;
                var ids = frame.state().get('selection').models.map(
                    function (e) {
                        element = e.toJSON();
                        preview_img = typeof element.sizes.thumbnail !== 'undefined' ? element.sizes.thumbnail.url : element.url;
                        preview_html = "<div class='screen-thumb'><img src='" + preview_img + "'/></div>";
                        $('div.gallery-screenshot').append(preview_html);
                        return e.id;
                    }
                );
                $('#gallery').val(ids.join(',')).trigger('change');
            });

            // Set selected attachment file when re-open
            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var gallery = $('#gallery').val();

                if (gallery.length > 0) {
                    var ids = gallery.split(',');

                    ids.forEach(function (id) {
                        attachment = wp.media.attachment(id);
                        attachment.fetch();
                        selection.add(attachment ? [attachment] : []);
                    });
                }
            });

            // Finally, open the modal on click
            frame.open();
        });

        // Reset Gallery
        $("#clear-gallery").on("click", function (event) {
            event.preventDefault();

            // Clear html content
            $('div.gallery-screenshot').html('');

            // Clear hidden input value
            $('#gallery').val("").trigger('change');
        });

        // Feature Image upload
        $(".btn-featured-image").on("click", function (event) {
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if (typeof frame !== 'undefined') {
                frame.open();
                return;
            }

            // Create a new media frame
            frame = wp.media({
                title: 'Select Featured Image',
                button: {
                    text: 'Set featured image'
                },
                multiple: false
            });

            // When an image is selected in the media frame...
            frame.on('select', function () {
                // Reset before set image
                $('div#featured-image-wrapper').html('');

                var element, preview_html = '', preview_img, attachments;
                attachments = frame.state().get('selection').toJSON();
                element = attachments[0];
                preview_img = typeof element.sizes.thumbnail !== 'undefined' ? element.sizes.thumbnail.url : element.url;
                preview_html = "<div class='screen-thumb'><img src='" + preview_img + "'/></div>";

                // Render thumbnail image
                $('div#featured-image-wrapper').append(preview_html);
                // Set attachment id
                $('#featured-image').val(element.id).trigger('change');
            });

            // Set selected attachment file when re-open
            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var file_id = $('#featured-image-id').val();

                if (file_id) {
                    attachment = wp.media.attachment(file_id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                }
            });

            // Finally, open the modal on click
            frame.open();
        });

        // Reset Featured Image
        $("#btn-featured-image-reset").on("click", function (event) {
            event.preventDefault();

            // Clear html content
            $('div#featured-image-wrapper').html('');

            // Clear hidden input value
            $('#featured-image-id').val("").trigger('change');
        });

        // Reset form when ajax finish called
        $(document).ajaxComplete(function (event, xhr, settings) {
            if (settings.hasOwnProperty('data')) {
                var queryStringArr = settings.data.split('&');
                if ($.inArray('action=add-tag', queryStringArr) !== -1) {
                    var xml = xhr.responseXML;
                    $response = $(xml).find('term_id').text();
                    if ($response != "") {
                        // Clear html content
                        $('div#featured-image-wrapper').html('');

                        // Clear hidden input value
                        $('#featured-image-id').val("").trigger('change');

                        // Icon picker reset
                        $('.iconpicker').val("");

                        // Color Picker reset
                        $('input.wp-picker-clear').trigger('click');

                    }
                }
            }
        });
    });
})(jQuery);
