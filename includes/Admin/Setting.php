<?php
namespace Amazon\Affiliate\Admin;
/**
 * Admin setting page and setting handler
 *
 * @package Amazon\Affiliate\Admin
 */
class Setting {

    public function setting_page() {
        $template = __DIR__ . '/views/settings.php';
        if ( file_exists( $template ) ) {
            require_once $template;
        }
    }

    public function general_amazon_setting_handler() {
        check_admin_referer( 'general_amazon_setting_nonce' );
        $access_key_id     = sanitize_text_field( $_POST['access_key_id'] );
        $secret_access_key = sanitize_text_field( $_POST['secret_access_key'] );
        $associate_tag     = sanitize_text_field( $_POST['ams_associate_tag'] );
        $country           = sanitize_text_field( $_POST['ams_amazon_country'] );

        $woocommerce_currency  = sanitize_text_field( $_POST['woocommerce_currency'] );
        update_option( 'woocommerce_currency', $woocommerce_currency );
        
        update_option( 'ams_access_key_id', $access_key_id );
        update_option( 'ams_secret_access_key', $secret_access_key );
        update_option( 'ams_associate_tag', $associate_tag );
        update_option( 'ams_amazon_country', $country );

        wp_redirect( 'admin.php?page=wc-product-setting-page&action=affiliates&tab='.$_POST['action_tab'] );
    }

    public function product_cat_country_setting() {
        // Sanitize and save the posted data
        $country = sanitize_text_field($_POST['ams_amazon_country']);
        update_option('ams_amazon_country', $country);
        
        $ams_default_category = sanitize_text_field($_POST['ams_default_category']);
        update_option('ams_default_category', $ams_default_category);
        
        $woocommerce_currency = sanitize_text_field($_POST['woocommerce_currency']);
        update_option('woocommerce_currency', $woocommerce_currency);
        
        $page_redirect = sanitize_text_field($_POST['page_redirect']);
        
        // Check if the request came from a page with a tab
        $referer = wp_get_referer();
        $parsed_referer = parse_url($referer);
        parse_str($parsed_referer['query'] ?? '', $query_params);
        
        $redirect_url = admin_url('admin.php?page=' . $page_redirect);
        
        // If there was a tab parameter in the referer URL, include it in the redirect
        if (isset($query_params['tab'])) {
            $redirect_url .= '&tab=' . sanitize_text_field($query_params['tab']);
        }
        
        wp_safe_redirect(esc_url_raw($redirect_url));
        exit();
    }

