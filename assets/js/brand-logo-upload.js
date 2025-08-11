jQuery(document).ready(function ($) {
    var mediaUploader;

    $('#upload-brand-logo').on('click', function (e) {
        e.preventDefault();
        
        // Open the media uploader
        mediaUploader = wp.media({
            title: 'Select Brand Logo',
            button: {
                text: 'Use this image'
            },
            multiple: false // Only allow a single file to be selected
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#brand_logo').val(attachment.id); // Store attachment ID in hidden input
            $('#brand-logo-preview').attr('src', attachment.url).show(); // Show the image preview
            $('#remove-brand-logo').show(); // Show the remove button
        });

        mediaUploader.open();
    });

    // Remove logo functionality
    $('#remove-brand-logo').on('click', function (e) {
        e.preventDefault();
        $('#brand_logo').val(''); // Clear the hidden input value
        $('#brand-logo-preview').hide(); // Hide the preview image
        $(this).hide(); // Hide the remove button
    });
});