//script foe view details screenshots
jQuery(document).ready(function($) {
    $(document).on('click', '.lightbox-image', function(e) {
        e.preventDefault();
        var imgSrc = $(this).attr('src');
        var lightbox = $('<div class="lightbox"></div>');
        var img = $('<img>').attr('src', imgSrc);
        lightbox.append(img);
        $('body').append(lightbox);
        lightbox.fadeIn('fast');
        lightbox.click(function() {
            $(this).fadeOut('fast', function() {
                $(this).remove();
            });
        });
    });
});
//script foe view details screenshots