    public function general_ams_product_cron(){
    	update_option( 'product_name_cron', $_POST['product_name_cron'] );
        update_option( 'product_price_cron', $_POST['product_price_cron'] );
        update_option( 'product_sku_cron', $_POST['product_sku_cron'] );
        update_option( 'product_variants_cron', $_POST['product_variants_cron'] );
        update_option( 'product_variant_image_cron', $_POST['product_variant_image_cron'] );
        update_option( 'product_variant_description_cron', $_POST['product_variant_description_cron'] );
        update_option( 'product_out_of_stock_cron', $_POST['product_out_of_stock_cron'] );
        update_option( 'product_description_cron', $_POST['product_description_cron'] );
        update_option( 'product_image_cron', $_POST['product_image_cron'] );
        update_option( 'product_category_cron', $_POST['product_category_cron'] );
        // Check if exists in from 
        if( !isset($_POST['product_tags_cron']) ) {
        	$_POST['product_tags_cron'] = '0';
        }
        if( !isset($_POST['product_review_cron']) ) {
            $_POST['product_review_cron'] = '0';   
        }
        update_option( 'product_tags_cron', $_POST['product_tags_cron'] );
        update_option( 'product_review_cron', $_POST['product_review_cron'] );
        wp_redirect( 'admin.php?page=wc-product-setting-page&action=product_avail&tab='.$_POST['action_tab'] );
        die();
        $product_name_cron =  get_option('product_name_cron');
        $product_tags_cron =  get_option('product_tags_cron');
        $product_variants_cron =  get_option('product_variants_cron');
        $product_sku_cron = get_option('product_sku_cron');
        $product_price_cron =  get_option('product_price_cron');
        $product_category_cron =  get_option('product_category_cron');
        $product_out_of_stock_cron = get_option('product_out_of_stock_cron');
        $product_description_cron = get_option('product_description_cron');
        if ( ! class_exists( 'simple_html_dom' ) ) {
            require_once __DIR__ . '/lib/simplehtmldom/simple_html_dom.php';
        }
        $asins = ams_get_all_products_info();
        $id_asin = array_combine( $asins['id'], $asins['asin'] );
        foreach ( $id_asin as $id => $asin ) {
            $url = sprintf( 'https://www.amazon.%s/dp/%s', get_option( 'ams_amazon_country' ), $asin );
            sleep( rand( 1, 5 ) );
            $product_import = new \Amazon\Affiliate\Admin\ProductsSearchWithoutApi();
            $user_agent = $product_import->user_agent();
            $options = array(
                'http' => array(
                    'method' => "GET",
                    'header' => "Accept-language: en\r\n" .
                        $user_agent
                )
            );
            $context = stream_context_create( $options );
            $response_body = file_get_contents( esc_url_raw( $url ), false, $context );
            $html = new \simple_html_dom();
            $html->load( $response_body );
            $product_status = 'instock';
            if ( $html->find( '#outOfStock .a-color-price', 0 ) ) {
                $product_status = 'outofstock';
            }
            $newBuyBoxPrice =  $html->find('#newBuyBoxPrice');
            $price = $html->find('#price');
            $priceblock_ourprice = $html->find('#priceblock_ourprice');
            $priceblock_dealprice = $html->find('#priceblock_dealprice');
            $priceblock_saleprice = $html->find('#priceblock_saleprice');
            if ( isset( $newBuyBoxPrice[0] )){
                $amount = $newBuyBoxPrice[0]->innertext;
            } 
            if ( isset( $price[0] )){
                $amount = $price[0]->innertext;
            } 
            if ( isset( $priceblock_ourprice[0] ) ) {
               $amount = $priceblock_ourprice[0]->innertext;
            } 
            if ( isset( $priceblock_saleprice[0] ) ) {
               $amount = $priceblock_saleprice[0]->innertext;
            }
            if ( isset( $priceblock_dealprice[0] ) ) {
               $amount = $priceblock_dealprice[0]->innertext;
            }
            $amount = preg_replace("/[^0-9 .]/", "", $amount );
        $amount = str_replace( ',', '.', $amount );
        $amount = strtok( $amount, ' ' );
        if($product_sku_cron){
            update_post_meta($id, '_sku', $asin );
        }
        if($product_out_of_stock_cron){
            update_post_meta( $id, '_stock_status', $product_status );
        }
        if($product_price_cron){
            update_post_meta( $id, '_price', $amount );
            update_post_meta( $id, '_regular_price', $amount );
            update_post_meta( $id, '_sale_price', $amount);
        }

        if($product_variants_cron){
            global $wpdb;           
            $product = wc_get_product($id);
            $children_ids = $product->get_children();
            $meta_prefix = $wpdb->prefix . 'options';    
            $query =  "DELETE FROM $meta_prefix
                WHERE (option_name LIKE '_transient_wc_var_prices_".$id."'
                OR option_name LIKE '_transient_timeout_wc_var_prices_".$id."')";
            $wpdb->query( $query);
            if(isset($children_ids) && !empty($children_ids)){
                foreach ($children_ids as $key => $children_id) {
                    update_post_meta( $children_id, '_price', sanitize_text_field($amount) );
                    update_post_meta( $children_id, '_sale_price', $amount);
                }
            }
        }
        wc_delete_product_transients($id);
    }
}


