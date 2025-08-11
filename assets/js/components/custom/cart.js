document.addEventListener("DOMContentLoaded", function() {
    var submitButton = document.querySelector('form.cart button[type="submit"]');
    if (submitButton) {
        submitButton.classList.add("ams-theme-button"); // Add a new class for styling
    }
});
jQuery(document).ready(function($) {
    // Instead of removing 'cart', add a new class to modify styles if necessary
    setTimeout(function() {
        $('body.single-product form.cart').addClass('ams-custom-style');
    }, 1000);
})