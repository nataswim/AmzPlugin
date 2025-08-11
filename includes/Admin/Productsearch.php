<?php

namespace Amazon\Affiliate\Admin;
use Amazon\Affiliate\Admin;


/**
 * Class Productsearch
 *
 * @package Amazon\Affiliate\Admin
 */


class Productsearch extends ImportProducts

{

    public function product_page() {
        $this->get_amazon_cat();
        $this->get_wc_terms();
        $template = __DIR__ . '/views/product.php';
        if (file_exists($template)) {
            require_once $template;
        }
    }

    public function get_option($name) {
        $option = get_option($name);
        return $option;
    }

    public function get_wc_terms() {
        $categories = get_terms(array(
            'hide_empty' => false,
        ));

        $cat = array();

        foreach ($categories as $row) {
            if ('product_cat' === $row->taxonomy) {
                $cat[] = array(
                    'term_id' => $row->term_id,
                    'name' => $row->name,
                );
            }
        }
        return array_reverse($cat);

    }

    public function active_site() {
        $ams_activated_site = get_option('ams_activated_site');
        $url = $_SERVER['HTTP_HOST'];

        if (strtolower($ams_activated_site) != strtolower($url)) {
            $license = sprintf("<h4 class='wca-warning'>%s</h4>", esc_html__('You have cloned the website Please activate the license on the website.', 'ams-wc-amazon'));
            echo wp_kses_post($license);
            wp_die();
        }
    }

    public function search_products() {
        check_admin_referer('wca_search_product');
        wca_add_products_search_count();

        $results = [];

        if ('keyword' === $_POST['wca_search_by']) {
            $keyword = sanitize_text_field($_POST['keyword']);
            $sort_by = sanitize_text_field($_POST['sort_by']);
            $item_page = sanitize_text_field($_POST['item_page']);
            $amazon_cat = sanitize_text_field($_POST['ams_amazon_cat']);
            
            $results = $this->get_keyword_products($keyword, $item_page, $sort_by, $amazon_cat);
            $results = is_array($results) ? $results : [];
        } else {
            $asin_id = sanitize_text_field($_POST['asin_id']);
            $searched_asins = array_values(array_filter(array_map('trim', explode(',', $asin_id)), 'strlen'));
            
            $valid_asins = array_filter($searched_asins, function($asin) {
                return strlen($asin) == 10 && preg_match('/^[0-9A-Z]{10}$/', $asin);
            });

            if (empty($valid_asins)) {
                $this->display_no_results_message("No valid ASINs found. Please check your input and try again.");
                wp_die();
            }

            $valid_asins = array_slice($valid_asins, 0, 10);
            $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $per_page = 10;
            $offset = ($current_page - 1) * $per_page;
            $current_page_asins = array_slice($valid_asins, $offset, $per_page);

            $results = $this->get_item_id_products(implode(',', $current_page_asins));
        }

        if (empty($results)) {
            $this->display_no_results_message();
        } else {
            foreach ($results as $row) {
                $this->display_product_card($row);
            }

            // Add pagination information if necessary
            if (isset($valid_asins)) {
                $total_pages = ceil(count($valid_asins) / $per_page);
                echo '<div class="pagination-info" data-total-pages="' . $total_pages . '" data-current-page="' . $current_page . '"></div>';

                if (count($valid_asins) < count($searched_asins)) {
                    $skipped_count = count($searched_asins) - count($valid_asins);
                    echo '<div class="alert alert-info" role="alert">' . $skipped_count . ' invalid ASIN(s) were skipped.</div>';
                }
            }
        }

        wp_die();
    }

    private function display_no_results_message($custom_message = null) {
        $message = $custom_message ?? "We couldn't find any products matching your search criteria. Please try adjusting your search terms or filters.";
        ?>
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">No Results Found</h4>
                <p><?php echo esc_html($message); ?></p>
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
        <?php
    }

