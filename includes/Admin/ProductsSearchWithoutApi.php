<?php
namespace Amazon\Affiliate\Admin;
/**
 * Class ProductsSearchWithoutApi
 *
 * @package Amazon\Affiliate\Admin
 */
class ProductsSearchWithoutApi extends ImportProducts {
    /**
     * Load product search page view
     */
    public function products_page() {
        $this->get_amazon_cat();
        $this->get_wc_terms();
        $template = __DIR__ . '/views/proudcts-search-without-api.php';
        if ( file_exists( $template ) ) {
            require_once $template;
        }
    }
    /**
     * Amazon product category
     *
     * @return string[]
     */
    public function get_option( $name ) {
        $option =  get_option( $name );
        return  $option;
    }

    public function get_amazon_cat() {
        $all_country_cat = ams_amazon_departments();
        $country = get_option('ams_amazon_country');

        // Special case for China: use 'com' categories
        if ($country === 'cn') {
            return $all_country_cat['com'];
        }

        // Check if the country exists in the all_country_cat array
        if (isset($all_country_cat[$country])) {
            return $all_country_cat[$country];
        } else {
            // If the country is not found, return the 'com' (US) categories as a fallback
            return $all_country_cat['com'];
        }
    }


    function get_wc_terms() {
        $categories = get_terms( array(
            // 'parent' => 0,
            'hide_empty' => false,
        ) );
        $cat = array();
        foreach ( $categories as $row ) {
            if ( $row->slug == "uncategorized" ) continue;
            if ( 'product_cat' === $row->taxonomy ) {
                $cat[] = array(
                    'term_id'  => $row->term_id,
                    'name'  => $row->name,
                );
            }
        }
        return array_reverse( $cat );
    }

