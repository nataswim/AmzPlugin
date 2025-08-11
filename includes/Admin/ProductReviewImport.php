<?php
namespace Amazon\Affiliate\Admin;

class ProductReviewImport extends ProductsSearchWithoutApi
{
    public function __construct()
    {
        // Constructor logic (if any) goes here
    }

    public function product_review_page()
    {
        if ('1' != get_option('enable_amazon_review', true)) {
            wp_redirect('admin.php?page=wc-product-setting-page&tab=pills-general-tab');
        }
        $this->get_amazon_cat();
        $this->get_wc_terms();
        $template = __DIR__ . '/views/product-review-import.php';
        if (file_exists($template)) {
            require_once $template;
        }
    }

    public function product_review_import() {
        $nonce = sanitize_text_field($_POST['_wpnonce']);
        if (!wp_verify_nonce($nonce, 'ams_product_review_import_nonce')) {
            die(esc_html__('Busted!', 'ams-wc-amazon'));
        }
        
        $ams_product = sanitize_text_field($_POST['ams_product']);  
        $product = wc_get_product($ams_product);
        if (!$product) {
            $response = array( 
                'status' => false, 
                'message' => '<div class="alert alert-danger w-100">' . __('Please select valid product!', 'ams-wc-amazon') . '</div>'
            );
            wp_send_json($response);
        }

        // Get the main product URL - we'll use this directly
        $product_url = get_post_meta($product->get_id(), '_ams_product_url', true);
        
        // Get review limit from settings
        $limit = (int) get_option('single_import_review_limit', 10);

        // Get user agent
        $user_agent = $this->user_agent();
        
        // Get product data using existing method
        $response_body = fetchAndValidateProductData($product_url, $user_agent, false);
        if (!is_string($response_body) || empty($response_body)) {
            wp_send_json([
                'status' => false,
                'message' => '<div class="alert alert-danger w-100">' . __('Failed to fetch product data', 'ams-wc-amazon') . '</div>'
            ]);
        }

        // Create HTML object
        if (!class_exists('simple_html_dom')) {
            require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
        }
        $html = new \simple_html_dom();
        $html->load($response_body);

        // Check for broken page
        $message = check_for_broken_page($response_body, $html);
        if ($message !== null) {
            wp_send_json([
                'status' => false,
                'message' => '<div class="alert alert-danger w-100">' . $message . '</div>'
            ]);
        }

        // Use existing scrape function to get reviews from the main product page HTML
        $reviews = scrape_amazon_reviews($html, $limit);
        
        if (empty($reviews)) {
            wp_send_json([
                'status' => false,
                'message' => '<div class="alert alert-info w-100">' . __('Product you choose does not have a review yet!', 'ams-wc-amazon') . '</div>'
            ]);
        }

        // Get existing reviews
        $existing_reviews = get_comments([
            'post_id' => $product->get_id(),
            'type' => 'review',
            'status' => 'approve'
        ]);
        
        $existing_count = count($existing_reviews);
        
        // Create array of existing review hashes
        $existing_hashes = [];
        foreach ($existing_reviews as $existing_review) {
            $existing_hash = get_comment_meta($existing_review->comment_ID, 'review_hash', true);
            if (!empty($existing_hash)) {
                $existing_hashes[$existing_hash] = $existing_review->comment_ID;
            }
        }

        // Check if all reviews already exist
        $new_reviews_count = 0;
        foreach ($reviews as $review_hash => $review) {
            if (!isset($existing_hashes[$review_hash])) {
                $new_reviews_count++;
            }
        }

        if ($new_reviews_count === 0) {
            wp_send_json([
                'status' => true,
                'data' => [
                    $product_url,
                    $limit,
                    [
                        'totalRating' => 0,
                        'totalReview' => 0,
                        'existingReviews' => $existing_count,
                        'result' => []
                    ]
                ],
                'message' => '<div class="alert alert-info text-center w-100">' . 
                    sprintf(__('No new reviews to import. Product already has %d reviews.', 'ams-wc-amazon'), $existing_count) . 
                '</div>'
            ]);
            return;
        }

        // Initialize rating totals
        $rating_sum = 0;
        $rating_count = 0;
        $review_results = [];

        // Process each review
        foreach ($reviews as $review_hash => $review) {
            // Skip if review already exists
            if (isset($existing_hashes[$review_hash])) {
                continue;
            }

            // Prepare comment data
            $commentdata = [
                'comment_post_ID' => $product->get_id(),
                'comment_author' => $review['reviewer_name'],
                'comment_content' => $review['text'],
                'comment_date' => $review['date'],
                'comment_date_gmt' => get_gmt_from_date($review['date']),
                'comment_approved' => 1,
                'comment_type' => 'review',
                'user_id' => 0
            ];

            // Insert the comment
            $comment_id = wp_insert_comment($commentdata);

            if ($comment_id) {
                // Add all the comment meta
                add_comment_meta($comment_id, 'rating', $review['rating']);
                add_comment_meta($comment_id, 'review_hash', $review_hash);
                add_comment_meta($comment_id, 'verified', 1);
                add_comment_meta($comment_id, 'title', $review['title']);

                if (!empty($review['reviewer_image'])) {
                    add_comment_meta($comment_id, 'reviewer_image', $review['reviewer_image']);
                }

                $rating_sum += floatval($review['rating']);
                $rating_count++;

                // Store for response
                $review_results[] = [
                    'title' => $review['title'],
                    'user' => $review['reviewer_name'],
                    'rating' => $review['rating'],
                    'content' => $review['text']
                ];
            }
        }

        // Update product rating if we added any new reviews
        if ($rating_count > 0) {
            $product = wc_get_product($product->get_id());
            if ($product) {
                // Get actual count of approved reviews
                $actual_review_count = get_comments([
                    'post_id' => $product->get_id(),
                    'type' => 'review',
                    'status' => 'approve',
                    'count' => true
                ]);

                // Calculate actual rating sum
                $actual_rating_sum = 0;
                $product_reviews = get_comments([
                    'post_id' => $product->get_id(),
                    'type' => 'review',
                    'status' => 'approve'
                ]);

                foreach ($product_reviews as $review) {
                    $rating = get_comment_meta($review->comment_ID, 'rating', true);
                    $actual_rating_sum += floatval($rating);
                }

                // Calculate new average
                $new_average = $actual_rating_sum / $actual_review_count;

                // Update all rating meta
                update_post_meta($product->get_id(), '_wc_average_rating', round($new_average, 2));
                update_post_meta($product->get_id(), '_wc_rating_count', $actual_review_count);
                update_post_meta($product->get_id(), '_wc_review_count', $actual_review_count);
                update_post_meta($product->get_id(), '_wc_rating_sum', $actual_rating_sum);

                // Clear all relevant caches
                delete_transient('wc_product_reviews_' . $product->get_id());
                delete_transient('wc_average_rating_' . $product->get_id());
                wp_cache_delete($product->get_id(), 'product');
                
                if (function_exists('wc_delete_product_transients')) {
                    wc_delete_product_transients($product->get_id());
                }
            }
        }

        // Prepare response results
        $results = [
            'totalRating' => $rating_sum,
            'totalReview' => $rating_count,
            'existingReviews' => $existing_count,
            'result' => $review_results
        ];

        // Send response
        wp_send_json([
            'status' => true,
            'data' => [$product_url, $limit, $results],
            'message' => '<div class="alert alert-success text-center w-100">' . 
                sprintf(__('Successfully imported %d new reviews. Total reviews: %d', 'ams-wc-amazon'), 
                    $rating_count, ($existing_count + $rating_count)) . 
            '</div>'
        ]);
    }
}