    private function display_product_card($row) {
        $asin = $row->ASIN;
        $parentASIN = isset($row->ParentASIN) ? $row->ParentASIN : null;
        $detail_page_url = $row->DetailPageURL;
        $image = $row->Images->Primary->Medium->URL ?? '';
        $amount = $row->Offers->Listings[0]->Price->DisplayAmount ?? $row->Offers->Summaries[0]->LowestPrice->DisplayAmount ?? '';
        $saving_amount = $row->Offers->Listings[0]->SavingBasis->DisplayAmount ?? '';
        $title = $row->ItemInfo->Title->DisplayValue ?? '';

        // Use parent ASIN for import if available, otherwise use the product's ASIN
        $import_asin = $parentASIN ?: $asin;
        ?>
        <div class="col-lg-2 col-md-4 col-sm-6 p-1">
            <div class="card">
                <div class="product-item card-body" style="min-height: 452px">
                    <!--Ribbon-->
                    <div class="ribbon-wrapper">
                        <div class="ribbon"> Trending </div>
                    </div>
                    <!--//Ribbon-->
                    <?php if (!empty($image)) : ?>
                        <img src="<?php echo esc_attr($image); ?>" alt="<?php echo esc_attr($title); ?>">
                    <?php else : ?>
                        <div class="text-center mt-3">
                            <i class="fas fa-image fa-3x text-muted"></i>
                            <p>Image not available</p>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo esc_attr($detail_page_url); ?>" target="_blank" title="<?php echo esc_attr($title); ?>" class="product-item__name">
                        <?php echo esc_html(substr($title, 0, 30) . '...'); ?>
                    </a>
                    <div class="product-item__description">
                    <?php if(!empty($amount)) : ?>
                        <span><?php echo esc_html($amount); ?> 
                        <?php if (!empty($saving_amount)) : ?>
                            <small class="wca-delete"> <?php echo esc_html($saving_amount); ?></small>
                        <?php endif; ?>
                        </span>
                    <?php else : ?>
                        <span class="text-muted">Price Not Available</span>
                    <?php endif; ?>
                        <a href="<?php echo esc_attr($detail_page_url); ?>" target="_blank" class="d-block"> <small> View Product </small></a>
                    </div>
                    <div class="product-item__price">
                    <?php
                        $ams_all_asin = ams_get_all_products_info_with_parent();
                        if (in_array($import_asin, $ams_all_asin['asin'])) {
                        ?>
                            <button id="import_btn" disabled type="button" class="btn btn-block btn-primary btn-sm py-2 mt-2 d-block text-center imported wca-add-to-imported">
                                <?php echo esc_html__('Already Imported', 'ams-wc-amazon'); ?>
                            </button>
                        <?php
                        } else {
                        ?>
                            <button id="import_btn" type="button" class="btn btn-block btn-primary mt-3 d-block text-center imported wca-add-to-import" data-asin="<?php echo esc_attr($import_asin); ?>">
                                <?php echo esc_html__('Import Product', 'ams-wc-amazon'); ?>
                            </button>
                        <?php
                        }
                    ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function display_not_found_card($asin) {
        ?>
        <div class="col-lg-2 col-md-4 col-sm-6 p-1">
            <div class="card">
                <div class="product-item card-body" style="min-height: 452px">
                    <div class="text-center mt-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Product Not Found</h5>
                        <p>ASIN: <?php echo esc_html($asin); ?></p>
                        <p class="text-muted">This product may no longer be available or may have incomplete data.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_item_id_products($asin_id) {

        $space_remove_asin_id = str_replace(' ', '', $asin_id);
        $array_asin_id = explode(',', $space_remove_asin_id);
        $locale = get_option('ams_amazon_country');
        $regions = ams_get_amazon_regions();
        $marketplace = 'www.amazon.' . get_option('ams_amazon_country');
        $serviceName = 'ProductAdvertisingAPI';
        $region = $regions[$locale]['RegionCode'];
        $accessKey = get_option('ams_access_key_id');
        $secretKey = get_option('ams_secret_access_key');
        $payloadArr = array();
        $payloadArr['ItemIds'] = $array_asin_id;
        $payloadArr['Resources'] = array("Images.Primary.Small", "Images.Primary.Medium", "Images.Primary.Large", "Images.Variants.Small", "Images.Variants.Medium", "Images.Variants.Large", "ItemInfo.ByLineInfo", "ItemInfo.ContentInfo", "ItemInfo.ContentRating", "ItemInfo.Classifications", "ItemInfo.ExternalIds", "ItemInfo.Features", "ItemInfo.ManufactureInfo", "ItemInfo.ProductInfo", "ItemInfo.TechnicalInfo", "ItemInfo.Title", "ItemInfo.TradeInInfo", "Offers.Listings.Availability.MaxOrderQuantity", "Offers.Listings.Availability.Message", "Offers.Listings.Availability.MinOrderQuantity", "Offers.Listings.Availability.Type", "Offers.Listings.Condition", "Offers.Listings.Condition.ConditionNote", "Offers.Listings.Condition.SubCondition", "Offers.Listings.DeliveryInfo.IsAmazonFulfilled", "Offers.Listings.DeliveryInfo.IsFreeShippingEligible", "Offers.Listings.DeliveryInfo.IsPrimeEligible", "Offers.Listings.DeliveryInfo.ShippingCharges", "Offers.Listings.IsBuyBoxWinner", "Offers.Listings.LoyaltyPoints.Points", "Offers.Listings.MerchantInfo", "Offers.Listings.Price", "Offers.Listings.ProgramEligibility.IsPrimeExclusive", "Offers.Listings.ProgramEligibility.IsPrimePantry", "Offers.Listings.Promotions", "Offers.Listings.SavingBasis", "Offers.Summaries.HighestPrice", "Offers.Summaries.LowestPrice", "Offers.Summaries.OfferCount");
        $payloadArr['PartnerTag'] = get_option('ams_associate_tag');
        $payloadArr['PartnerType'] = 'Associates';
        $payloadArr['Marketplace'] = $marketplace;
        $payloadArr['Operation'] = 'GetItems';
        $payload = json_encode($payloadArr);
        $host = $regions[$locale]['Host'];
        $uri_path = "/paapi5/getitems";
        $api = new \Amazon\Affiliate\Api\Amazon_Product_Api($accessKey, $secretKey, $region, $serviceName, $uri_path, $payload, $host, 'GetItems');
        $response = $api->do_request();
        $results = @$response->ItemsResult->Items;
        return $results;
    }

    private function is_valid_product($item) {
        $valid = isset($item->ASIN) &&
                 isset($item->ItemInfo->Title->DisplayValue) &&
                 isset($item->Images->Primary->Medium->URL) &&
                 (
                     (isset($item->Offers->Listings) && !empty($item->Offers->Listings)) ||
                     (isset($item->Offers->Summaries) && !empty($item->Offers->Summaries))
                 );

        if (!$valid) {
            $missing = array();
            if (!isset($item->ASIN)) $missing[] = 'ASIN';
            if (!isset($item->ItemInfo->Title->DisplayValue)) $missing[] = 'Title';
            if (!isset($item->Images->Primary->Medium->URL)) $missing[] = 'Image URL';
            if (!(isset($item->Offers->Listings) && !empty($item->Offers->Listings)) &&
                !(isset($item->Offers->Summaries) && !empty($item->Offers->Summaries))) {
                $missing[] = 'Offers';
            }
        }

        return $valid;
    }

    public function get_keyword_products($keyword, $item_page, $sort_by, $amazon_cat) {
        $ams_product_per_page = get_option('ams_product_per_page');
        $locale = get_option('ams_amazon_country');
        $regions = ams_get_amazon_regions();
        $marketplace = 'www.amazon.' . get_option('ams_amazon_country');
        $serviceName = 'ProductAdvertisingAPI';
        $region = $regions[$locale]['RegionCode'];
        $accessKey = get_option('ams_access_key_id');
        $secretKey = get_option('ams_secret_access_key');
        $payloadArr = array();
        $payloadArr['Keywords'] = $keyword;
        $payloadArr['Resources'] = array('CustomerReviews.Count', 'CustomerReviews.StarRating', 'Images.Primary.Small', 'Images.Primary.Medium', 'Images.Primary.Large', 'Images.Variants.Small', 'Images.Variants.Medium', 'Images.Variants.Large', 'ItemInfo.ByLineInfo', 'ItemInfo.ContentInfo', 'ItemInfo.ContentRating', 'ItemInfo.Classifications', 'ItemInfo.ExternalIds', 'ItemInfo.Features', 'ItemInfo.ManufactureInfo', 'ItemInfo.ProductInfo', 'ItemInfo.TechnicalInfo', 'ItemInfo.Title', 'ItemInfo.TradeInInfo', 'Offers.Listings.Availability.MaxOrderQuantity', 'Offers.Listings.Availability.Message', 'Offers.Listings.Availability.MinOrderQuantity', 'Offers.Listings.Availability.Type', 'Offers.Listings.Condition', 'Offers.Listings.Condition.SubCondition', 'Offers.Listings.DeliveryInfo.IsAmazonFulfilled', 'Offers.Listings.DeliveryInfo.IsFreeShippingEligible', 'Offers.Listings.DeliveryInfo.IsPrimeEligible', 'Offers.Listings.DeliveryInfo.ShippingCharges', 'Offers.Listings.IsBuyBoxWinner', 'Offers.Listings.LoyaltyPoints.Points', 'Offers.Listings.MerchantInfo', 'Offers.Listings.Price', 'Offers.Listings.ProgramEligibility.IsPrimeExclusive', 'Offers.Listings.ProgramEligibility.IsPrimePantry', 'Offers.Listings.Promotions', 'Offers.Listings.SavingBasis', 'Offers.Summaries.HighestPrice', 'Offers.Summaries.LowestPrice', 'Offers.Summaries.OfferCount', 'ParentASIN', 'SearchRefinements');
        $payloadArr["ItemCount"] = (int) $ams_product_per_page;
        $payloadArr["ItemPage"] = (int) $item_page;
        $payloadArr["SortBy"] = $sort_by;
        $payloadArr["SearchIndex"] = $amazon_cat;
        $payloadArr['PartnerTag'] = get_option('ams_associate_tag');
        $payloadArr['PartnerType'] = 'Associates';
        $payloadArr['Availability'] = 'Available';
        $payloadArr['Marketplace'] = $marketplace;
        $payload = json_encode($payloadArr);
        $host = $regions[$locale]['Host'];
        $uri_path = "/paapi5/searchitems";
        $api = new \Amazon\Affiliate\Api\Amazon_Product_Api($accessKey, $secretKey, $region, $serviceName, $uri_path, $payload, $host, 'SearchItems');
        $response = $api->do_request();
        $results = isset($response->SearchResult) && $response->SearchResult ? $response->SearchResult->Items : [];
        return $results;
    }

    public function get_amazon_cat() {
        $all_country_cat = ams_amazon_departments();
        $country = get_option('ams_amazon_country');
        $cat = $all_country_cat[$country];
        return $cat;
    }

    public function import_products() {
        $nonce = sanitize_text_field($_POST['_wpnonce']);
        
        // Check if nonce is invalid
        if (!wp_verify_nonce($nonce, 'wca_search_product')) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('Invalid nonce, please refresh your screen and try again.', 'ams-wc-amazon')
            );
            wp_send_json_error(new \WP_Error("403", $message));
        }

        if (!isset($_FILES["csv"]) || $_FILES["csv"]["error"] !== UPLOAD_ERR_OK) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('There is no file to upload or an error occurred during upload.', 'ams-wc-amazon')
            );
            wp_send_json_error(new \WP_Error("400", $message));
        }

        $filepath = $_FILES['csv']['tmp_name'];

        if (!file_exists($filepath) || !is_readable($filepath)) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('The uploaded file is not accessible.', 'ams-wc-amazon')
            );
            wp_send_json_error(new \WP_Error("400", $message));
        }

        $fileSize = filesize($filepath);

        if ($fileSize === 0) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('The file is empty.', 'ams-wc-amazon')
            );
            wp_send_json_error(new \WP_Error("400", $message));
        }

        $maxSize = 1024 * 1024 * 1; // 1MB
        if ($fileSize > $maxSize) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('File size must be less than 1MB.', 'ams-wc-amazon')
            );
            wp_send_json_error(new \WP_Error("400", $message));
        }

        $filetype = $this->get_file_type($filepath);
        $allowedTypes = array(
            'text/tsv', 'text/csv', 'text/plain', 'application/vnd.ms-excel'
        );

        if (!in_array($filetype, $allowedTypes)) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('Please select a file with a valid file type.', 'ams-wc-amazon')
            );
            wp_send_json_error(new \WP_Error("400", $message));
        }

        // Declare the delimiter
        $delimiter = ',';
        // Open the file in read mode
        $csv = fopen($filepath, 'r');
        $rows = [];
        $row_number = 0;
        $headers = fgetcsv($csv, 0, $delimiter);
        while ($csv_row = fgetcsv($csv, 0, $delimiter)) {
            // Increment Row Number
            $row_number++;
            if (!empty($csv_row[0])) {
                $rows[] = trim($csv_row[0]); // Add first cell only
            }
            if ($row_number >= 100) break;
        }
        fclose($csv);

        $rows = array_filter($rows);
        // Remove duplicate skus
        $rows = array_unique($rows, SORT_REGULAR);
        
        // Return json with success
        wp_send_json_success(array_chunk($rows, 10), 200);
    }

    private function get_file_type($filepath) {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $filetype = finfo_file($finfo, $filepath);
                finfo_close($finfo);
                return $filetype;
            }
        }

        // Fallback to mime_content_type if finfo is not available
        if (function_exists('mime_content_type')) {
            return mime_content_type($filepath);
        }

        // Last resort: check file extension
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $mime_types = array(
            'csv' => 'text/csv',
            'tsv' => 'text/tsv',
            'txt' => 'text/plain',
            'xls' => 'application/vnd.ms-excel'
        );

        return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
    }


    /**
     * Extract all amazon asin from CSV
     */
    public function wca_import_process() {

        // Response Defaults
        $aResponse = [];
        $aResponse['success'] = [];
        $aResponse['imported'] = [];
        
        // POST Data
        $asin = sanitize_text_field($_POST['asin']);
        $nonce = sanitize_text_field($_POST['nonce']);
        
        // Check if nonce is invalid
        if ( !wp_verify_nonce( $nonce, 'ams_product_import' ) ) {
            $message = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('Invalid nonce, please refresh your screen and try again.', 'ams-wc-amazon')
            );
            $aResponse['message'] = $message;
            $aResponse['cancelled'] = explode(",", $asin);
            wp_send_json_error( $aResponse );
        }

        // Check if plugin license status is active or not
        if (ams_plugin_license_status() === false ) {
            $license = sprintf(
                '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                esc_html__('Please activate your license to enable full plugin functionality.', 'ams-wc-amazon')
            );
            $aResponse['message'] = $license;
            $aResponse['cancelled'] = explode(",", $asin);
            wp_send_json_error( $aResponse );
        }
        
        // AMAZON PAAPI
        $asins          = explode(",", $asin);
        $regions        = ams_get_amazon_regions();
        $locale         = get_option( 'ams_amazon_country' );
        $marketplace    = 'www.amazon.'. get_option( 'ams_amazon_country' );
        $service_name   = 'ProductAdvertisingAPI';
        $region         = $regions[ $locale ]['RegionCode'] ?? '';

        // AMAZON PAAPI Credentials
        $access_key     = get_option( 'ams_access_key_id' );
        $secret_key     = get_option( 'ams_secret_access_key' );
        
        // AMAZON PAAPI Payload
        $payload_arr                = array();
        $payload_arr['ItemIds']     = $asins;
        $payload_arr['Resources']   = array( "ParentASIN", "Images.Primary.Small", "Images.Primary.Medium", "Images.Primary.Large", "Images.Variants.Small", "Images.Variants.Medium", "Images.Variants.Large", "ItemInfo.ByLineInfo", "ItemInfo.ContentInfo", "ItemInfo.ContentRating", "ItemInfo.Classifications", "ItemInfo.ExternalIds", "ItemInfo.Features", "ItemInfo.ManufactureInfo", "ItemInfo.ProductInfo", "ItemInfo.TechnicalInfo", "ItemInfo.Title", "ItemInfo.TradeInInfo", "Offers.Listings.Availability.MaxOrderQuantity", "Offers.Listings.Availability.Message", "Offers.Listings.Availability.MinOrderQuantity", "Offers.Listings.Availability.Type", "Offers.Listings.Condition", "Offers.Listings.Condition.ConditionNote", "Offers.Listings.Condition.SubCondition", "Offers.Listings.DeliveryInfo.IsAmazonFulfilled", "Offers.Listings.DeliveryInfo.IsFreeShippingEligible", "Offers.Listings.DeliveryInfo.IsPrimeEligible", "Offers.Listings.DeliveryInfo.ShippingCharges", "Offers.Listings.IsBuyBoxWinner", "Offers.Listings.LoyaltyPoints.Points", "Offers.Listings.MerchantInfo", "Offers.Listings.Price", "Offers.Listings.ProgramEligibility.IsPrimeExclusive", "Offers.Listings.ProgramEligibility.IsPrimePantry", "Offers.Listings.Promotions", "Offers.Listings.SavingBasis", "Offers.Summaries.HighestPrice", "Offers.Summaries.LowestPrice", "Offers.Summaries.OfferCount" );
        $payload_arr['PartnerTag']  = get_option( 'ams_associate_tag' );
        $payload_arr['PartnerType'] = 'Associates';
        $payload_arr['Marketplace'] = $marketplace;
        $payload_arr['Operation']   = 'GetItems';
        $payload                    = wp_json_encode( $payload_arr );
        $host = $regions[ $locale ]['Host'];
        $uri_path = "/paapi5/getitems";
        $api = new \Amazon\Affiliate\Api\Amazon_Product_Api( $access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems' );
        $response = $api->do_request();
        
        // Check if ItemsResult is set and not null before accessing Items
        if( isset($response->ItemsResult) && isset($response->ItemsResult->Items) ) {
            $results = $response->ItemsResult->Items;
        } else {
            $results = null;
        }
        
        if( empty($results) ) {
            sleep(5);
            $apiRetried = new \Amazon\Affiliate\Api\Amazon_Product_Api($access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems');
            $response = $apiRetried->do_request();
        
            // Check if ItemsResult is set and not null before accessing Items
            if( isset($response->ItemsResult) && isset($response->ItemsResult->Items) ) {
                $results = $response->ItemsResult->Items;
            } else {
                $results = null;
            }
        
            if( empty($results) ) {            
                // Send failed
                $message = sprintf(
                    '<div class="alert alert-warning mx-3 text-left" role="alert">%s</div>', 
                    esc_html__('API connection error.', 'ams-wc-amazon')
                );
                $aResponse['message'] = $message;
                $aResponse['cancelled'] = explode(",", $asin);
                wp_send_json_error( $aResponse );
                // wp_send_json_error( new \WP_Error( '403', 'API connection error.' ) );
            }
        }

        // $ams_all_asin = ams_get_all_products_info();
        $ams_all_asin = ams_get_all_products_info_with_parent();

        foreach( $results as $row ) {
            $asin            = $row->ASIN;
            $parentASIN = isset($row->ParentASIN) ? $row->ParentASIN : $asin;
            $detail_page_url = $row->DetailPageURL;
            $thumbnail_size  = get_option('ams_product_thumbnail_size');
            
            if( !empty($ams_all_asin) && !empty($ams_all_asin['asin']) && ( in_array($asin, $ams_all_asin['asin']) || in_array($parentASIN, $ams_all_asin['asin']) ) ) {
                // Already imported or not found sku
                array_push($aResponse['imported'], $asin);
                continue;
            }

            // Check if specific thumbnail size exists, otherwise get the first key
            if(isset($row->Images->Primary->{$thumbnail_size})) {
                $image = $row->Images->Primary->{$thumbnail_size}->URL;
            } else {
                $primaryImages = (array) $row->Images->Primary;
                $key = key($primaryImages);
                $image = $row->Images->Primary->{$key}->URL;
            }

            $gallery = isset($row->Images->Variants) ? $row->Images->Variants : [];

            // Safe check for Amount
            if (isset($row->Offers->Listings[0]->Price->Amount)) {
                $amount = $row->Offers->Listings[0]->Price->Amount;
            } else {
                $amount = null; // or some default value
            }

            // Safe check for SavingBasis Amount
            if (isset($row->Offers->Listings[0]->SavingBasis) && isset($row->Offers->Listings[0]->SavingBasis->Amount)) {
                $saving_amount = $row->Offers->Listings[0]->SavingBasis->Amount;
            } else {
                $saving_amount = null; // or some default value
            }

            $title          = $row->ItemInfo->Title->DisplayValue;
            $product_status = isset($row->Offers->Listings[0]->Availability->Message) ? $row->Offers->Listings[0]->Availability->Message : '';
            $product_status = !empty($product_status) ? 'instock' : 'outofstock';
            
            // Safe check for Features DisplayValues
            if (isset($row->ItemInfo->Features) && isset($row->ItemInfo->Features->DisplayValues)) {
                $features = $row->ItemInfo->Features->DisplayValues;
            } else {
                $features = []; // Assign an empty array or some default value if Features or DisplayValues are not set
            }

            // Import Product Faster if setting is Enabled
            if( 'Yes' == get_option('ams_fast_product_importer')  ) {
                $productData = [];
                $productData['asin'] = $asin;
                $productData['title'] = $title;
                $productData['region'] = $region;
                $productData['parentSku'] = $parentASIN;
                $productData['product_url'] = $detail_page_url;
                $productData['import_method'] = '1'; // Set manually
                $default_message = '<span class="dashicons dashicons-saved"></span> ' . esc_html__( 'Success', 'ams-wc-amazon' );
                $productData['default_message'] = $default_message; // Set manually
                $productData['attributes'] = [];
                $productData['sale_price'] = $amount;
                $productData['regular_price'] = !empty($saving_amount) ? $saving_amount : $amount;
                
                // Success
                array_push($aResponse['success'], $asin);
                
                /**
                 * Import Product Faster
                 * 
                 * @param array $productData
                 * 
                 * @return string
                 */
                advancedProductImporter( $productData ); continue;
            }

            $payload_arr2 = array();
            $payload_arr2['ASIN']       = $asin;//'B00T0C9XRK';
            $payload_arr2['Resources']  = array( "ParentASIN", "ItemInfo.Title", "Offers.Listings.Price", "Offers.Listings.ProgramEligibility.IsPrimeExclusive", "Offers.Listings.ProgramEligibility.IsPrimePantry", "Offers.Listings.Promotions", "Offers.Listings.SavingBasis", "Offers.Listings.Availability.Message", "Offers.Summaries.HighestPrice", "Offers.Summaries.LowestPrice", "VariationSummary.Price.HighestPrice", "VariationSummary.Price.LowestPrice","VariationSummary.VariationDimension", "Images.Primary.Small", "Images.Primary.Medium", "Images.Primary.Large", "Images.Variants.Small", "Images.Variants.Medium", "Images.Variants.Large" );
            $payload_arr2['PartnerTag'] = get_option( 'ams_associate_tag' );
            $payload_arr2['PartnerType']= 'Associates';
            $payload_arr2['Marketplace']= $marketplace; //'www.amazon.com';
            $payload_arr2['Operation']  = 'GetVariations';
            $payload2                   = json_encode($payload_arr2);
            $host                       = $regions[ $locale ]['Host'];
            $uri_path                   = "/paapi5/getvariations";
            $api2                       = new  \Amazon\Affiliate\Api\Amazon_Product_Api ( $access_key, $secret_key,$region, $service_name, $uri_path, $payload2, $host, 'getVariation' );
            $response2                  = $api2->do_request();
            
            $variations                 = isset($response2->VariationsResult->VariationSummary) ? $response2->VariationsResult->VariationSummary : null;
            $attributes                 = isset($response2->VariationsResult->Items) ? $response2->VariationsResult->Items : [];

            
            $VariationPage              = 2; 
            $Variationlist              = [];                   
            if( isset($variations->PageCount) && $variations->PageCount >= 1 ) {
                foreach( $response2->VariationsResult->Items as $item ) {
                    $VariationAttribute = [];
                    foreach( $item->VariationAttributes as $ItemVariationAttribute ) {
                        $VariationAttribute[$ItemVariationAttribute->Name] = trim($ItemVariationAttribute->Value);
                    }
            
                    // Safe check for Amount
                    if (isset($item->Offers->Listings[0]->Price->Amount)) {
                        $amount = $item->Offers->Listings[0]->Price->Amount;
                    } else {
                        $amount = null; // or some default value
                    }
            
                    // Safe check for DisplayAmount
                    if (isset($item->Offers->Listings[0]->Price->DisplayAmount)) {
                        $DisplayAmount = $item->Offers->Listings[0]->Price->DisplayAmount;
                    } else {
                        $DisplayAmount = null; // or some default value
                    }
            
                    // Safe check for SavingBasis Amount
                    if (isset($item->Offers->Listings[0]->SavingBasis) && isset($item->Offers->Listings[0]->SavingBasis->Amount)) {
                        $saving_amount = $item->Offers->Listings[0]->SavingBasis->Amount;
                    } else {
                        $saving_amount = null; // or some default value
                    }
            
                    // Safe check for SavingDisplayAmount
                    if (isset($item->Offers->Listings[0]->SavingBasis) && isset($item->Offers->Listings[0]->SavingBasis->DisplayAmount)) {
                        $SavingDisplayAmount = $item->Offers->Listings[0]->SavingBasis->DisplayAmount;
                    } else {
                        $SavingDisplayAmount = null; // or some default value
                    }
            
                    // Stock status
                    $product_stock = isset($item->Offers->Listings[0]->Availability->Message) ? $item->Offers->Listings[0]->Availability->Message : '';
                    $stock_status  = !empty($product_stock) ? 'instock' : 'outofstock';

                    if (empty($saving_amount)) {
                        $sale_price = $amount;
                        $regular_price = $amount;
                    } else {
                        $sale_price = $amount;
                        $regular_price = $saving_amount;
                    }

                    // Add variation images
                    $v_gallery = [@$item->Images->Primary->Large->URL];
                    $Variationlist[] = array(
                        'post_title' => $item->ItemInfo->Title->DisplayValue,
                        'attributes' => $VariationAttribute,
                        'sku' => $item->ASIN,
                        'regular_price' => floatval($regular_price),
                        'sale_price' => floatval($sale_price),
                        'stock_status' => $stock_status,
                        'product_image_gallery' => $v_gallery,
                    );
                }
                
                while( $VariationPage <= $variations->PageCount ) {
                    $payload_arr2['VariationPage']   = $VariationPage;
                    $payload3                   = json_encode($payload_arr2);
                    $api3 = new  \Amazon\Affiliate\Api\Amazon_Product_Api ( 
                        $access_key, $secret_key,$region, $service_name, $uri_path, $payload3, $host, 'getVariation' 
                    );
                    $response3 = $api3->do_request();

                    foreach( isset($response3->VariationsResult->Items) ? $response3->VariationsResult->Items : [] as $item ) {
                        $VariationAttribute = [];
                        foreach( $item->VariationAttributes as $ItemVariationAttribute ) {
                            $VariationAttribute[$ItemVariationAttribute->Name] = trim($ItemVariationAttribute->Value);
                        }
                        
                        $amount = isset($item->Offers->Listings[0]->Price->Amount) 
                            ? $item->Offers->Listings[0]->Price->Amount 
                            : null;
                        
                        $DisplayAmount = isset($item->Offers->Listings[0]->Price->DisplayAmount) 
                            ? $item->Offers->Listings[0]->Price->DisplayAmount 
                            : null;

                        $saving_amount = isset($item->Offers->Listings[0]->SavingBasis) && isset($item->Offers->Listings[0]->SavingBasis->Amount) 
                            ? $item->Offers->Listings[0]->SavingBasis->Amount 
                            : null;
                        
                        $SavingDisplayAmount = isset($item->Offers->Listings[0]->SavingBasis) && isset($item->Offers->Listings[0]->SavingBasis->DisplayAmount) 
                            ? $item->Offers->Listings[0]->SavingBasis->DisplayAmount 
                            : null;

                        $product_stock = isset($item->Offers->Listings[0]->Availability->Message) 
                            ? $item->Offers->Listings[0]->Availability->Message 
                            : '';
                        
                        $stock_status = !empty($product_stock) ? 'instock' : 'outofstock';

                        if (empty($saving_amount)) {
                            $sale_price = $amount;
                            $regular_price = $amount;
                        } else {
                            $sale_price = $amount;
                            $regular_price = $saving_amount;
                        }

                        // Add variation images
                        $v_gallery = [@$item->Images->Primary->Large->URL];
                        $Variationlist[] = array(
                            'post_title' => $item->ItemInfo->Title->DisplayValue,
                            'attributes' => $VariationAttribute,
                            'sku' => $item->ASIN,
                            'regular_price' => floatval($regular_price),
                            'sale_price' => floatval($sale_price),
                            'stock_status' => $stock_status,
                            'product_image_gallery' => $v_gallery,
                        );
                    }
                    
                    $VariationPage++;
                }
            }
            
            $content = '';
            foreach ( $features as $feature) {
                $content .= '<ul><li>'.$feature.'</li></ul>';
            }

            if(empty($content)) {
                $options_new = array(
                    'http'=>array(
                        'method'=>"GET",
                        'header'=>"Accept-language: en\r\n" .
                                "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36\r\n" // i.e. An iPad 
                    )
                );

                $url = esc_url_raw($row->DetailPageURL);
                $context_new = stream_context_create( $options_new );
                $headers = @get_headers($url);
                $response_body_new = '';
                if ($headers && strpos($headers[0], '200') !== false) {
                    $response_body_new = @file_get_contents($url, false, $context_new);
                }

                if (!empty($response_body_new)) {
                    if (!class_exists('simple_html_dom')) {
                        require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
                    }
                    $html_new = new \simple_html_dom();
                    $html_new->load($response_body_new);

                    $content = $this->fetchContentFromHtml($html_new);
                }
            }

            $user_id = get_current_user();

            // Get status settings
            $importStatus = get_option( 'ams_product_import_status', true );

            $post_id = wp_insert_post(array(
                'post_author'  => $user_id,
                'post_title'   => stripslashes($title),
                // 'post_name'    => sanitize_title( $title ),
                'post_content' => $content,
                'post_status'  => $importStatus,
                // 'post_status'  => 'publish',
                'post_type'    => "product",
                'post_parent'  => '',
            ));

            if(!isset($variations->VariationDimensions) || empty($variations->VariationDimensions)){
                wp_set_object_terms( $post_id, 'simple', 'product_type');
                $product = wc_get_product( $post_id );
                $product->save(); // Update
                // For simple products, always use the product's own ASIN
                update_post_meta( $post_id, '_sku', $asin );
            } else {
                wp_set_object_terms( $post_id, 'variable', 'product_type');
                $product = wc_get_product( $post_id );
                $product->save(); // Update
                // For variable products, use ParentASIN if available, otherwise use the product's own ASIN
                update_post_meta( $post_id, '_sku', $parentASIN !== $asin ? $parentASIN : $asin );
            }

            $product_category = isset($row->ItemInfo->Classifications->Binding->DisplayValue) ? $row->ItemInfo->Classifications->Binding->DisplayValue : '';
            if( empty( trim($product_category) ) || "unknown binding" == trim( strtolower($product_category) ) ) {
                $product_category = isset($row->ItemInfo->Classifications->ProductGroup->DisplayValue) ? $row->ItemInfo->Classifications->ProductGroup->DisplayValue : '';
            }
            $ams_default_category = get_option('ams_default_category');
            if(!empty($ams_default_category) && $ams_default_category == '_auto_import_amazon') {

                if(!empty($product_category)) {
                    
                    $run_cat = 1;
                    if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                        if (get_option('product_category_cron') != 1) {
                            $run_cat = 0;
                        }
                    }

                    if(!empty($run_cat)) {
                        // Create if not exists
                        if( ! term_exists( $product_category, 'product_cat', $parent = 0 ) ) {
                            wp_insert_term($product_category, 'product_cat', array(
                                'description' => $product_category,
                                // 'slug' => $slug,
                                'parent' => 0
                            ));
                        }
                        if ($term = get_term_by('name', esc_attr( $product_category ), 'product_cat')) {
                            wp_set_object_terms($post_id, $term->term_id, 'product_cat');
                        } else {
                            wp_set_object_terms($post_id, $product_category, 'product_cat');
                        }
                    }

                } else {
                    // re call amazon code and get category
                    $options_new = array(
                        'http'=>array(
                            'method'=>"GET",
                            'header'=>"Accept-language: en\r\n" .
                                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36\r\n" // i.e. An iPad 
                        )
                    );

                    $context_new = stream_context_create( $options_new );
                    $response_body_new = $page = file_get_contents( esc_url_raw( $row->DetailPageURL ), false, $context_new );
                    if (!empty($response_body_new)) {
                        if (!class_exists('simple_html_dom')) {
                            require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
                        }
                        $html_new = new \simple_html_dom();
                        $html_new->load($response_body_new);

                        $product_category_new = $this->syncAndGetCategory($html_new);
                        if(!empty($product_category_new)) {
                            wp_set_object_terms($post_id, $product_category_new, 'product_cat');
                        }
                    }

                } //end else
            } else {
                wp_set_object_terms($post_id, $ams_default_category, 'product_cat');
            }
            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, 'total_sales', '0' );
            update_post_meta( $post_id, '_downloadable', 'no' );
            update_post_meta( $post_id, '_virtual', 'yes' );

            if (!isset($variations->VariationDimensions) || empty($variations->VariationDimensions)) {
                if (empty($saving_amount)) {
                    $price = $this->parsePriceStringnew($amount);
                    update_post_meta($post_id, '_regular_price', $price);
                    update_post_meta($post_id, '_sale_price', $price);
                    update_post_meta($post_id, '_price', $price);
                } else {
                    $regular_price = $this->parsePriceStringnew($saving_amount);
                    $sale_price = $this->parsePriceStringnew($amount);
                    update_post_meta($post_id, '_regular_price', $regular_price);
                    update_post_meta($post_id, '_sale_price', $sale_price);
                    update_post_meta($post_id, '_price', $sale_price);
                }
            } else {
                wp_set_object_terms($post_id, 'variable', 'product_type');
            }
            
            update_post_meta( $post_id, '_purchase_note', '' );
            update_post_meta( $post_id, '_featured', 'no' );
            update_post_meta( $post_id, '_weight', '' );
            update_post_meta( $post_id, '_length', '' );
            update_post_meta( $post_id, '_width', '' );
            update_post_meta( $post_id, '_height', '' );
            
            update_post_meta( $post_id, '_sale_price_dates_from', '' );
            update_post_meta( $post_id, '_sale_price_dates_to', '' );
            update_post_meta( $post_id, '_sold_individually', '' );
            update_post_meta( $post_id, '_manage_stock', 'no' );
            update_post_meta( $post_id, '_backorders', 'no' );
            update_post_meta( $post_id, '_stock', '' );
            update_post_meta( $post_id, '_wca_amazon_affiliate_asin', $asin );
            update_post_meta( $post_id, '_wca_amazon_affiliate_parent_asin', $parentASIN );
            
            // Amazon product URL
            update_post_meta( $post_id, '_ams_product_url', $detail_page_url );
            update_post_meta( $post_id, '_detail_page_url', $detail_page_url );
            update_post_meta( $post_id, '_region', $region );
            update_post_meta( $post_id, '_import_method', '1' );
            
            ############################### Create product Variations ############################################
            if(isset($variations->VariationDimensions) && !empty($variations->VariationDimensions)){
                $attributeChecks = [];
                $attributes_data = [];
                foreach ($variations->VariationDimensions as $attribute => $term_name ) {
                    $attr_label = $term_name->DisplayName;
                    $values = $term_name->Values;
                    $values_array = implode('|', $values);
                    // $attr_slug = sanitize_title($attr_label);
                    $attr_slug = sanitize_title($term_name->Name);

                    // TODO: Step 2 or 3
                    $values = array_map('trim', $values);
                    $attributeChecks[$attr_slug] = sanitize_title($attr_label);

                    // TODO: Step 2
                    $attributes_data[] = array(
                        'name'=>$attr_label, 
                        'slug' => $attr_slug, 
                        'options'=>$values, 
                        'visible' => 1, 
                        'variation' => 1 
                    );
                }
                
                // TODO: Step 2
                wc_update_product_attributes($post_id,$attributes_data);
                
                // TODO: Step 2
                $product = wc_get_product($post_id);
                foreach( $Variationlist as $SingleVariation ) {
                    if (!isset($SingleVariation['sku']) || empty($SingleVariation['attributes']) || "outofstock" == $SingleVariation['stock_status']) {
                        continue;
                    }

                    $variation = array(
                        'post_title'  => $SingleVariation['post_title'],
                        'post_name'   => 'product-'.$post_id.'-variation-'.$SingleVariation['sku'],
                        'post_status' => 'publish',
                        'post_parent' => $post_id,
                        'post_type'   => 'product_variation',
                        'guid'        => $product->get_permalink()
                    );
                    // $existing_variation = get_post($variation);
                    $existing_variation = get_product_by_sku($SingleVariation['sku']);
                    if($existing_variation !== null) {
                        // $variation_id = $existing_variation->ID;
                        // $variation['ID'] = $existing_variation->ID;
                        $variation['ID'] = $variation_id = $existing_variation->get_id();
                        wp_update_post($variation);                        
                    } else {
                        $variation_id = wp_insert_post( $variation );
                    }

                    // Get an instance of the WC_Product_Variation object
                    $variation = new \WC_Product_Variation( $variation_id );
                    if( count($SingleVariation['attributes']) > 0 ) {
                        // Iterating through the variations attributes
                        foreach ($SingleVariation['attributes'] as $attribute => $term_name ) {
                            $taxonomy = 'pa_'.$attribute; // The attribute taxonomy
                            
                            $term_name = esc_attr($term_name);

                            // If taxonomy doesn't exists
                            if( ! taxonomy_exists( $taxonomy ) ) continue;

                            // Check if the Term name exist
                            if( ! term_exists( $term_name, $taxonomy ) ) continue;
                            $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

                            // Get the post Terms names from the parent variable product.
                            $post_term_names =  wp_get_post_terms( $post_id, $taxonomy, array('fields' => 'names') );
                            $post_term_names = array_map('strtolower', $post_term_names);

                            // Check if the post term exist
                            if( ! in_array( strtolower($term_name), $post_term_names ) ) continue;
                            // Set/save the attribute data in the product variation
                            update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
                        }
                    }
                    
                    // SKU
                    if( ! empty( $SingleVariation['sku'] ) ) {
                        $variation->set_sku( $SingleVariation['sku'] );
                    }

                    // Set defaults
                    if( isset($SingleVariation['regular_price']) && $SingleVariation['regular_price'] == '0' ) {
                        $SingleVariation['regular_price'] = '';
                    }
                    
                    if( isset($SingleVariation['sale_price']) && $SingleVariation['sale_price'] == '0' ) {
                        $SingleVariation['sale_price'] = '';
                    }

                    // Prices
                    if( empty( $SingleVariation['sale_price'] ) ){
                        $variation->set_price( $SingleVariation['regular_price'] ?? '' );
                    } else {
                        $variation->set_price( $SingleVariation['sale_price'] ?? '' );
                        $variation->set_sale_price( $SingleVariation['sale_price'] ?? '' );
                    }
                    
                    $variation->set_regular_price($SingleVariation['regular_price'] ?? '');

                    // Stock
                    if( ! empty($SingleVariation['stock_status']) ){
                        // $variation->set_stock_quantity( $data['stock_qty'] );
                        // $variation->set_manage_stock(true);
                        $variation->set_stock_status($SingleVariation['stock_status']);
                    } else {
                        $variation->set_manage_stock(false);
                    }

                    // Product image gallery
                    if( !empty( $SingleVariation['product_image_gallery'] ) ) {
                        if( count( $SingleVariation['product_image_gallery'] ) > 0 ) {
                            // Create image id
                            $attachment = array_shift($SingleVariation['product_image_gallery']);

                            if ( 'Yes' === get_option( 'ams_remote_amazon_images' ) ) {
                                $this->attach_product_thumbnail_url($variation_id, $attachment, 2);

                                // Remove previous image
                                $image_id = $variation->get_image_id();
                                if( $image_id ) {
                                    // wp_delete_post( $image_id );
                                    // wp_delete_attachment( $image_id, true );
                                }
                            } else {
                                // Reset variation image url 
                                $this->reset_product_thumbnail_url($variation_id, $flag=2);
                                // Attach variation image
                                $attachment_id = $this->attach_product_thumbnail($variation_id, $attachment, 2);

                                if( $attachment_id ) {
                                    // Remove previous image
                                    $image_id = $variation->get_image_id();
                                    if( $image_id ) {
                                        wp_delete_post( $image_id );
                                        wp_delete_attachment( $image_id, true );
                                    }
                                    // Set image id
                                    $variation->set_image_id($attachment_id);
                                }
                            }
                        }
                    }
                    
                    $variation->set_weight(''); // weight (reseting)
                    $variation->save(); // Save the data
                }
            }
            ######################################################################################################
            
            // Check remote amazon images.
            if( $image || $gallery ) {
                // Remove featured image and url.
                $this->delete_product_images($post_id);
                $this->reset_product_thumbnail_url($post_id, $flag=0);
                // Remove product gallery images and url.
                $this->delete_product_gallery_images($post_id);
                $this->reset_product_thumbnail_url($post_id, $flag=1);
                
                $gallery_url = [];
                $gallery = is_array($gallery) ? $gallery : [];
                if ( 'Yes' === get_option( 'ams_remote_amazon_images' ) ) {
                    // Set featured image url
                    $this->attach_product_thumbnail_url( $post_id, $image, 0 );
                    // Set featured image gallary
                    $gallery_url = [];
                    foreach( $gallery as $image ) {
                        // Set gallery image.
                        if(isset($image->{$thumbnail_size})) {
                            $gallery_url[] = $image->{$thumbnail_size}->URL;
                        } else {
                            $imageArray = (array) $image; // Convert object to array
                            $key = key($imageArray);
                            $gallery_url[] = $image->{$key}->URL;
                        }
                    }
                    $this->attach_product_thumbnail_url( $post_id, $gallery_url, 1 );
                } else {
                    // Set featured image url
                    $this->attach_product_thumbnail($post_id, $image, 0);
                    // Set featured image gallary
                    if( count($gallery) > 0 ) {
                        foreach( $gallery as $image ) {
                            // Set gallery image.
                            if(isset($image->{$thumbnail_size})) {
                                $this->attach_product_thumbnail( $post_id, $image->{$thumbnail_size}->URL, 1 );
                            } else {
                                $imageArray = (array) $image; // Convert object to array
                                $key = key($imageArray);
                                $this->attach_product_thumbnail( $post_id, $image->{$key}->URL, 1 );
                            }
                        }
                    }
                }
            }
            
            $Current = get_post_meta( $post_id, '_stock_status',true);
            update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
            update_post_meta( $post_id, 'ams_last_cron_status',0);
            update_post_meta( $post_id, '_stock_status', $product_status );
            
            // Success
            array_push($aResponse['success'], $asin);
        } // End foreach
        
        /* END HERE */

        // Failed or not found sku
        $aResponse['failed'] = array_diff($asins, array_merge($aResponse['success'], $aResponse['imported']));

        // Return json with success
        wp_send_json_success( $aResponse, 200 );
    }
}