    public function general_setting() {
        check_admin_referer('general_setting_nonce');
        
        // Existing variables
        $product_per_page            = sanitize_text_field($_POST['product_per_page']);
        $buy_now_label               = sanitize_text_field($_POST['buy_now_label']);
        $buy_action_btn              = sanitize_text_field($_POST['buy_action_btn']);
        $enable_no_follow_link       = isset($_POST['enable_no_follow_link']) && $_POST['enable_no_follow_link'] === 'nofollow' ? 'nofollow' : 'follow';
        $remove_unavailable_products = isset($_POST['remove_unavailable_products']) && $_POST['remove_unavailable_products'] === 'Yes' ? 'Yes' : 'No';
        $fast_product_importer       = isset($_POST['fast_product_importer']) && $_POST['fast_product_importer'] === 'Yes' ? 'Yes' : 'No';
        $ams_product_import_status   = sanitize_text_field($_POST['ams_product_import_status']);
        $ams_default_category        = sanitize_text_field($_POST['ams_default_category']);
        $checkout_mass_redirected    = sanitize_text_field($_POST['checkout_mass_redirected']);
        $checkout_redirected_seconds = sanitize_text_field($_POST['checkout_redirected_seconds']);
        $remote_amazon_images        = sanitize_text_field($_POST['remote_amazon_images']);
        $product_thumbnail_size      = sanitize_text_field($_POST['product_thumbnail_size']);
        $percentage_profit           = sanitize_text_field($_POST['percentage_profit']);

        $variation_image_limit = isset($_POST['variation_image_limit']) ? intval($_POST['variation_image_limit']) : 5;
        $variation_image_limit = min(max($variation_image_limit, 1), 10);

        $enable_amazon_review        = isset($_POST['enable_amazon_review']) ? sanitize_text_field($_POST['enable_amazon_review']) : '';
        $single_import_review_limit  = isset($_POST['single_import_review_limit']) ? sanitize_text_field($_POST['single_import_review_limit']) : '10';
        $multiple_import_review_limit = isset($_POST['multiple_import_review_limit']) ? sanitize_text_field($_POST['multiple_import_review_limit']) : '10';

        $ams_results_limit           = sanitize_text_field($_POST['ams_results_limit'] ?? '50');

        $category_min_depth          = isset($_POST['category_min_depth']) ? intval($_POST['category_min_depth']) : 1;
        $category_max_depth          = isset($_POST['category_max_depth']) ? intval($_POST['category_max_depth']) : 10;
        
        // ScraperAPI variables
        $scraper_api_key             = sanitize_text_field($_POST['scraper_api_key'] ?? '');
        $scraper_api_is_active       = isset($_POST['scraper_api_is_active']) ? '1' : '0';
        $scraper_api_on_update       = isset($_POST['scraper_api_on_update']) ? '1' : '0';
        
        // ScrapingAnt variables
        $scrapingant_api_key         = sanitize_text_field($_POST['scrapingant_api_key'] ?? '');
        $scrapingant_is_active       = isset($_POST['scrapingant_is_active']) ? '1' : '0';
        $scrapingant_on_update       = isset($_POST['scrapingant_on_update']) ? '1' : '0';
        
        $variation_image_meta_key = sanitize_text_field($_POST['variation_image_meta_key'] ?? '_product_image_gallery');
        $variation_limit = isset($_POST['variation_limit']) ? intval($_POST['variation_limit']) : 5;
        $ams_image_fit = sanitize_text_field($_POST['ams_image_fit'] ?? 'cover');

        // Get the Custom Theme Hook Settings
        $use_custom_button = isset($_POST['use_custom_button']) ? '1' : '0';
        $theme_hook = sanitize_text_field($_POST['theme_hook'] ?? ''); // Default empty

        // Get the enable/disable option for page speed test
        $enable_page_speed_test = isset($_POST['enable_page_speed_test']) ? '1' : '0';

        // Get the selected style
        $page_speed_test_style = sanitize_text_field($_POST['page_speed_test_style'] ?? 'style1');

        // Enable/Disable reviewer image and review title
        $enable_reviewer_image = isset($_POST['enable_reviewer_image']) ? '1' : '0';
        $enable_review_title = isset($_POST['enable_review_title']) ? '1' : '0';
        $enable_last_updated_date = isset($_POST['enable_last_updated_date']) ? '1' : '0';
        $enable_custom_message = isset($_POST['enable_custom_message']) ? '1' : '0';

        $last_updated_custom_message = isset($_POST['last_updated_custom_message']) 
            ? sanitize_textarea_field($_POST['last_updated_custom_message']) 
            : 'Important Notice: Product details may change. Please check regularly for updates.';

        // Get the selected style for the Last Updated Notice
        $last_updated_notice_style = isset($_POST['last_updated_notice_style']) 
            ? sanitize_text_field($_POST['last_updated_notice_style']) 
            : 'style1';

        $enable_daily_cron = isset($_POST['enable_daily_cron']) ? '1' : '0';
        $sidebar_autoclose = isset($_POST['sidebar_autoclose']) ? '1' : '0';

        // Enable/Disable and message options
        $enable_product_last_updated = isset($_POST['enable_product_last_updated']) ? '1' : '0';
        $product_last_updated_message = sanitize_textarea_field($_POST['product_last_updated_message']);
        $enable_global_last_updated = isset($_POST['enable_global_last_updated']) ? '1' : '0';
        $global_last_updated_message = sanitize_textarea_field($_POST['global_last_updated_message']);
        $enable_custom_notification = isset($_POST['enable_custom_notification']) ? '1' : '0';
        $custom_notification_message = sanitize_textarea_field($_POST['custom_notification_message']);
        $message_alignment = sanitize_text_field($_POST['message_alignment']);
        $enable_legal_notice = isset($_POST['enable_legal_notice']) ? '1' : '0';
        $legal_notice_text = sanitize_textarea_field($_POST['legal_notice_text'] ?? 'Affiliate Products | Advertisement | Sponsored');

        $product_category_cron = isset($_POST['product_category_cron']) ? '1' : '0';

        $enable_clean_completed_actions = isset($_POST['enable_clean_completed_actions']) ? '1' : '0';
        $enable_clean_action_logs = isset($_POST['enable_clean_action_logs']) ? '1' : '0';

        // Update existing options
        update_option('ams_product_per_page', $product_per_page);
        update_option('ams_buy_now_label', $buy_now_label);
        update_option('ams_buy_action_btn', $buy_action_btn);
        update_option('ams_enable_no_follow_link', $enable_no_follow_link);
        update_option('ams_remove_unavailable_products', $remove_unavailable_products);
        update_option('ams_fast_product_importer', $fast_product_importer);
        update_option('ams_product_import_status', $ams_product_import_status);
        update_option('ams_default_category', $ams_default_category);
        update_option('product_category_cron', $product_category_cron);
        update_option('ams_checkout_mass_redirected', $checkout_mass_redirected);
        update_option('ams_checkout_redirected_seconds', $checkout_redirected_seconds);
        update_option('ams_remote_amazon_images', $remote_amazon_images);
        update_option('ams_product_thumbnail_size', $product_thumbnail_size);
        update_option('ams_percentage_profit', $percentage_profit);
        update_option('ams_sidebar_autoclose', $sidebar_autoclose);

        update_option('enable_amazon_review', $enable_amazon_review);
        update_option('single_import_review_limit', $single_import_review_limit);
        update_option('multiple_import_review_limit', $multiple_import_review_limit);
        update_option('enable_reviewer_image', $enable_reviewer_image);
        update_option('enable_review_title', $enable_review_title);

        update_option('ams_results_limit', $ams_results_limit);
        update_option('ams_variation_image_limit', $variation_image_limit);
        update_option('ams_variation_limit', $variation_limit);
        update_option('variation_image_meta_key', $variation_image_meta_key);
        update_option('ams_image_fit', $ams_image_fit);
        update_option('ams_category_min_depth', $category_min_depth);
        update_option('ams_category_max_depth', $category_max_depth);
        

        // Save Enable/Disable and message options
        update_option('enable_product_last_updated', $enable_product_last_updated);
        update_option('product_last_updated_message', $product_last_updated_message);
        update_option('enable_global_last_updated', $enable_global_last_updated);
        update_option('global_last_updated_message', $global_last_updated_message);
        update_option('enable_custom_notification', $enable_custom_notification);
        update_option('custom_notification_message', $custom_notification_message);
        update_option('message_alignment', $message_alignment);
        update_option('enable_legal_notice', $enable_legal_notice);
        update_option('legal_notice_text', $legal_notice_text);
    
        // Update the option in the database
        update_option('enable_page_speed_test', $enable_page_speed_test);
        // Update the style in the database
        update_option('page_speed_test_style', $page_speed_test_style);

        // Update Custom Theme Hook Settings
        update_option('ams_use_custom_button', $use_custom_button);
        update_option('ams_theme_hook', $theme_hook);

        // Update custom message on product page
        update_option('enable_last_updated_date', $enable_last_updated_date);
        update_option('enable_custom_message', $enable_custom_message);
        update_option('last_updated_custom_message', $last_updated_custom_message);
        update_option('last_updated_notice_style', $last_updated_notice_style);

        
        // Update ScraperAPI options
        update_option('ams_scraper_api_key', $scraper_api_key);
        update_option('ams_scraper_api_is_active', $scraper_api_is_active);
        update_option('ams_scraper_api_on_update', $scraper_api_on_update);
        
        // Update ScrapingAnt options
        update_option('ams_scrapingant_api_key', $scrapingant_api_key);
        update_option('ams_scrapingant_is_active', $scrapingant_is_active);
        update_option('ams_scrapingant_on_update', $scrapingant_on_update);

        update_option('enable_clean_completed_actions', $enable_clean_completed_actions);
        update_option('enable_clean_action_logs', $enable_clean_action_logs);


        // Save the Enable/Disable Daily Cron setting
        update_option('enable_daily_cron', $enable_daily_cron);

        // Manage cron job scheduling
        if ($enable_daily_cron === '1') {
            if (!wp_next_scheduled('ams_daily_cron_event')) {
                wp_schedule_event(time(), 'ams_every_day', 'ams_daily_cron_event');
            }
        } else {
            if (wp_next_scheduled('ams_daily_cron_event')) {
                wp_clear_scheduled_hook('ams_daily_cron_event');
            }
        }
        
        wp_redirect('admin.php?page=wc-product-setting-page&tab='.$_POST['action_tab']);
        exit;
    }


