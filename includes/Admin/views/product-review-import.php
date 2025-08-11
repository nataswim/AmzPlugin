<?php include "common-header.php"; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <i class="fas fa-sync-alt me-2 text-primary"></i><?= esc_html__('Import Amazon Product Reviews', 'ams-wc-amazon'); ?>
                    </h2>
                    <p class="card-text mb-4"><?= esc_html__('Import Amazon product reviews to your WooCommerce store with/without API. Select a product and click "Get Reviews" to start.', 'ams-wc-amazon'); ?></p>
                    
                    <div id="validation-error-message" class="alert alert-danger d-none" role="alert"></div>
                    
                    <form id="ams_product_review" class="wca-product-review-import" method="POST">
                        <div class="mb-4">
                            <select name="ams_product" id="ams_product" class="form-select" required>
                                <option value="" disabled selected><?= esc_html__('Select a Product...', 'ams-wc-amazon'); ?></option>
                                <?php 
                                $posts = get_posts(array(
                                    'post_type' => 'product',
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                ));
                                foreach ($posts as $post) {
                                    if (!get_post_meta($post->ID, '_ams_product_url', true)) continue;
                                    echo '<option value="' . esc_attr($post->ID) . '">' . esc_html(wp_trim_words($post->post_title, 7, '...')) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="text-center">
                            <input type="hidden" name="action" value="ams_product_review_import">
                            <?php wp_nonce_field('ams_product_review_import_nonce'); ?>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <?= esc_html__('Get Reviews', 'ams-wc-amazon'); ?>
                            </button>
                        </div>
                    </form>
                    
                    <div id="review-import-status" class="mt-4 d-none">
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden"><?= esc_html__('Loading...', 'ams-wc-amazon'); ?></span>
                                </div>
                                <span><?= esc_html__('Importing reviews, please wait...', 'ams-wc-amazon'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div id="review_error_message" class="alert alert-danger mt-4 d-none" role="alert"></div>
                </div>
            </div>
            
            <div class="mt-5">
                <div class="wca-amazon-product-import"></div>
                
                <div class="text-center mt-4">
                    <button type="button" class="loadmore btn btn-outline-primary d-none">
                        <?= esc_html__('Load More', 'ams-wc-amazon'); ?>
                    </button>
                    
                    <div class="wca-loading-icon d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?= esc_html__('Loading...', 'ams-wc-amazon'); ?></span>
                        </div>
                        <p class="mt-2"><?= esc_html__('Importing reviews, please wait...', 'ams-wc-amazon'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "common-footer.php"; ?>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $("#ams_product_review").submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let selectedProduct = $("#ams_product").val();
        let $validationError = $('#validation-error-message');
        let $reviewError = $('#review_error_message');
        let $importStatus = $('#review-import-status');
        let $submitButton = form.find('button[type="submit"]');
        let $reviewsContainer = $('.wca-amazon-product-import');

        // Hide any previous messages and clear container
        $validationError.addClass('d-none').html('');
        $reviewError.addClass('d-none').html('');
        $reviewsContainer.html('');

        // Check if the product is selected
        if (!selectedProduct) {
            $validationError
                .removeClass('d-none')
                .html('<?= esc_js(__('Please choose a product from the dropdown list to proceed with the review retrieval.', 'ams-wc-amazon')); ?>');
            return false;
        }
        
        // Show import status
        $importStatus.removeClass('d-none');
        
        // Disable the submit button
        $submitButton.prop('disabled', true);
        
        $.ajax({
            url: amsbackend.ajax_url,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                console.log("Ajax response:", response);
                if (response.status) {
                    let successMessage = $('<div class="alert alert-success" role="alert"></div>')
                        .append('<h4><?= esc_js(__('Review Import Complete!', 'ams-wc-amazon')); ?></h4>')
                        .append('<p>' + response.message + '</p>');
                    
                    $reviewsContainer.html(successMessage);

                    if (response.data && response.data.length > 0) {
                        let dataList = $('<ul class="list-group mt-3"></ul>');

                        // Add product page link
                        let productLink = $('<li class="list-group-item"></li>');
                        productLink.html('<strong>Product Page:</strong> <a href="' + amsbackend.site_url + '?p=' + selectedProduct + '" target="_blank">View Product</a>');
                        dataList.append(productLink);

                        // Add Amazon URL
                        let amazonUrl = $('<li class="list-group-item"></li>');
                        amazonUrl.html('<strong>Amazon URL:</strong> <a href="' + response.data[0] + '" target="_blank">' + response.data[0] + '</a>');
                        dataList.append(amazonUrl);

                        // Add Review Limit
                        let reviewLimit = $('<li class="list-group-item"></li>');
                        reviewLimit.html('<strong>Review Limit Setting:</strong> ' + response.data[1]);
                        dataList.append(reviewLimit);

                        // Add Review Summary
                        if (response.data[2]) {
                            let summary = response.data[2];
                            let summaryItem = $('<li class="list-group-item"></li>');
                            summaryItem.html('<strong>Import Summary:</strong>');
                            
                            let summaryList = $('<ul class="mt-2"></ul>');

                            if (summary.totalReview === 0) {
                                summaryList.append('<li class="text-info">No new reviews to import.</li>');
                                summaryList.append('<li>All reviews for this product were already imported previously.</li>');
                                summaryList.append('<li>Total Existing Reviews: ' + summary.existingReviews + '</li>');
                            } else {
                                summaryList.append('<li>Previously Existing Reviews: ' + summary.existingReviews + '</li>');
                                summaryList.append('<li>New Reviews Imported: ' + summary.totalReview + '</li>');
                                summaryList.append('<li>Total Reviews Now: ' + (summary.existingReviews + summary.totalReview) + '</li>');
                                if (summary.totalReview > 0) {
                                    let avgRating = (summary.totalRating / summary.totalReview).toFixed(1);
                                    summaryList.append('<li>Average Rating of New Reviews: ' + avgRating + ' / 5</li>');
                                }
                            }

                            summaryItem.append(summaryList);

                            // Only show detailed reviews if we have new ones
                            if (summary.result && summary.result.length > 0 && summary.totalReview > 0) {
                                let detailsButton = $('<button class="btn btn-sm btn-outline-primary mt-2">Show Detailed Reviews</button>');
                                let detailedResults = $('<div id="detailedResults" class="mt-3 d-none"></div>');

                                summary.result.forEach(function(review) {
                                    let reviewCard = $('<div class="card mb-2"></div>');
                                    let cardBody = $('<div class="card-body"></div>');

                                    if (review.title) {
                                        cardBody.append('<h5 class="card-title">' + review.title + '</h5>');
                                    }

                                    cardBody.append(
                                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                                        '<span class="text-muted">By ' + review.user + '</span>' +
                                        '<span class="badge bg-primary">' + review.rating + ' ★</span>' +
                                        '</div>'
                                    );

                                    cardBody.append('<p class="card-text">' + review.content + '</p>');
                                    
                                    reviewCard.append(cardBody);
                                    detailedResults.append(reviewCard);
                                });

                                detailsButton.click(function() {
                                    detailedResults.toggleClass('d-none');
                                    $(this).text(function(i, text) {
                                        return text === "Show Detailed Reviews" ? "Hide Detailed Reviews" : "Show Detailed Reviews";
                                    });
                                });

                                summaryItem.append(detailsButton);
                                summaryItem.append(detailedResults);
                            }

                            dataList.append(summaryItem);
                        }

                        $reviewsContainer.append(dataList);
                    }
                } else {
                    $reviewError.removeClass('d-none').html(response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Ajax error:", textStatus, errorThrown);
                $reviewError.removeClass('d-none')
                    .html("<?= esc_js(__('An error occurred while importing reviews.', 'ams-wc-amazon')); ?>");
            },
            complete: function() {
                // Hide import status
                $importStatus.addClass('d-none');
                // Re-enable the submit button
                $submitButton.prop('disabled', false);
            }
        });
    });
});
</script>