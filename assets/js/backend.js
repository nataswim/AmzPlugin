(function($) {
    'use strict';
    
    $.AjaxQueue = function() {
      this.reqs = [];
      this.requesting = false;
    };
    
    $.AjaxQueue.prototype = {
      add: function(req) {
        this.reqs.push(req);
        this.next();
      },
      next: function() {
        if (this.reqs.length == 0)
          return;

        if (this.requesting == true)
          return;

        var req = this.reqs.splice(0, 1)[0];
        var complete = req.complete;
        var self = this;
        if (req._run)
          req._run(req);
        req.complete = function() {
          if (complete)
            complete.apply(this, arguments);
          self.requesting = false;
          self.next();
        }

        this.requesting = true;
        $.ajax(req);
      }
    };

    var queue = new $.AjaxQueue();

    // here code for product filter
    $( '.wca-search-by' ).on( 'change', function () {
        if ( 'keyword' === $( this ).val() ) {
            $( '.wca-keyword' ).show();
            $( '.wca-asin' ).hide();
        } else {
            $( '.wca-keyword' ).hide();
            $( '.wca-asin' ).show();
        }
    });

    $(document).on( 'submit', '.wca-product-import', function ( event ) {
        event.preventDefault();
   		event.stopPropagation();

        $('.wca-loading-icon').show();
        $('.wca-amazon-product').html('');
        if(!$( '#validation-error-message' ).hasClass('d-none')) {
            $( '#validation-error-message' ).addClass('d-none');
        }
        $.ajax( {

            type: 'POST',
            cache: false,
            url: amsbackend.ajax_url,
            data: new FormData(this),
            contentType: false,
            processData:false,
            success: function ( response ) {
                $( '.wca-loading-icon' ).hide();
                if ( true === response.success ) {
                    const response_json = response.data;
                    let newArr = [];
                    for( let i = 0; i < response_json.length; i++ ) {
                        newArr = newArr.concat(response_json[i]);
                    }
                    var asinLists = $("<div />").addClass('col-12 asin-list');
                    for( let j = 0; j < newArr.length; j++) {
                        asinLists.append(
                            `<div class="asin-list-item" data-asin="${newArr[j]}">
                                <span class="asin-list-item-icon"></span>
                                <span class="asin-list-item-title">${newArr[j]}</span>
                                <span class="asin-list-item-message"></span>
                            </div>`
                        ); 
                    }
                    asinLists.appendTo(".wca-amazon-product");

                    $.each(response_json, function( index, asins ) {
                        // Make an ajax call
                        $.each(asins, function( i, asin ) {
                            $("div[data-asin='"+asin+"']").find('.asin-list-item-icon').addClass('spinner');
                        });
                        queue.add( {
                            type: 'POST',
                            url: amsbackend.ajax_url,
                            data: {
                                'nonce': amsbackend.check_nonce,
                                'asin': asins.join(),
                                'action': 'wca_import_process',
                            },
            
                            success: function ( res ) {
                                $.each(res.data.success, function( i, asin ) {
                                    $("div[data-asin='"+asin+"']")
                                        .addClass('success')
                                        .find('.asin-list-item-icon')
                                        .removeClass('spinner')
                                        .addClass('success')
                                        .html('<i class="dashicons dashicons-yes-alt"></i>')
                                        .siblings('.asin-list-item-message')
                                        .html('<p>This SKU has been imported successfully!</p>');
                                });
                                $.each(res.data.failed, function( i, asin ) {
                                    $("div[data-asin='"+asin+"']")
                                        .addClass('failed')
                                        .find('.asin-list-item-icon')
                                        .removeClass('spinner')
                                        .addClass('failed')
                                        .html('<i class="dashicons dashicons-dismiss"></i>')
                                        .siblings('.asin-list-item-message')
                                        .html('<p>This SKU has been failed to import. Maybe SKU is wrong or not available!</p>');
                                });
                                $.each(res.data.imported, function( i, asin ) {
                                    $("div[data-asin='"+asin+"']")
                                        .addClass('imported')
                                        .find('.asin-list-item-icon')
                                        .removeClass('spinner')
                                        .addClass('imported')
                                        .html('<i class="dashicons dashicons-yes-alt"></i>')
                                        .siblings('.asin-list-item-message')
                                        .html('<p>This SKU already imported in the store!</p>');
                                }); 
                                $.each(res.data.cancelled, function( i, asin ) {
                                    $("div[data-asin='"+asin+"']")
                                        .addClass('cancelled')
                                        .find('.asin-list-item-icon')
                                        .removeClass('spinner')
                                        .addClass('cancelled')
                                        .html('<i class="dashicons dashicons-warning"></i>')
                                        .siblings('.asin-list-item-message')
                                        .html('<p>This SKU has been cancelled to import. Please check your configuration settings!</p>');
                                });              
                            }
            
                        } );
                    });
                } else {
                    // Otherwise, handle the errors or the messages that were provided by the server, here.
                    const res = response.data.find((_, index) => !index);
                    
                    $( '.wca-amazon-product' ).html( '' );
                    $( '#validation-error-message' ).html( $(res.message).text() ).removeClass('d-none');
    
                }
            },
        } );
    });


    var wca_item_page = 1;
    var wca_loading = false;
    var form_data = '';

    //here code is ajax request for get product list
    $( document ).on( 'submit', '.wca-product-search', function ( event ) {
        event.preventDefault();
   		event.stopPropagation();
        
        if( 'keyword' == $('input[type=radio][name=wca_search_by]:checked').val() ) {
            if( '' == $('select[name=ams_amazon_cat]:selected').val() || '' == $('input[name=keyword]').val() ) {
                $('#validation-error-message').html('Both the keyword and category are required fields.').removeClass('d-none');
                return;
            } else {
                $('#validation-error-message').html('').addClass('d-none');
            }
        } else if ( 'asin' == $('input[type=radio][name=wca_search_by]:checked').val() ) {
            if( '' == $('input[name=asin_id]').val() ) {
                $('#validation-error-message').html('ASIN Number is required.').removeClass('d-none');
                return;
            } else {
                $('#validation-error-message').html('').addClass('d-none');
            }       
        } else {
            $('#validation-error-message').html('').addClass('d-none');
        }


        wca_loading = true;
        wca_item_page = 1;
        form_data = $( this ).serialize();

        $( '.wca-loading-icon' ).show();
        $.ajax( {
            type: 'POST',
            url: amsbackend.ajax_url,
            data: form_data + '&item_page=' + wca_item_page,

            success: function ( html ) {
                $( '.wca-loading-icon' ).hide();
                $( '.wca-amazon-product' ).html( html );
                wca_item_page = wca_item_page + 1;
                wca_loading = false;
              $( '.loadmore' ).show();
            }
        } );



        //These codes are written to automatically load the product when scrolling to the bottom of the product page.

        $(document).find( '.loadmore' ).on( 'click', function (event) {
            let thatBtn = $(this);
            $(this).hide();
            if (!wca_loading) {
                if (($(document).height() - $( window ).height()) - $( document.body ).scrollTop() <= 100 ) {
                    wca_loading = true;
                    $( '.wca-loading-icon' ).show();

                    $.ajax( {
                        type: 'POST',
                        url: amsbackend.ajax_url,
                        data: form_data + '&item_page=' + wca_item_page,
                        success: function ( html ) {
                            $( '.wca-loading-icon' ).hide();
                            $( '.wca-amazon-product' ).append( html );
                            $(thatBtn).show();
                            wca_item_page = wca_item_page + 1;
                            wca_loading = false;
                        }
                    } );
                }
            }
        } );
    });


    $(document).on('click', '.wca-add-to-import', function(){

    	event.preventDefault();

			event.stopPropagation();

       

        $(document).find( this ).prop( 'disabled', true);

        var wca_button = this;

        var data_asin = $( this ).attr( 'data-asin' );

        console.log(data_asin);

        // return false;

        $( wca_button ).html( '<span class="dashicons dashicons-update wca-spin"></span>' + amsbackend.ams_t_import );

        queue.add( {

            type: 'POST',

            url: amsbackend.ajax_url,

            beforeSend: function(msg){

		    	$(document).find( this ).prop( 'disabled', true);

		    },

            data: {

                'nonce': amsbackend.check_nonce,

                'asin': data_asin,

                'action': 'ams_product_import',

            },



            success: function ( html ) {
                
                $( '.wca-loading-icon' ).hide();

                $( wca_button ).html( html );

                 $(document).find( '.wca-add-to-import' ).prop( 'disabled', false);

            }

        } );
    });

    //These codes are written for product import
    function wca_product_import() {
    }

    //These codes are written to bring dashboard information
    if ( true == amsbackend.ams_dashboard ) {
        setInterval( function(){
            dashboard_info();
        }, 5000 );

        function dashboard_info() {
            $.ajax({
                type: 'POST',
                url: amsbackend.ajax_url,
                data: {
                    'nonce_ams_dashboard_info': amsbackend.nonce_ams_dashboard_info,
                    'action': 'ams_dashboard_info',
                },
                success: function(data) {
                    $('#wca-products-count').html(data.products_count);
                    $('#wca-total-view-count').html(data.total_view_count);
                    $('#wca-total-product-added-to-cart').html(data.total_product_added_to_cart);
                    $('#wca-total-product-direct-redirected').html(data.total_product_direct_redirected);
                    $('#wca-total-product-search').html(data.products_search_count);
                }
            });
        }

    }

    // Function to update license alert visibility
    function updateLicenseAlert(show) {
        var $alertContainer = $('.container-fluid:has(.alert-danger)').first();
        if (show) {
            $alertContainer.show();
        } else {
            $alertContainer.hide();
        }
    }

// Activation function
$(document).on('click', '.wca-activation-btn', function(event) {
    event.preventDefault();
    event.stopPropagation();
    var wca_purchase_code = $('.wca-purchase-code-input').val();
    if (wca_purchase_code.trim() === '') {
        $('.wca-purchase-massage').html("<p class='wca-error'>Please enter a purchase code.</p>");
        return;
    }
    $('.wca-purchase-massage').html("<p class='success'>Activating...</p>");
    $.ajax({
        type: 'POST',
        url: amsbackend.ajax_url,
        data: {
            'nonce': amsbackend.check_nonce,
            'action': 'ams_license_activation',
            'purchase_code': wca_purchase_code,
        },
        success: function(response) {
            var data;
            try {
                data = JSON.parse(response);
            } catch (e) {
                console.error("Error parsing JSON response:", e);
                $('.wca-purchase-massage').html("<p class='wca-error'>An unexpected error occurred. Please try again.</p>");
                return;
            }
            if (data.status === 'success') {
                // Update license status
                $('#wca_license_activation').html(data.license_status);
                // Update success message
                $('.wca-purchase-massage').html("<p class='success'>" + (data.message || "Activation successful!") + "</p>");
                // Update UI to reflect activated state
                $('#license-status-badge')
                    .removeClass('bg-warning text-dark')
                    .addClass('bg-success')
                    .html('<i class="fas fa-check-circle me-1"></i><span id="license-status-text">Active</span>');
                
                $('.wca-purchase-code-input').prop('disabled', true);
                
                // Replace activation button with deactivation button
                $('.wca-activation-btn')
                    .removeClass('btn-primary wca-activation-btn')
                    .addClass('btn-outline-danger ams-deactivated')
                    .html('<i class="fas fa-power-off"></i>');
                // Hide license alert
                updateLicenseAlert(false);
                // Hide any error messages
                $('.error').hide();
            } else {
                // If activation failed, show the error message
                $('.wca-purchase-massage').html("<p class='wca-error'>" + (data.message || "Activation failed. Please try again.") + "</p>");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error during activation:", status, error);
            $('.wca-purchase-massage').html("<p class='wca-error'>An error occurred during activation. Please try again.</p>");
        }
    });
});

 // Modified deactivation function
$(document).on('click', '.ams-deactivated', function(event) {
    event.preventDefault();
    event.stopPropagation();
    var this_el = this;
    
    if (!confirm('Are you sure you want to deactivate your license?')) {
        return;
    }
    
    $(this_el).html("Deactivating...");
    $('.wca-purchase-massage').html("<p class='success'>Deactivating...</p>");
    
    $.ajax({
        type: 'POST',
        url: amsbackend.ajax_url,
        data: {
            'nonce': amsbackend.nonce_ams_de_activated,
            'action': 'ams_license_deactivated',
        },
        success: function(response) {
            var data;
            try {
                data = JSON.parse(response);
            } catch (e) {
                console.error("Error parsing JSON response:", e);
                $('.wca-purchase-massage').html("<p class='wca-error'>An unexpected error occurred. Please try again.</p>");
                $(this_el).html('<i class="fas fa-power-off"></i>');
                return;
            }
            
            if (data.status === 'success') {
                // Show license alert
                updateLicenseAlert(true);
                
                $('#license-status-badge')
                    .removeClass('bg-success')
                    .addClass('bg-warning text-dark')
                    .html('<i class="fas fa-exclamation-circle me-1"></i><span id="license-status-text">Inactive</span>');
                
                $('.wca-purchase-code-input').prop('disabled', false).val('');
                
                $(this_el)
                    .removeClass('btn-outline-danger ams-deactivated')
                    .addClass('btn-primary wca-activation-btn')
                    .html('<i class="fas fa-check"></i>');
                
                $('.wca-purchase-massage').html("<p class='success'>" + (data.message || "License deactivated successfully.") + "</p>");
            } else {
                // If deactivation failed, show the error message
                $('.wca-purchase-massage').html("<p class='wca-error'>" + (data.message || "Deactivation failed. Please try again.") + "</p>");
                $(this_el).html('<i class="fas fa-power-off"></i>');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error during deactivation:", status, error);
            $('.wca-purchase-massage').html("<p class='wca-error'>An error occurred during deactivation. Please try again.</p>");
            $(this_el).html('<i class="fas fa-power-off"></i>');
        }
    });
});

    // Check license status on page load (optional, if you want to ensure consistency)
    $(document).ready(function() {
    });

// Test Amazon API when the test button is clicked
$(document).ready(function() {
    $('.ams-test-api-btn').on('click', function(event) {
        event.preventDefault();  // Prevent the default form submit action
        event.stopPropagation(); // Stop the event from propagating up the DOM
        
        // Show loading message with Bootstrap 5 spinner
        $('.ams-api-message').html(
            '<div class="d-flex align-items-center">' +
            '<div class="spinner-border spinner-border-sm text-primary me-2" role="status">' +
            '<span class="visually-hidden">Loading...</span>' +
            '</div>' +
            '<span>' + amsbackend.ams_t_testing_api + '</span>' +
            '</div>'
        );
        
        // Disable the button
        $(this).prop('disabled', true);
        
        // Perform the AJAX POST request
        $.ajax({
            type: 'POST',
            url: amsbackend.ajax_url,
            data: {
                nonce: amsbackend.ams_test_api,
                action: 'ams_test_api',
            },
            success: function(data) {
                // Display the result data in the message container
                $('.ams-api-message').html(data);
            },
            error: function() {
                // Optional: handle errors, such as displaying an error message
                $('.ams-api-message').html("<p class='alert alert-danger'>Error processing request.</p>");
            },
            complete: function() {
                // Re-enable the button after the request is complete
                $('.ams-test-api-btn').prop('disabled', false);
            }
        });
    });
});



    //These codes written for plugin accordion
    var wca_acc = document.getElementsByClassName( 'wca-accordion' );
    var i;
    for ( i = 0; i < wca_acc.length; i++ ) {

        wca_acc[ i ].addEventListener( 'click', function () {

            this.classList.toggle( 'wca-active' );


            /* Toggle between hiding and showing the wca-active panel */

            var panel = this.nextElementSibling;

            if ( panel.style.display === 'block' ) {

                panel.style.display = 'none';

            } else {

                panel.style.display = 'block';

            }

        } );
    }
    /**
     * Products search without api
     */


//product-without-api-search//
var searchTimer;
var lastSearchParams = {};

function debounce(func, wait) {
    var timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function performSearch(formData) {
    if (JSON.stringify(formData) === JSON.stringify(lastSearchParams)) {
        return; // Avoid duplicate searches
    }
    lastSearchParams = formData;

    $('.wca-loading-icon').show();
    $('.wca-amazon-product').html('');
    
    $.ajax({
        type: 'POST',
        url: amsbackend.ajax_url,
        data: formData,
        dataType: "json",
        success: function(response) {
            $('.wca-loading-icon').hide();
            if (response.html.trim() === '') {
                // No results found
                $('.wca-amazon-product').html(`
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading">No Results Found</h4>
                            <p>We couldn't find any products matching your search criteria. Please try adjusting your search terms or filters.</p>
                            <hr>
                            <p class="mb-0">Tips: 
                                <ul>
                                    <li>Check your spelling</li>
                                    <li>Try more general keywords</li>
                                    <li>Adjust your price range</li>
                                    <li>Try a different category</li>
                                    <li>Adjust your star rating filter</li>
                                </ul>
                            </p>
                        </div>
                    </div>
                `);
                $('.loadmore').hide();
            } else {
                $('.wca-amazon-product').html(response.html);
                $('.loadmore').show();
            }
            wca_loading = false;
        }
    });
}

var debouncedSearch = debounce(performSearch, 500);

function getFormData() {
    var $categorySelect = $('select[name=ams_amazon_cat]');
    var selectedCategory = $categorySelect.val();
    var keyword = $('input[name=keyword]').val().trim();

    return {
        action: "search_products_without_api",
        nonce: amsbackend.nonce_ams_without_api,
        ams_keyword: keyword,
        ams_amazon_cat: selectedCategory,
        ams_amazon_page: 1,
        min_price: $('#min_price').val(),
        max_price: $('#max_price').val(),
        title_search: $('#title_search').val(),
        star_rating: $('#star_rating').val() || ''
    };
}

$(document).find('.wca-product-without-api-search').on('submit', function(event) {
    event.preventDefault();
    event.stopPropagation();
    
    var $categorySelect = $('select[name=ams_amazon_cat]');
    var selectedCategory = $categorySelect.val();
    var placeholderText = $categorySelect.attr('placeholder-text');
    var firstOptionValue = $categorySelect.find('option:first').val();
    var keyword = $('input[name=keyword]').val().trim();
    
    if (!selectedCategory || selectedCategory === '' || selectedCategory === firstOptionValue || keyword === '') {
        $('#validation-error-message').html('Keyword and category fields are mandatory.').removeClass('d-none');
        return;
    } else {
        $('#validation-error-message').html('').addClass('d-none');
    }
    
    wca_loading = true;
    wca_item_page = 0;
    
    var formData = getFormData();
    performSearch(formData);
});



// Event listener for real-time filtering (only for title and star rating)
$('#title_search, #star_rating').on('input change', function() {
    var formData = getFormData();
    debouncedSearch(formData);
});
//product-without-api-search//


    /**
     * Products import without api
     */
    $(document).on('click', '.wca-import-without-api', function(event){
        event.preventDefault();
        event.stopPropagation();
        
        var wca_button = this;
        var $button = $(wca_button);
        
        $button.prop('disabled', true)
               .html('<span class="dashicons dashicons-update wca-spin"></span>' + amsbackend.ams_t_import);

        $.ajax({
            type: 'POST',
            url: amsbackend.ajax_url,
            data: {
                nonce: amsbackend.nonce_ams_without_api,
                action: 'product_import_without_api',
                title: $button.attr('data-title'),
                img: $button.attr('data-img'),
                detail_page_url: $button.attr('data-detail-page-url'),
                asin: $button.attr('data-asin'),
                amount: $button.attr('data-amount'),
            },
            timeout: 300000, // 5 minutes
            success: function (response) {
                $('.wca-loading-icon').hide();
                if (response && response.trim() !== '') {
                    $button.html(response);
                } else {
                    $button.html('Import complete');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Import failed:', textStatus, errorThrown);
                $button.html('Import failed');
            },
            complete: function () {
                $button.prop('disabled', true);
            }
        });
    });

    function wca_product_import_without_api() {
    }


    $('.ams-buy-action-btn').on('change', function () {
        if ($(this).val() == 'dropship') {
            $('.ams-dropship').show();
        } else {
            $('.ams-dropship').hide();
        }
        
        if (this.value == 'multi_cart' || this.value == 'dropship') {
            $('.ams').addClass('d-none');
            $(this).parent().next().removeClass('d-none');
        } else {
            $('.ams').addClass('d-none');
        }
    });


    $(document).find('.wca-product-import-by-url').on('click', function (event) {
        event.preventDefault();
   		event.stopPropagation();

        $( '.wca-amazon-product' ).show();

        var product_all_url = $( '#wca-product-all-url' ).val();
        var array_url_list = product_all_url.split( ',' );
        var promises = [];

        for ( i = 0; i < array_url_list.length; i++ ) {
            $(document).find( '.wca-loading-icon' ).show();
            $( '.wca-amazon-product-by-url' ).append( '<p class="wca-import-warning"> ' + array_url_list [ i ] + amsbackend.ams_mass_product_importing + '  </p>' );

           var request =  $.ajax( {
                type: 'POST',
                url: amsbackend.ajax_url,
                data: {
                    nonce: amsbackend.nonce_ams_import_product_url,
                    action: 'ams_product_import_by_url',
                    product_url: array_url_list [ i ],
                },

               
                success: function ( html ) {
                    $( '.wca-amazon-product-by-url' ).append( html );
                }

            } );

            promises.push(request);
        }

        $.when.apply(null, promises).done(function() {
            $(document).find( '.wca-loading-icon' ).hide();
        })
    });
    
    
    /**
     *
     *  Product update request
     *
     */
     
    $(document).on('click', '.wca-product-update-request', function() {
        event.preventDefault();
        event.stopPropagation();

        var wca_button = this;
        var post_id = $( this ).attr( 'data-post-id' );
        var product_url = $( this ).attr( 'data-url' );
        $(document).find( wca_button ).prop( 'disabled', true);

        $( wca_button ).html( '<span class="dashicons dashicons-update wca-spin"></span>' + amsbackend.ams_t_import );

        queue.add({
            type: 'POST',
            url: amsbackend.ajax_url,
            data: {
                post_id: post_id,
                product_url: product_url,
                action: 'product_update_request',
                nonce: amsbackend.nonce_product_update_request,
            },
            success: function ( html ) {
                $( '.wca-loading-icon' ).hide();
                $( wca_button ).html( html );
                $( wca_button ).prop( 'disabled', true);
            }
        });
    });

})(jQuery);