    public function get_option( $name ) {
        $option =  get_option( $name );
        return  $option;
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

    public function test_api() {
        $accessKey = get_option('ams_access_key_id');
        $secretKey = get_option('ams_secret_access_key');
        $associateTag = get_option('ams_associate_tag');
        $selectedLocale = get_option('ams_amazon_country');

        // Check if all required fields are filled
        if (empty($accessKey) || empty($secretKey) || empty($associateTag)) {
            $message = '<div class="alert alert-danger" role="alert">Error: Please fill in all required Amazon API settings (Access Key, Secret Key, and Associate Tag).</div>';
            echo wp_kses_post($message);
            wp_die();
        }

        $regions = ams_get_amazon_regions();
        list($detectedLocale, $errorMessage) = $this->detect_correct_store($accessKey, $secretKey, $associateTag, $regions);

        if ($detectedLocale) {
            $detectedStoreName = $this->get_store_name($detectedLocale, $regions);
            $selectedStoreName = $this->get_store_name($selectedLocale, $regions);

            if ($detectedLocale !== $selectedLocale) {
                $message = sprintf(
                    '<div class="alert alert-warning" role="alert">
                        Your API credentials are valid for the %s (%s) store, but you\'ve selected %s (%s). 
                        <strong>Recommendation:</strong> Change your country selection to %s (%s) in the settings.
                    </div>',
                    esc_html(strtoupper($detectedLocale)),
                    esc_html($detectedStoreName),
                    esc_html(strtoupper($selectedLocale)),
                    esc_html($selectedStoreName),
                    esc_html(strtoupper($detectedLocale)),
                    esc_html($detectedStoreName)
                );
            } else {
                $message = sprintf(
                    '<div class="alert alert-success" role="alert">
                        Your Amazon API credentials are valid for the %s (%s) store. 
                        Your current country selection is correct.
                    </div>',
                    esc_html(strtoupper($detectedLocale)),
                    esc_html($detectedStoreName)
                );

                // Fetch additional API details
                $apiDetails = $this->get_api_details($accessKey, $secretKey, $associateTag, $detectedLocale, $regions[$detectedLocale]);
                if ($apiDetails) {
                    $message .= $this->format_api_details($apiDetails);
                }
            }
        } else {
            $decodedError = json_decode($errorMessage);
            if ($decodedError && isset($decodedError->__type) && isset($decodedError->Errors)) {
                $amazonError = $decodedError->Errors[0]->Message ?? 'Unknown Amazon API error';
                $message = sprintf(
                    '<div class="alert alert-danger" role="alert">
                        Unable to validate your API credentials for any Amazon store. 
                        Please check your Access Key, Secret Key, and Associate Tag.
                        <br><strong>Error details:</strong> %s
                    </div>',
                    esc_html($amazonError)
                );
            } else {
                $message = sprintf(
                    '<div class="alert alert-danger" role="alert">
                        Unable to validate your API credentials for any Amazon store. 
                        Please check your Access Key, Secret Key, and Associate Tag.
                        <br><strong>Error details:</strong> %s
                    </div>',
                    esc_html($errorMessage)
                );
            }
        }

        echo wp_kses_post($message);
        wp_die();
    }

    private function test_credentials_for_store($accessKey, $secretKey, $associateTag, $locale, $regionInfo) {
        $keyword = 'test';
        $marketplace = "www.amazon.{$locale}";
        $serviceName = 'ProductAdvertisingAPI';
        $region = $regionInfo['RegionCode'];
        $host = $regionInfo['Host'];

        $payloadArr = array(
            'Keywords' => $keyword,
            'Resources' => array('ItemInfo.Title'),
            'PartnerTag' => $associateTag,
            'PartnerType' => 'Associates',
            'Marketplace' => $marketplace,
            'ItemCount' => 1
        );

        $payload = json_encode($payloadArr);
        $uri_path = "/paapi5/searchitems";

        try {
            $api = new \Amazon\Affiliate\Api\Amazon_Product_Api($accessKey, $secretKey, $region, $serviceName, $uri_path, $payload, $host, 'SearchItems');
            $response = $api->do_request();

            if (isset($response->SearchResult)) {
                return true;
            } elseif (isset($response->__type) && isset($response->Errors)) {
                // This captures the Amazon error message structure we saw earlier
                return json_encode($response);
            }
        } catch (Exception $e) {
            // Capture any other exceptions
            return $e->getMessage();
        }

        return false;
    }

    private function detect_correct_store($accessKey, $secretKey, $associateTag, $regions) {
        $lastErrorMessage = '';
        foreach ($regions as $locale => $regionInfo) {
            $result = $this->test_credentials_for_store($accessKey, $secretKey, $associateTag, $locale, $regionInfo);
            if ($result === true) {
                return array($locale, '');
            } elseif (is_string($result)) {
                $lastErrorMessage = $result;
            }
        }
        return array(null, $lastErrorMessage);
    }

    private function get_store_name($locale, $regions) {
        $countryNames = [
            'us' => 'United States',
            'uk' => 'United Kingdom',
            'de' => 'Germany',
            'fr' => 'France',
            'jp' => 'Japan',
            'ca' => 'Canada',
            'it' => 'Italy',
            'es' => 'Spain',
            'in' => 'India',
            'br' => 'Brazil',
            'mx' => 'Mexico',
            'au' => 'Australia',
            'sg' => 'Singapore',
            'ae' => 'United Arab Emirates',
            'nl' => 'Netherlands',
            'sa' => 'Saudi Arabia',
            'se' => 'Sweden',
            'pl' => 'Poland',
            'tr' => 'Turkey',
        ];

        $locale = strtolower($locale);
        if (isset($countryNames[$locale])) {
            return $countryNames[$locale] . ' (Amazon.' . $locale . ')';
        }
        
        // Fallback to using the name from regions if available
        if (isset($regions[$locale]['Name'])) {
            return $regions[$locale]['Name'] . ' (Amazon.' . $locale . ')';
        }
        
        // If all else fails, return the locale code
        return 'Amazon.' . $locale;
    }

    private function get_api_details($accessKey, $secretKey, $associateTag, $locale, $regionInfo) {
        $keyword = 'bestseller'; // Using a more general keyword that should return results in any locale
        $marketplace = "www.amazon.{$locale}";
        $serviceName = 'ProductAdvertisingAPI';
        $region = $regionInfo['RegionCode'];
        $host = $regionInfo['Host'];

        $payloadArr = array(
            'Keywords' => $keyword,
            'Resources' => array('ItemInfo.Title', 'Offers.Listings.Price'),
            'PartnerTag' => $associateTag,
            'PartnerType' => 'Associates',
            'Marketplace' => $marketplace,
            'ItemCount' => 1
        );

        $payload = json_encode($payloadArr);
        $uri_path = "/paapi5/searchitems";

        try {
            $api = new \Amazon\Affiliate\Api\Amazon_Product_Api($accessKey, $secretKey, $region, $serviceName, $uri_path, $payload, $host, 'SearchItems');
            $response = $api->do_request();

            if (isset($response->SearchResult)) {
                return [
                    'totalResults' => $response->SearchResult->TotalResultCount ?? 0,
                    'searchURL' => $response->SearchResult->SearchURL ?? '',
                    'keyword' => $keyword
                ];
            }
        } catch (Exception $e) {
            // If an exception is thrown, we'll return null
        }

        return null;
    }

    private function format_api_details($apiDetails) {
        $containerStyle = 'background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 20px; margin-top: 20px; font-family: Arial, sans-serif;';
        $headingStyle = 'color: #0056b3; margin-top: 0; margin-bottom: 15px; font-size: 1.2em;';
        $paragraphStyle = 'margin-bottom: 15px; line-height: 1.5;';
        $listStyle = 'list-style-type: none; padding-left: 0;';
        $listItemStyle = 'margin-bottom: 10px;';
        $strongStyle = 'color: #495057;';
        $linkStyle = 'color: #0056b3; text-decoration: none;';
        $keywordStyle = 'background-color: #e9ecef; padding: 2px 5px; border-radius: 3px; font-family: monospace;';

        $formattedDetails = "<div style='{$containerStyle}'>";
        $formattedDetails .= "<h4 style='{$headingStyle}'>API Test Results</h4>";
        $formattedDetails .= "<p style='{$paragraphStyle}'>We performed a test search using the keyword <span style='{$keywordStyle}'>" . esc_html($apiDetails['keyword']) . "</span> to verify your API functionality.</p>";
        $formattedDetails .= "<ul style='{$listStyle}'>";
        $formattedDetails .= "<li style='{$listItemStyle}'><strong style='{$strongStyle}'>Total Results Found:</strong> " . esc_html(number_format($apiDetails['totalResults'])) . "</li>";
        if (!empty($apiDetails['searchURL'])) {
            $formattedDetails .= "<li style='{$listItemStyle}'><strong style='{$strongStyle}'>Test Search Results:</strong> <a href='" . esc_url($apiDetails['searchURL']) . "' target='_blank' style='{$linkStyle}'>View on Amazon</a></li>";
        }
        $formattedDetails .= "</ul>";
        $formattedDetails .= "<p style='{$paragraphStyle}'>These results confirm that your Amazon Product Advertising API is working correctly and able to fetch product data.</p>";
        $formattedDetails .= "</div>";

        return $formattedDetails;
    }
}