    /**
     * WooCommerce product import form Amazon
     */
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }



    public function get_product_list() {
        // Debug: Log all POST data
        //error_log('POST data: ' . print_r($_POST, true));

        // Step 1: Nonce Verification
        $nonce = sanitize_text_field( $_POST['nonce'] );
        if ( ! wp_verify_nonce( $nonce, 'ams_without_api' ) ) {
            error_log('Nonce verification failed');
            echo esc_html__( 'Busted!', 'ams-wc-amazon' );
            wp_die();
        }

        // Step 3: Search Execution
        $importProduct = new ImportProducts();
        $result = $importProduct->get_search_results([
            'k' => $_POST['ams_keyword'] ?? '',
            'i' => $_POST['ams_amazon_cat'] ?? '',
            'page' => $_POST['ams_amazon_page'] ?? ''
        ]);

        // Debug: Log raw search results
        //error_log('Raw search results: ' . print_r($result, true));

        // Step 4: Error Handling
        if (false == $result) {
            //error_log('Search results are false');
        }

        // Step 5: Data Processing
        $data = $result['data'];
        $load_more = $result['load_more'] ?? 0;

        // Debug: Log processed data
        //error_log('Processed data: ' . print_r($data, true));

        // Step 6: Dynamic Filtering
        $min_price = isset($_POST['min_price']) && $_POST['min_price'] !== '' ? floatval($_POST['min_price']) : null;
        $max_price = isset($_POST['max_price']) && $_POST['max_price'] !== '' ? floatval($_POST['max_price']) : null;
        $title_search = isset($_POST['title_search']) ? sanitize_text_field($_POST['title_search']) : '';
        $star_rating = isset($_POST['star_rating']) && $_POST['star_rating'] !== '' ? floatval($_POST['star_rating']) : null;

        $data = array_filter($data, function($item) use ($min_price, $max_price, $title_search, $star_rating) {
            $price = floatval(str_replace('$', '', $item['Price']));
            $rating = floatval($item['Rating']);
            
            $price_condition = ($min_price === null || $price >= $min_price) && ($max_price === null || $price <= $max_price);
            $title_condition = empty($title_search) || stripos($item['Title'], $title_search) !== false;
            $rating_condition = $star_rating === null || $rating >= $star_rating;
            
            return $price_condition && $title_condition && $rating_condition;
        });

        // Debug: Log filtered data
        //error_log('Filtered data: ' . print_r($data, true));

        // Get the locale for URL construction
        $locale = get_option('ams_amazon_country', 'com'); // Default to 'com' if not set

        // Step 7: HTML Generation
        ob_start();

        foreach ($data as $row) {
            $asin            = $row['ASIN'];
            $title           = $row['Title'];
            $sale_price      = $row['Price'];
            $regular_price   = $row['SavingBasis'];
            $img             = $row['ImageUrl'];
            $rating          = $row['Rating'];
            $review_count    = $row['ReviewCount'];

            // Construct the Amazon product URL using the ASIN
            $product_url = "https://www.amazon.{$locale}/dp/{$asin}";

            // Debug: Log each product's data
            //error_log("Product data - ASIN: $asin, Title: $title, Price: $sale_price, URL: $product_url");

            ?>
            <div class="col-6 col-md-4 col-lg-3 col-xl-2 p-2">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-2 d-flex flex-column">
                        <!-- Image Container -->
                        <div class="img-container mb-2" style="height: 200px;">
                            <img src="<?php echo esc_attr($img); ?>" alt="<?php echo esc_attr($title);?>" class="img-fluid h-100 w-100" style="object-fit: contain;">
                        </div>
                        
                        <!-- Title -->
                        <h6 class="card-title mb-1 overflow-hidden" style="height: 3rem;">
                            <a href="<?php echo esc_url($product_url); ?>" target="_blank" class="text-decoration-none text-dark" title="<?php echo esc_attr($title); ?>">
                                <?php echo wp_trim_words($title, 10, '...'); ?>
                            </a>
                        </h6>
                        
                        <!-- Rating and Review Count -->
                        <div class="mb-1" style="min-height: 1.5rem;">
                            <?php if ($rating > 0) { ?>
                                <span class="text-warning">
                                    <?php
                                    $full_stars = floor($rating);
                                    $half_star = $rating - $full_stars >= 0.5;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $full_stars) {
                                            echo '★';
                                        } elseif ($i == $full_stars + 1 && $half_star) {
                                            echo '★';
                                        } else {
                                            echo '☆';
                                        }
                                    }
                                    ?>
                                </span>
                                <span class="text-muted small ms-1"><?php echo number_format($rating, 1); ?></span>
                                <span class="text-primary small ms-1">(<?php echo esc_html($review_count); ?>)</span>
                            <?php } ?>
                        </div>
                        
                        <!-- Price -->
                        <div class="mb-2" style="min-height: 2rem;">
                            <?php if (!empty($sale_price)) { ?>
                                <span class="fw-bold fs-5 text-danger"><?php echo esc_html($sale_price); ?></span>
                                <?php if (!empty($regular_price) && $regular_price !== $sale_price) { ?>
                                    <small class="text-muted ms-1"><del><?php echo esc_html($regular_price); ?></del></small>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="text-muted">Price Not Available</span>
                            <?php } ?>
                        </div>

                        <!-- View Product Link -->
                        <a href="<?php echo esc_url($product_url); ?>" target="_blank" class="btn btn-link btn-sm p-0 mb-2">View Product</a>
                        
                        <!-- Trending Ribbon -->
                        <div class="position-absolute top-0 start-0 bg-warning text-dark px-2 py-1 small" style="transform: rotate(-45deg) translateX(-20px) translateY(-10px);">
                            Trending
                        </div>
                        
                        <!-- Import Button -->
                        <div class="mt-auto">
                            <?php
                            $ams_all_asin = ams_get_all_products_info_with_parent();
                            if (in_array($asin, $ams_all_asin['asin'])) {
                            ?>
                                <button disabled type="button" class="btn btn-secondary btn-sm w-100">Already Imported</button>
                            <?php } else { ?>
                                <button id="import_btn" type="button" class="btn btn-warning btn-sm w-100 wca-import-without-api"
                                    data-title="<?php echo esc_attr($title); ?>"
                                    data-amount="<?php echo esc_attr($sale_price); ?>"
                                    data-saving-basis="<?php echo esc_attr($regular_price); ?>"
                                    data-img="<?php echo esc_attr($img); ?>"
                                    data-product-url="<?php echo esc_url($product_url); ?>"
                                    data-asin="<?php echo esc_attr($asin); ?>"
                                    data-rating="<?php echo esc_attr($rating); ?>"
                                    data-review-count="<?php echo esc_attr($review_count); ?>">
                                    <?php echo esc_html__('Import Product', 'ams-wc-amazon'); ?>
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        $content = ob_get_clean();

        // Step 8 & 9: Response Preparation and Sending
        $response = json_encode(['html' => $content, 'load_more' => $load_more]);
        
        // Debug: Log final response
        //error_log('Final response: ' . $response);

        echo $response;
        wp_die();
    }

    public function product_import_without_api() {
        $locale = get_option('ams_amazon_country');
        $regions = ams_get_amazon_regions();
        if(!isset($_POST['is_cron']) || $_POST['is_cron'] != 1){
            $region = $regions[$locale]['RegionCode'];
        } else {
            $region = isset($_POST['region']) ? $_POST['region'] : "";
        }
        $nonce = $_POST['nonce'];
        $ams_all_asin = ams_get_all_products_info_with_parent();
        if (!wp_verify_nonce($nonce, 'ams_without_api')) {
            die(esc_html__('Busted!', 'ams-wc-amazon'));
        }
        if (ams_plugin_license_status() === false) {
            $license = sprintf(
                "<h4 class='text-white'>%s</h4>",
                esc_html__('Please activate the plugin license before importing the product.', 'ams-wc-amazon')
            );
            echo wp_kses_post($license);
            wp_die();
        }
        $asin = sanitize_text_field($_POST['asin']);
        if (!empty($ams_all_asin['asin'])) {
            if (in_array($asin, $ams_all_asin['asin'])) {
                $massage = '<span class="dashicons dashicons-saved"></span> ' . esc_html__('Success', 'ams-wc-amazon');
                echo wp_kses_post($massage);
            }
        }

        // Construct the Amazon product URL using the ASIN
        $product_url = "https://www.amazon.{$locale}/dp/{$asin}";
        //echo '<pre>'; dd( $product_url ); echo '</pre>'; exit;

        // Get product data first time
        $user_agent = $this->user_agent();
        $response_body = fetchAndValidateProductData($product_url, $user_agent, false);


        if (is_string($response_body) && strlen($response_body)) {
            if (!class_exists('simple_html_dom')) {
                require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
            }

            $html = new \simple_html_dom();
            $html->load($response_body);

            logImportVerification('Product import started...', null);

            // Check for broken page
            $message = check_for_broken_page($response_body, $html);
            if ($message !== null) {
                // Page is broken, display the message and stop execution
                echo wp_kses_post($message);
                logImportVerification($message, null);
                wp_die();
            }

            // Extract Asin from product_url
            $asin = extractAsin($html, $product_url);
            if(empty($asin)) {
                die(esc_html__('ASIN not found!', 'ams-wc-amazon'));
            }

            // Get Parent ASIN from html
            $parentSku = $this->getParentSkuFromHtml($html);
            if (!empty($parentSku)) {
                // Check both original ASIN and parent SKU
                check_sku_and_parent_sku($asin, $parentSku);
            } else {
                logImportVerification('Failed to extract valid parent SKU', null);
            }

            // Check if product title exists, else abort
            $productTitle = extractAmazonProductTitle($html);
            if ($productTitle === false) {
                logImportVerification('Product Title Extraction Failed', null);
                wp_die();
            }
            $title = html_entity_decode($productTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            logImportVerification('Product Title: ', $title);

            // Fast Import start!
            if ('Yes' == get_option('ams_fast_product_importer')) {
                $productPrice = $this->fetchPriceFromHtml($html);

                $productData = [];
                $productData['asin'] = $asin;
                $productData['title'] = $title;
                $productData['region'] = $region;
                $productData['parentSku'] = $parentSku;
                $productData['product_url'] = $product_url;
                $productData['import_method'] = '3'; // Set manually
                $productData['default_message'] = null; // Set manually
                $productData['attributes'] = $this->getProductAttributeFromHtml($html);
                $productData['regular_price'] = isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0;
                $productData['sale_price'] = isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0;
                
                advancedProductImporter($productData);
                wp_die();
            }
            // Fast Import end!

            // Get Main Content
            $content = $this->fetchContentFromHtml($html);
            $defaultContent = "No detailed description available.";
            $productDescription = !empty($content) ? $content : $defaultContent;

            // Get product import status settings
            $importStatus = get_option('ams_product_import_status', true);

            if (!empty($productDescription)) {

                // Get Product Category
                $product_category = $this->syncAndGetCategory($html);

                // Get Product Short Description
                $short_description = $this->fetchShortDescriptionFromHtml($html);

                // Get Product Additional Content
                $additional_description = $this->fetchAdditionalContentFromHtml($html);

                // Get Product attributes - For checking if variable or simple
                $attributes = $this->getProductAttributeFromHtml($html);
                //echo '<pre>'; dd( $attributes ); echo '</pre>';

                //Run if variable
                if(count($attributes) > 0) {
                    // Create a new instance of WC_Product_Variable
                    $product = new \WC_Product_Variable();

                    // Set the product data
                    $product->set_name(stripslashes($title));
                    $product->set_description($content);
                    $product->set_status($importStatus);

                    // Save the new product
                    $product->save();

                    // Start import product data
                    $post_id = $product->get_id();

                    if($parentSku) {
                        update_post_meta($post_id, '_sku', $parentSku);
                    }

                    // Delete product short description
                    $postData = array(
                        'ID' => $post_id,
                        'post_excerpt' => ''
                    );
                    wp_update_post($postData);

                    // Category saved
                    if(!empty($product_category)) {
                        wp_set_object_terms($post_id, $product_category, 'product_cat');
                        logImportVerification('Category: ', $product_category);
                    }

                    // Update brand name
                    $brandElement = $html->find('a#bylineInfo', 0) 
                        ?: $html->find('div#bylineInfo_feature_div', 0) 
                        ?: $html->find('div#bondByLine_feature_div', 0);

                    if ($brandElement) {
                        $rawBrandName = trim($brandElement->plaintext);

                        $brandName = str_replace(array('Visit the', 'Store'), '', $rawBrandName);

                        // Trim any extra spaces
                        $brandName = trim($brandName);

                        logImportVerification('Brand: ' . $brandName);

                        $brandTerm = term_exists($brandName, 'product_brand');

                        if (!$brandTerm) {
                            // If the brand doesn't exist, create it
                            $brandTerm = wp_insert_term($brandName, 'product_brand');
                        }

                        if (is_wp_error($brandTerm)) {
                            logImportVerification('Error creating brand term: ' . $brandTerm->get_error_message());
                        } else {
                            // Get the term ID
                            $brandTermId = isset($brandTerm['term_id']) ? $brandTerm['term_id'] : $brandTerm;

                            $productId = $post_id;

                            // Assign the brand to the product
                            wp_set_object_terms($productId, intval($brandTermId), 'product_brand');

                            update_post_meta($productId, '_product_brand', $brandName);

                            logImportVerification('Brand assigned to product successfully.');

                            // Add brand as an attribute
                            $product = wc_get_product($productId);
                            if ($product) {
                                $attributes = $product->get_attributes();

                                // Check if the "Brand" attribute already exists
                                $existingAttribute = false;
                                foreach ($attributes as $key => $attribute) {
                                    if ($attribute->get_name() === 'Brand') {
                                        $attributes[$key]->set_options([$brandName]); // Update the brand value
                                        $existingAttribute = true;
                                        break;
                                    }
                                }

                                // If the attribute does not exist, create it
                                if (!$existingAttribute) {
                                    $brandAttribute = new \WC_Product_Attribute();
                                    $brandAttribute->set_name('Brand'); // Attribute name
                                    $brandAttribute->set_options([$brandName]); // Set the scraped brand name
                                    $brandAttribute->set_visible(true); // Make visible on product page
                                    $brandAttribute->set_variation(false); // Not used for variations
                                    $attributes[] = $brandAttribute; // Add new attribute to the list
                                }

                                // Save the updated attributes back to the product
                                $product->set_attributes($attributes);
                                $product->save(); // Save changes

                                logImportVerification('Brand attribute successfully added to the product.');
                            } else {
                                logImportVerification('Failed to retrieve product for adding brand attribute.');
                            }
                        }
                    } else {
                        logImportVerification('Brand not found in the provided HTML.');
                    }
                    // Update brand name


                    // Update the GTIN, UPC, EAN, or ISBN code
                    $upcElement = $html->find('div#productDetails_expanderTables_depthLeftSections', 0);

                    if ($upcElement) {
                        $upcCode = ''; // Initialize variable

                        // Iterate through table rows to find GTIN, UPC, EAN, or ISBN
                        foreach ($upcElement->find('table.prodDetTable tr') as $row) {
                            $header = $row->find('th', 0); // Get the header cell
                            $value = $row->find('td', 0); // Get the value cell

                            if ($header && $value) {
                                $headerText = trim($header->plaintext);
                                $valueText = trim($value->plaintext);

                                // Check for GTIN, UPC, EAN, or ISBN
                                if (stripos($headerText, 'UPC') !== false || stripos($headerText, 'GTIN') !== false || stripos($headerText, 'EAN') !== false || stripos($headerText, 'ISBN') !== false) {
                                    $upcCode = $valueText; // Extract the value
                                    break; // Exit loop once found
                                }
                            }
                        }

                        if (!empty($upcCode)) {
                            // Save the value to the default WooCommerce GTIN/UPC/EAN/ISBN fields
                            update_post_meta($post_id, '_gtin', $upcCode); // GTIN field
                            update_post_meta($post_id, '_upc', $upcCode);  // UPC field
                            update_post_meta($post_id, '_ean', $upcCode);  // EAN field
                            update_post_meta($post_id, '_isbn', $upcCode); // ISBN field
                        }
                    }
                    // Update the GTIN, UPC, EAN, or ISBN code
                    

                    // additional_description saved
                    if( !empty($additional_description) ) {
                        update_post_meta($post_id, '_ams_additional_information', $additional_description);
                        logImportVerification('Additional description saved.', null);
                    }

                    // Product feature image
                    $images = $this->fetchImagesFromHtml($html);
                    $image = !empty($images) ? array_shift($images) : null; // Get the first image
                    $gallery = $images; // Remaining images for gallery
                    $use_remote_images = ('Yes' === get_option('ams_remote_amazon_images'));

                    // Always remove existing featured image and URLs if there's an image
                    if ($image) {
                        // Remove the current featured image and reset URL
                        delete_product_images($post_id);
                        reset_product_thumbnail_url($post_id, $flag = 0);
                    }

                    if (count($gallery) > 0) {
                        // Remove gallery images and reset gallery URLs
                        delete_product_gallery_images($post_id);
                        reset_product_thumbnail_url($post_id, $flag = 1);
                    }

                    if ($use_remote_images) {
                        // Set the featured image URL
                        if ($image) {
                            attach_product_thumbnail_url($post_id, $image, 0);
                        }
                        // Set the gallery image URLs
                        if (count($gallery) > 0) {
                            attach_product_thumbnail_url($post_id, $gallery, 1);
                        }
                        // Remove any locally stored images
                        delete_local_product_images($post_id);
                    } else {
                        // Set the locally stored featured image
                        if ($image) {
                            attach_product_thumbnail($post_id, $image, 0);
                        }
                        // Set the locally stored gallery images
                        if (count($gallery) > 0) {
                            foreach ($gallery as $image) {
                                attach_product_thumbnail($post_id, $image, 1); // Attach gallery images
                            }
                        }
                        // Remove any stored image URLs
                        delete_product_image_urls($post_id);
                    }

                    $skus = $imported_skus = $product_variations = [];

                    // Get all variants based on the SKUs found
                    $all_skus = $this->getSkusFromHtml($html);

                    $variation_ids = $this->getProductFirstVariationFromHtml($html, $parentSku, $product_url, $all_skus);

                    // variations to process
                    $variation_limit = get_option('ams_variation_limit', 5);

                    // Check if there are variation IDs:
                    if (!empty($variation_ids) && count($variation_ids) > 0) {

                        // Apply the static limit to the variation IDs:
                        $variation_ids = array_slice($variation_ids, 0, $variation_limit);
                        
                        // Determine the preferred URL-generation function based solely on product title
                        $preferred_function = null;
                        $first_variation_processed = false;

                        foreach ($variation_ids as $variation_id) {
                            if (in_array($variation_id, $imported_skus)) {
                                continue;
                            }
                            array_push($imported_skus, $variation_id);

                            // For the first variation, decide which function to use based solely on product title.
                            if (!$first_variation_processed) {
                                // Try using function 1 with regular curl first
                                $test_url = generate_amazon_url_1($product_url, $variation_id);
                                $userAgent = getAlternatingBool();
                                $test_content = $this->getContentUsingCurl($test_url, $userAgent);
                                $test_html = new \simple_html_dom();
                                $test_html->load($test_content);
                                
                                // If regular curl fails or no attributes found, try scraping
                                if (!$test_content || count($this->getProductAttributeFromHtml($test_html)) == 0) {
                                    $test_content = executeScrapingService($test_url, true);
                                    $test_html = new \simple_html_dom();
                                    $test_html->load($test_content);
                                }
                                
                                // Check if product title exists; if not, choose function 2
                                $productTitle = extractAmazonProductTitle($test_html);
                                if ($productTitle === false) {
                                    $preferred_function = 2;
                                    //echo "<pre>Preferred function set to 2 (function 1 failed to extract product title).</pre>";
                                    logImportVerification('function 1 failed to extract product title');
                                } else {
                                    $title = html_entity_decode($productTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $preferred_function = 1;
                                    //echo "<pre>Preferred function set to 1 based on product title: {$title}</pre>";
                                    logImportVerification('Preferred function set to 1 based on product title: {$title}');
                                }
                                $test_html->clear();
                                $first_variation_processed = true;
                            }

                            // Use the preferred function to generate the base URL.
                            if ($preferred_function === 1) {
                                $base_url = generate_amazon_url_1($product_url, $variation_id);
                            } else {
                                $base_url = generate_amazon_url_2($product_url, $variation_id);
                            }
                            
                            //echo "<pre>Processing Variation ID: {$variation_id} using function {$preferred_function}. Base URL: {$base_url}</pre>";

                            // First try with regular curl
                            $userAgent = getAlternatingBool();
                            $content = $this->getContentUsingCurl($base_url, $userAgent);
                            $loop_html = new \simple_html_dom();
                            $loop_html->load($content);
                            
                            // Check if we need to use scraping service
                            if (!$content || count($this->getProductAttributeFromHtml($loop_html)) == 0) {
                                $content = executeScrapingService($base_url, true);
                                $loop_html = new \simple_html_dom();
                                $loop_html->load($content);
                            }

                            $productPrice = $this->fetchPriceFromHtml($loop_html);

                            // If no ppd div found, retry twice with proxy
                            $retry = 2;
                            while (isset($productPrice['search_area']) && $productPrice['search_area'] == 'entire HTML' && $retry > 0) {
                                $content = executeScrapingService($base_url, true);
                                $loop_html = new \simple_html_dom();
                                $loop_html->load($content);
                                $productPrice = $this->fetchPriceFromHtml($loop_html);
                                $retry--;
                            }

                            $regular_price = isset($productPrice['final_prices']['regular_price'])
                                ? $productPrice['final_prices']['regular_price']
                                : (isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0);
                            $sale_price = isset($productPrice['final_prices']['sale_price'])
                                ? $productPrice['final_prices']['sale_price']
                                : (isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0);

                            // Add ScraperAPI retry if prices are zero
                            $isUpdate = true;
                            if ($regular_price == 0) {
                                $scraperapi = get_scraping_services_config()['scraperapi'];
                                if (!empty($scraperapi['api_key']) && ($isUpdate ? $scraperapi['on_update'] : $scraperapi['is_active'])) {
                                    $content = call_user_func($scraperapi['execute'], $base_url, $scraperapi['api_key']);
                                    if ($content) {
                                        $loop_html = new \simple_html_dom();
                                        $loop_html->load($content);
                                        $productPrice = $this->fetchPriceFromHtml($loop_html);
                                        
                                        // Update prices with new values
                                        $regular_price = isset($productPrice['final_prices']['regular_price'])
                                            ? $productPrice['final_prices']['regular_price']
                                            : (isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0);
                                        $sale_price = isset($productPrice['final_prices']['sale_price'])
                                            ? $productPrice['final_prices']['sale_price']
                                            : (isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0);
                                    }
                                } else {
                                    //echo '<pre>Warning: ScraperAPI service is disabled. Enable it for better price scraping.</pre>';
                                    logImportVerification('Warning: ScraperAPI service is disabled. Enable it for better price scraping.');
                                }
                            }

                            $currency = $this->fetchCurrencyFromHtml($loop_html);
                            logImportVerification('Currency: ', $currency);

                            if ($regular_price > 0 || $sale_price > 0) {
                                $product_status = 'instock';
                            } else {
                                $product_status = check_product_stock_status($loop_html);
                                if ($product_status === 'instock') {
                                    $product_status = 'outofstock';
                                    logImportVerification('Status changed to outofstock due to zero prices');
                                }
                            }
                            logImportVerification('Final stock status: ', $product_status);

                            $quantity = 0;
                            if ($qty = $loop_html->find('#availability span', 0)) {
                                $quantity = $this->parseNumberFromString($qty->text());
                            }
                            logImportVerification('Quantity: ', $quantity);

                            $short_description = $this->fetchVariationContentFromHtml($loop_html);
                            $additional_description = $this->fetchAdditionalContentFromHtml($loop_html);

                            // Get variation images
                            $v_gallery = $this->fetchImagesFromHtml($loop_html);
                            $image_limit = get_option('ams_variation_image_limit', 5);
                            if ($image_limit > 0) {
                                $v_gallery = array_slice($v_gallery, 0, $image_limit);
                            }
                            // Get variation images

                            $attributes = $this->getProductAttributeFromHtml($loop_html);

                            $product_variations[] = array(
                                'sku'                     => $variation_id,
                                'stock_qty'               => $quantity,
                                'stock_status'            => $product_status,
                                'regular_price'           => $regular_price,
                                'sale_price'              => $sale_price,
                                'attributes'              => $attributes,
                                'description'             => $short_description,
                                'product_image_gallery'   => isset($v_gallery) ? $v_gallery : array(),
                                'additional_description'  => $additional_description,
                            );
                        }
                    }

                    //echo '<pre>'; dd( $product_variations ); echo '</pre>';
                    //error_log(print_r($product_variations, true)); exit;
                    if (count($product_variations) > 0) {
                        wc_create_product_variations($post_id, $product_variations, $parentSku);
                    }

                    update_post_meta($post_id, '_visibility', 'visible');
                    update_post_meta($post_id, '_stock_status', $product_status);
                    update_post_meta($post_id, 'total_sales', '0');
                    update_post_meta($post_id, '_downloadable', 'no');
                    update_post_meta($post_id, '_purchase_note', '');
                    update_post_meta($post_id, '_featured', 'no');
                    update_post_meta($post_id, '_weight', '');
                    update_post_meta($post_id, '_length', '');
                    update_post_meta($post_id, '_width', '');
                    update_post_meta($post_id, '_height', '');
                    update_post_meta($post_id, '_wca_amazon_affiliate_asin', $asin);
                    update_post_meta($post_id, '_wca_amazon_affiliate_parent_asin', $parentSku);
                    update_post_meta($post_id, '_region', $region );
                    update_post_meta($post_id, '_import_method', '3' );
                    update_post_meta($post_id, '_ams_product_url', $product_url );
                    update_post_meta($post_id, '_detail_page_url', $product_url );
                    update_post_meta($post_id, 'ams_last_cron_update', date('Y-m-d H:i:s') );
                    update_post_meta($post_id, 'ams_last_cron_status', 0 );
                    
                    logImportVerification('Variable product created!', null);

                    $message = sprintf(esc_html__(' Success!', 'ams-wc-amazon' ));
                    echo wp_kses_post($message);
                    sleep(2);
                    wp_die();
                } 
                // Run if simple
                else {
                    // Create a new instance of WC_Product_Simple
                    $product = new \WC_Product_Simple();

                    // Set the product data
                    $product->set_name(stripslashes($title));
                    $product->set_description($content);
                    $product->set_status($importStatus);

                    // Save the new product
                    $product->save();

                    // Get product ID and start import data
                    $post_id = $product->get_id();

                    // Product price Start
                    $productPrice = $this->fetchPriceFromHtml($html);
                    $regular_price = isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0;
                    $sale_price = isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0;
                    logImportVerification('Regular price: ', $regular_price);
                    logImportVerification('Sale price: ', $sale_price);
                    //error_log("Product Price: " . print_r($productPrice, true));


                    // Currency
                    $currency = $this->fetchCurrencyFromHtml($html);
                    logImportVerification('Currency: ', $currency);


                    // Set initial product status based on price availability and stock check
                    if ($regular_price > 0 || $sale_price > 0) {
                        $product_status = 'instock';
                    } else {
                        // If no prices found, proceed with the original stock check
                        $product_status = check_product_stock_status($html);
                        
                        // If the product is 'instock' but has no price, change it to 'outofstock'
                        if ($product_status === 'instock') {
                            $product_status = 'outofstock';
                            logImportVerification('Status changed to outofstock due to zero prices');
                        }
                    }
                    logImportVerification('Final stock status: ', $product_status);


                    // Check if both prices are 0 and the product is out of stock
                    if ($regular_price == 0 && $sale_price == 0 && $product_status === 'outofstock' && get_option('ams_remove_unavailable_products') === 'Yes') {
                        // Refresh product object
                        $_product = wc_get_product($post_id);
                        
                        // Delete the product if it exists
                        if ($_product) {
                            wp_delete_post($post_id, true);
                            $log_message = "Product ID: {$post_id} removed due to being out of stock or having a price of zero.";
                            logImportVerification($log_message);
                            $display_message = esc_html__("Removed!", 'ams-wc-amazon');
                            exit("$display_message");
                        }
                    }


                    // Update price meta
                    update_post_meta($post_id, '_regular_price', $regular_price);
                    if ($sale_price > 0 && $sale_price < $regular_price) {
                        update_post_meta($post_id, '_sale_price', $sale_price);
                        update_post_meta($post_id, '_price', $sale_price);
                    } else {
                        delete_post_meta($post_id, '_sale_price');
                        update_post_meta($post_id, '_price', $regular_price);
                    }
                    // Product price end

                    // Quantity
                    $quantity = 0;
                    if ($qty = $html->find('#availability span', 0)) {
                        $quantity = $this->parseNumberFromString($qty->text());
                        if ($quantity > 0) {
                            update_post_meta($post_id, '_stock', $quantity);
                            update_post_meta($post_id, '_manage_stock', 'yes');
                        } else {
                            update_post_meta($post_id, '_stock', '');
                            update_post_meta($post_id, '_manage_stock', 'no');
                        }
                    } else {
                        update_post_meta($post_id, '_stock', '');
                        update_post_meta($post_id, '_manage_stock', 'no');
                    }

                    // Always update the stock status based on our earlier determination
                    update_post_meta($post_id, '_stock_status', $product_status);

                    logImportVerification('Final Quantity: ', $quantity);
                    logImportVerification('Final Stock Status: ', $product_status);

                    // Category saved
                    if(!empty($product_category)) {
                        wp_set_object_terms($post_id, $product_category, 'product_cat');
                        logImportVerification('Category: ', $product_category);
                    }


                    // Update brand name
                    $brandElement = $html->find('a#bylineInfo', 0) 
                        ?: $html->find('div#bylineInfo_feature_div', 0) 
                        ?: $html->find('div#bondByLine_feature_div', 0);

                    if ($brandElement) {
                        $rawBrandName = trim($brandElement->plaintext);

                        $brandName = str_replace(array('Visit the', 'Store'), '', $rawBrandName);

                        // Trim any extra spaces
                        $brandName = trim($brandName);

                        logImportVerification('Brand: ' . $brandName);

                        $brandTerm = term_exists($brandName, 'product_brand');

                        if (!$brandTerm) {
                            // If the brand doesn't exist, create it
                            $brandTerm = wp_insert_term($brandName, 'product_brand');
                        }

                        if (is_wp_error($brandTerm)) {
                            logImportVerification('Error creating brand term: ' . $brandTerm->get_error_message());
                        } else {
                            // Get the term ID
                            $brandTermId = isset($brandTerm['term_id']) ? $brandTerm['term_id'] : $brandTerm;

                            $productId = $post_id;

                            // Assign the brand to the product
                            wp_set_object_terms($productId, intval($brandTermId), 'product_brand');

                            update_post_meta($productId, '_product_brand', $brandName);

                            logImportVerification('Brand assigned to product successfully.');

                            // Add brand as an attribute
                            $product = wc_get_product($productId);
                            if ($product) {
                                $attributes = $product->get_attributes();

                                // Check if the "Brand" attribute already exists
                                $existingAttribute = false;
                                foreach ($attributes as $key => $attribute) {
                                    if ($attribute->get_name() === 'Brand') {
                                        $attributes[$key]->set_options([$brandName]); // Update the brand value
                                        $existingAttribute = true;
                                        break;
                                    }
                                }

                                // If the attribute does not exist, create it
                                if (!$existingAttribute) {
                                    $brandAttribute = new \WC_Product_Attribute();
                                    $brandAttribute->set_name('Brand'); // Attribute name
                                    $brandAttribute->set_options([$brandName]); // Set the scraped brand name
                                    $brandAttribute->set_visible(true); // Make visible on product page
                                    $brandAttribute->set_variation(false); // Not used for variations
                                    $attributes[] = $brandAttribute; // Add new attribute to the list
                                }

                                // Save the updated attributes back to the product
                                $product->set_attributes($attributes);
                                $product->save(); // Save changes

                                logImportVerification('Brand attribute successfully added to the product.');
                            } else {
                                logImportVerification('Failed to retrieve product for adding brand attribute.');
                            }
                        }
                    } else {
                        logImportVerification('Brand not found in the provided HTML.');
                    }
                    // Update brand name


                    // Update the GTIN, UPC, EAN, or ISBN code
                    $upcElement = $html->find('div#productDetails_expanderTables_depthLeftSections', 0);

                    if ($upcElement) {
                        $upcCode = ''; // Initialize variable

                        // Iterate through table rows to find GTIN, UPC, EAN, or ISBN
                        foreach ($upcElement->find('table.prodDetTable tr') as $row) {
                            $header = $row->find('th', 0); // Get the header cell
                            $value = $row->find('td', 0); // Get the value cell

                            if ($header && $value) {
                                $headerText = trim($header->plaintext);
                                $valueText = trim($value->plaintext);

                                // Check for GTIN, UPC, EAN, or ISBN
                                if (stripos($headerText, 'UPC') !== false || stripos($headerText, 'GTIN') !== false || stripos($headerText, 'EAN') !== false || stripos($headerText, 'ISBN') !== false) {
                                    $upcCode = $valueText; // Extract the value
                                    break; // Exit loop once found
                                }
                            }
                        }

                        if (!empty($upcCode)) {
                            // Save the value to the default WooCommerce GTIN/UPC/EAN/ISBN fields
                            update_post_meta($post_id, '_gtin', $upcCode); // GTIN field
                            update_post_meta($post_id, '_upc', $upcCode);  // UPC field
                            update_post_meta($post_id, '_ean', $upcCode);  // EAN field
                            update_post_meta($post_id, '_isbn', $upcCode); // ISBN field
                        }
                    }
                    // Update the GTIN, UPC, EAN, or ISBN code


                    // short_description saved
                    if(!empty($short_description)) {
                        $product->set_short_description($short_description);
                        logImportVerification('Short description saved.', null);
                    }

                    // additional_description saved
                    if( !empty($additional_description) ) {
                        update_post_meta($post_id, '_ams_additional_information', $additional_description);
                        logImportVerification('Additional description saved.', null);
                    }


                    // Product images + feature image
                    $gallery = $this->fetchImagesFromHtml($html);

                    // Get the image limit from plugin settings
                    $image_limit = get_option('ams_variation_image_limit', 5);

                    // Ensure we have at least one image for the featured image
                    $image_limit = max($image_limit, 1);

                    // Apply the limit to the gallery (including the featured image)
                    $gallery = array_slice($gallery, 0, $image_limit);

                    // Set product feature image
                    $featured_image = array_shift($gallery);

                    if ('Yes' === get_option('ams_remote_amazon_images')) {
                        // Set featured image url
                        if ($featured_image) {
                            $featured_image_id = attach_product_thumbnail_url($post_id, $featured_image, 0);
                            if ($featured_image_id) {
                                set_post_thumbnail($post_id, $featured_image_id);
                            }
                        }
                        
                        // Set gallery images
                        if (count($gallery) > 0) {
                            $gallery_ids = attach_product_thumbnail_url($post_id, $gallery, 1);
                            if (!empty($gallery_ids)) {
                                update_post_meta($post_id, '_product_image_gallery', implode(',', $gallery_ids));
                            }
                        }
                    } else {
                        // Set featured image
                        if ($featured_image) {
                            $featured_image_id = attach_product_thumbnail($post_id, $featured_image, 0);
                            if ($featured_image_id) {
                                set_post_thumbnail($post_id, $featured_image_id);
                            }
                        }
                        
                        // Set gallery images
                        if (count($gallery) > 0) {
                            $gallery_ids = array();
                            foreach ($gallery as $image) {
                                $image_id = attach_product_thumbnail($post_id, $image, 1);
                                if ($image_id) {
                                    $gallery_ids[] = $image_id;
                                }
                            }
                            if (!empty($gallery_ids)) {
                                update_post_meta($post_id, '_product_image_gallery', implode(',', $gallery_ids));
                            }
                        }
                    }
                    // Product images + feature image


                    update_post_meta($post_id, '_purchase_note', '');
                    update_post_meta($post_id, '_visibility', 'visible');
                    update_post_meta($post_id, 'total_sales', '0');
                    update_post_meta($post_id, '_downloadable', 'no');
                    update_post_meta($post_id, '_featured', 'no');
                    update_post_meta($post_id, '_weight', '');
                    update_post_meta($post_id, '_length', '');
                    update_post_meta($post_id, '_width', '');
                    update_post_meta($post_id, '_height', '');
                    update_post_meta($post_id, '_sku', $asin);
                    //update_post_meta($post_id, '_product_attributes', array());
                    update_post_meta($post_id, '_sale_price_dates_from', '');
                    update_post_meta($post_id, '_sale_price_dates_to', '');
                    update_post_meta($post_id, '_sold_individually', '');
                    update_post_meta($post_id, '_backorders', 'no');
                    update_post_meta($post_id, '_wca_amazon_affiliate_asin', $asin);
                    update_post_meta($post_id, '_wca_amazon_affiliate_parent_asin', $parentSku);
                    update_post_meta($post_id, '_region', $region );
                    update_post_meta($post_id, '_import_method', '3' );
                    update_post_meta($post_id, '_ams_product_url', $product_url );
                    update_post_meta($post_id, '_detail_page_url', $product_url );
                    update_post_meta($post_id, '_product_currency', $currency);
                    update_post_meta($post_id, 'ams_last_cron_update', date('Y-m-d H:i:s') );
                    update_post_meta($post_id, 'ams_last_cron_status', 0 );

                    $product->save(); // Save all changes

                    logImportVerification('Simple product created!', null);
                    $message = sprintf(esc_html__(' Success!', 'ams-wc-amazon' ));
                }
            } 
            else {
                $message = sprintf(esc_html__('Content empty!', 'ams-wc-amazon'));
                logImportVerification('Unexpected case: Product content and default content are both empty.', null);
            }
        } else {
            $message = esc_html__('Failed. Skipped!', 'ams-wc-amazon');
            echo $message;
            logImportVerification($message, null);
            wp_die();
        }
        echo wp_kses_post($message);
        wp_die();
    }

    function file_get_contents_utf8($fn) {
        $content = file_get_contents($fn);
         return mb_convert_encoding($content, 'UTF-8',
             mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    }

    public function active_site() {
        $ams_activated_site = get_option('ams_activated_site');
        $url = $_SERVER['HTTP_HOST'];

        if (strtolower($ams_activated_site) != strtolower($url)) {

            $license = sprintf("<h4 class='alert alert-warning'>%s</h4>", 
                esc_html__('You have cloned the website. Please activate the license on the website.', 'ams-wc-amazon')
            );
            echo wp_kses_post($license);
            wp_die();
        } else {
            // Log that the check passed
            //error_log('Site activation check passed.');
        }
    }

    public function get_product_thumbnail_url( $element ) {
        return preg_replace(  '/(._.*_.)/i','.', $element->src );
    }
}