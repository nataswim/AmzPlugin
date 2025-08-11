<?php
namespace Amazon\Affiliate\Admin;

/**
 * Product import handler class
 *
 * @package Amazon\Affiliate\Admin
 */
class ImportProducts {

    public function create_product_variation($post_id,$data){

        ## ---------------------- VARIATION ATTRIBUTES ---------------------- ##
        $product_attributes = array();
        foreach( $data['attributes'] as $key => $terms ){
            $taxonomy = wc_attribute_taxonomy_name($key); // The taxonomy slug
            $attr_label = ucfirst($key); // attribute label name
            $attr_name = ( wc_sanitize_taxonomy_name($key)); // attribute slug
            // NEW Attributes: Register and save them
            if( ! taxonomy_exists( $taxonomy ) )
                $this->save_product_attribute_from_name( $attr_name, $attr_label );
            $product_attributes[$taxonomy] = array (
                'name'         => $taxonomy,
                'value'        => '',
                'position'     => '',
                'is_visible'   => 0,
                'is_variation' => 1,
                'is_taxonomy'  => 1
            );
            foreach( $terms as $value ){
                $term_name = ucfirst($value);
                $term_slug = sanitize_title($value);
                // Check if the Term name exist and if not we create it.
                if( ! term_exists( $value, $taxonomy ) )
                    wp_insert_term( $term_name, $taxonomy, array('slug' => $term_slug ) ); // Create the term
                // Set attribute values
                wp_set_post_terms( $post_id, $term_name, $taxonomy, true );
            }
        }
        update_post_meta( $post_id, '_product_attributes', $product_attributes );
    }

    public function save_product_attribute_from_name( $name, $label='', $set=true ){
        if( ! function_exists ('get_attribute_id_from_name') ) return;
        global $wpdb;
        $label = $label == '' ? ucfirst($name) : $label;
        $attribute_id = $this->get_attribute_id_from_name( $name );
        if( empty($attribute_id) ){
            $attribute_id = NULL;
        } else {
            $set = false;
        }
        $args = array(
            'attribute_id'      => $attribute_id,
            'attribute_name'    => $name,
            'attribute_label'   => $label,
            'attribute_type'    => 'select',
            'attribute_orderby' => 'menu_order',
            'attribute_public'  => 0,
        );
        if( empty($attribute_id) ) {
            $wpdb->insert(  "{$wpdb->prefix}woocommerce_attribute_taxonomies", $args );
            set_transient( 'wc_attribute_taxonomies', false );
        }
        if( $set ){
            $attributes = wc_get_attribute_taxonomies();
            $args['attribute_id'] = $this->get_attribute_id_from_name( $name );
            $attributes[] = (object) $args;
            //print_r($attributes);
            set_transient( 'wc_attribute_taxonomies', $attributes );
        } else {
            return;
        }
    }

    public function get_attribute_id_from_name( $name ){
        global $wpdb;
        $attribute_id = $wpdb->get_col("SELECT attribute_id
        FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
        WHERE attribute_name LIKE '$name'");
        return reset($attribute_id);
    }

// API Import
public function product_import() {
    try {
        @$product_region = $_POST['region'];
        @$nonce = $_POST['nonce'];
        if(!isset($_POST['is_cron']) || $_POST['is_cron'] != 1){
            if (!wp_verify_nonce( $nonce, 'ams_product_import' ) ) {
                die('Busted!');
            }
        }

        // Check License
        if (ams_plugin_license_status() === false) {
            echo "<script>console.log('Plugin license not activated');</script>";
            $license = sprintf(esc_html__('Activate License!','ams-wc-amazon'));
            echo wp_kses_post($license);
            wp_die();
        }

        $asin = sanitize_text_field( $_POST['asin'] );
        $locale = get_option( 'ams_amazon_country' );
        $regions = ams_get_amazon_regions();
        $marketplace = 'www.amazon.'. get_option( 'ams_amazon_country' );
        $service_name = 'ProductAdvertisingAPI';
        if (!isset($_POST['is_cron']) || $_POST['is_cron'] != 1) {
            $region = $regions[ $locale ]['RegionCode'];
        } else {
            $region = isset($_POST['region']) ? $_POST['region'] : "";
        }
        $access_key = get_option( 'ams_access_key_id' );
        $secret_key = get_option( 'ams_secret_access_key' );
        $payload_arr = array();
        $payload_arr['ItemIds'] = array( $asin );

        $payload_arr['Resources'] = array( 
            "BrowseNodeInfo.BrowseNodes.Ancestor",
            "ParentASIN", "Images.Primary.Small", "Images.Primary.Medium", "Images.Primary.Large", 
            "Images.Variants.Small", "Images.Variants.Medium", "Images.Variants.Large", 
            "ItemInfo.ByLineInfo", "ItemInfo.ContentInfo", "ItemInfo.ContentRating", 
            "ItemInfo.Classifications", "ItemInfo.ExternalIds", "ItemInfo.Features", 
            "ItemInfo.ManufactureInfo", "ItemInfo.ProductInfo", "ItemInfo.TechnicalInfo", 
            "ItemInfo.Title", "ItemInfo.TradeInInfo", "Offers.Listings.Availability.MaxOrderQuantity", 
            "Offers.Listings.Availability.Message", "Offers.Listings.Availability.MinOrderQuantity", 
            "Offers.Listings.Availability.Type", "Offers.Listings.Condition", 
            "Offers.Listings.Condition.ConditionNote", "Offers.Listings.Condition.SubCondition", 
            "Offers.Listings.DeliveryInfo.IsAmazonFulfilled", "Offers.Listings.DeliveryInfo.IsFreeShippingEligible", 
            "Offers.Listings.DeliveryInfo.IsPrimeEligible", "Offers.Listings.DeliveryInfo.ShippingCharges", 
            "Offers.Listings.IsBuyBoxWinner", "Offers.Listings.LoyaltyPoints.Points", 
            "Offers.Listings.MerchantInfo", "Offers.Listings.Price", 
            "Offers.Listings.ProgramEligibility.IsPrimeExclusive", 
            "Offers.Listings.ProgramEligibility.IsPrimePantry", "Offers.Listings.Promotions", 
            "Offers.Listings.SavingBasis", "Offers.Summaries.HighestPrice", 
            "Offers.Summaries.LowestPrice", "Offers.Summaries.OfferCount"
        );

        $payload_arr['PartnerTag'] = get_option( 'ams_associate_tag' );
        $payload_arr['PartnerType'] = 'Associates';
        $payload_arr['Marketplace'] = $marketplace;
        $payload_arr['Operation'] = 'GetItems';
        $payload = wp_json_encode( $payload_arr );
        $host = $regions[ $locale ]['Host'];
        $uri_path = "/paapi5/getitems";
        $api = new \Amazon\Affiliate\Api\Amazon_Product_Api ( $access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems' );
        $response = $api->do_request();

        // Check if ItemsResult is set and not null before accessing Items
        if (isset($response->ItemsResult) && isset($response->ItemsResult->Items)) {
            $results = $response->ItemsResult->Items;
        } else {
            $results = null;
        }

        if (empty($results)) {
            sleep(2);
            $apiRetried = new \Amazon\Affiliate\Api\Amazon_Product_Api($access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems');
            $response = $apiRetried->do_request();
        
            // Check if ItemsResult is set and not null before accessing Items
            if (isset($response->ItemsResult) && isset($response->ItemsResult->Items)) {
                $results = $response->ItemsResult->Items;
            } else {
                $results = null;
            }
        
            if (empty($results)) {
                // Make sure $post_id is defined before using it
                if (isset($post_id)) {
                    update_post_meta($post_id, 'ams_last_cron_update', date('Y-m-d H:i:s'));
                    update_post_meta($post_id, 'ams_last_cron_status', 1);
                }
        
                if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                    $message = esc_html__('Please check your Amazon API Settings First.', 'ams-wc-amazon');
                    echo wp_kses_post($message);
                } else {
                    echo wp_kses_post('Please try again!');
                }
                wp_die();
            }
        }

        $ams_all_asin = ams_get_all_products_info_with_parent();
        foreach ($results as $row) {
            $thumbnail_size  = get_option('ams_product_thumbnail_size');
            $asin            = $row->ASIN;
            $parentASIN      = isset($row->ParentASIN) ? $row->ParentASIN : null;
            $detail_page_url = $row->DetailPageURL;

            // If ParentASIN is null, we treat this as a standalone product
            $parentASIN = $parentASIN !== null ? $parentASIN : $asin;

            if (!isset($_POST['prod_id'])) {
                if (!empty($ams_all_asin) && !empty($ams_all_asin['asin'])) {
                    if ($parentASIN !== null && in_array($parentASIN, $ams_all_asin['asin'])) {
                        $message = esc_html__('Parent ASIN already imported', 'ams-wc-amazon');
                        echo wp_kses_post($message);
                        wp_die();
                    } elseif (in_array($asin, $ams_all_asin['asin'])) {
                        $message = esc_html__('ASIN already imported', 'ams-wc-amazon');
                        echo wp_kses_post($message);
                        wp_die();
                    }
                }
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
        
            // Safe check for DisplayAmount
            if (isset($row->Offers->Listings[0]->Price->DisplayAmount)) {
                $DisplayAmount = $row->Offers->Listings[0]->Price->DisplayAmount;
            } else {
                $DisplayAmount = null; // or some default value
            }
        
            // Safe check for SavingBasis Amount
            if (isset($row->Offers->Listings[0]->SavingBasis) && isset($row->Offers->Listings[0]->SavingBasis->Amount)) {
                $saving_amount = $row->Offers->Listings[0]->SavingBasis->Amount;
            } else {
                $saving_amount = null; // or some default value
            }

            $product_status = isset($row->Offers->Listings[0]->Availability->Message) ? $row->Offers->Listings[0]->Availability->Message : '';
            $product_status = !empty($product_status) ? 'instock' : 'outofstock';
            $title = $row->ItemInfo->Title->DisplayValue;

            // Safe check for Features DisplayValues
            if (isset($row->ItemInfo->Features) && isset($row->ItemInfo->Features->DisplayValues)) {
                $features = $row->ItemInfo->Features->DisplayValues;
            } else {
                $features = []; // Assign an empty array or some default value if Features or DisplayValues are not set
            }

            // Import Product Faster if setting is Enabled
            if ((!isset($_POST['prod_id']) && empty($_POST['prod_id'])) && 'Yes' == get_option('ams_fast_product_importer')) {
                $productData = [];
                $productData['asin'] = $asin;
                $productData['title'] = $title;
                $productData['region'] = $region;
                $productData['parentSku'] = $parentASIN;
                $productData['product_url'] = $detail_page_url;
                $productData['import_method'] = '1'; // Set manually
                $default_message = '<span class="dashicons dashicons-saved"></span> ' . esc_html__('Success', 'ams-wc-amazon');
                $productData['default_message'] = $default_message; // Set manually
                $productData['attributes'] = [];
                $productData['sale_price'] = $amount;
                $productData['regular_price'] = !empty($saving_amount) ? $saving_amount : $amount;
                
                /**
                 * Import Product Faster
                 * 
                 * @param array $productData
                 * 
                 * @return string
                 */
                advancedProductImporter($productData);
                wp_die();
            }

            $payload_arr2 = array();
            $payload_arr2['ASIN'] = $asin;
            $payload_arr2['Resources'] = array("ParentASIN", "ItemInfo.Title", "Offers.Listings.Price", "Offers.Listings.ProgramEligibility.IsPrimeExclusive", "Offers.Listings.ProgramEligibility.IsPrimePantry", "Offers.Listings.Promotions", "Offers.Listings.SavingBasis", "Offers.Listings.Availability.Message", "Offers.Summaries.HighestPrice", "Offers.Summaries.LowestPrice", "VariationSummary.Price.HighestPrice", "VariationSummary.Price.LowestPrice", "VariationSummary.VariationDimension", "Images.Primary.Small", "Images.Primary.Medium", "Images.Primary.Large", "Images.Variants.Small", "Images.Variants.Medium", "Images.Variants.Large");
            $payload_arr2['PartnerTag'] = get_option('ams_associate_tag');
            $payload_arr2['PartnerType'] = 'Associates';
            $payload_arr2['Marketplace'] = $marketplace;
            $payload_arr2['Operation'] = 'GetVariations';
            $payload2 = json_encode($payload_arr2);
            $host = $regions[$locale]['Host'];
            $uri_path = "/paapi5/getvariations";
            $api2 = new \Amazon\Affiliate\Api\Amazon_Product_Api($access_key, $secret_key, $region, $service_name, $uri_path, $payload2, $host, 'getVariation');
            $response2 = $api2->do_request();

            $variations = isset($response2->VariationsResult->VariationSummary) ? $response2->VariationsResult->VariationSummary : null;
            $attributes = isset($response2->VariationsResult->Items) ? $response2->VariationsResult->Items : [];

            $VariationPage = 2;
            $Variationlist = [];

            if (isset($variations->PageCount) && $variations->PageCount >= 1) {
                foreach ($response2->VariationsResult->Items as $item) {
                    $VariationAttribute = [];
                    foreach ($item->VariationAttributes as $ItemVariationAttribute) {
                        $VariationAttribute[$ItemVariationAttribute->Name] = trim($ItemVariationAttribute->Value);
                    }

                    // Safe check for Amount
                    $amount = isset($item->Offers->Listings[0]->Price->Amount) ? $item->Offers->Listings[0]->Price->Amount : 0;

                    // Safe check for SavingBasis Amount
                    $saving_amount = isset($item->Offers->Listings[0]->SavingBasis->Amount) ? $item->Offers->Listings[0]->SavingBasis->Amount : 0;

                    // Stock status
                    $product_stock = isset($item->Offers->Listings[0]->Availability->Message) ? $item->Offers->Listings[0]->Availability->Message : '';
                    $stock_status = !empty($product_stock) ? 'instock' : 'outofstock';

                    if (empty($saving_amount)) {
                        $sale_price = $amount;
                        $regular_price = $amount;
                    } else {
                        $sale_price = $amount;
                        $regular_price = $saving_amount;
                    }

                    // v_gallery loop
                    $v_gallery = [];
                    // Check for variant images and add them to the gallery
                    if (isset($item->Images->Variants)) {
                        foreach ($item->Images->Variants as $variant_image) {
                            if (isset($variant_image->Large->URL)) {
                                $v_gallery[] = $variant_image->Large->URL;
                            }
                        }
                    }
                    // Fallback to primary image if no variant images are found
                    if (empty($v_gallery) && isset($item->Images->Primary->Large->URL)) {
                        $v_gallery[] = $item->Images->Primary->Large->URL;
                    }
                    // v_gallery loop

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
                while ($VariationPage <= $variations->PageCount) {
                    $payload_arr2['VariationPage'] = $VariationPage;
                    $payload3 = json_encode($payload_arr2);
                    $api3 = new \Amazon\Affiliate\Api\Amazon_Product_Api(
                        $access_key, $secret_key, $region, $service_name, $uri_path, $payload3, $host, 'getVariation'
                    );
                    $response3 = $api3->do_request();

                    foreach (isset($response3->VariationsResult->Items) ? $response3->VariationsResult->Items : [] as $item) {
                        $VariationAttribute = [];
                        foreach ($item->VariationAttributes as $ItemVariationAttribute) {
                            $VariationAttribute[$ItemVariationAttribute->Name] = trim($ItemVariationAttribute->Value);
                        }
                        
                        $amount = isset($item->Offers->Listings[0]->Price->Amount) 
                            ? $item->Offers->Listings[0]->Price->Amount 
                            : 0;
                        
                        $saving_amount = isset($item->Offers->Listings[0]->SavingBasis->Amount) 
                            ? $item->Offers->Listings[0]->SavingBasis->Amount 
                            : 0;

                        $product_stock = isset($item->Offers->Listings[0]->Availability->Message) 
                            ? $item->Offers->Listings[0]->Availability->Message 
                            : '';
                        
                        $stock_status = !empty($product_stock) ? 'instock' : 'outofstock';

                        if ($amount == 0 && $saving_amount == 0) {
                            $sale_price = 0;
                            $regular_price = 0;
                        } elseif ($saving_amount == 0 || $amount >= $saving_amount) {
                            $sale_price = $amount;
                            $regular_price = $amount;
                        } else {
                            $sale_price = $amount;
                            $regular_price = $saving_amount;
                        }

                        // v_gallery loop
                        $v_gallery = [];
                        // Check for variant images and add them to the gallery
                        if (isset($item->Images->Variants)) {
                            foreach ($item->Images->Variants as $variant_image) {
                                if (isset($variant_image->Large->URL)) {
                                    $v_gallery[] = $variant_image->Large->URL;
                                }
                            }
                        }
                        // Fallback to primary image if no variant images are found
                        if (empty($v_gallery) && isset($item->Images->Primary->Large->URL)) {
                            $v_gallery[] = $item->Images->Primary->Large->URL;
                        }
                        // v_gallery loop

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


            // Product Description
            $content = '';
            // Step 1: Populate content from `$features` array, if available
            if (isset($features) && is_array($features)) {
                foreach ($features as $feature) {
                    $content .= '<ul><li>' . $feature . '</li></ul>';
                }
            }
            // Step 2: If content is empty, check cron settings and other API fields
            if (empty($content)) {
                $run_content = 1;
                if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1 && get_option('product_description_cron') != 1) {
                    $run_content = 0;
                }

                // Step 3: If cron settings allow, continue populating content
                if ($run_content) {
                    $content = '';

                    // Populate content with `Features.DisplayValues` if available
                    if (isset($row->ItemInfo->Features->DisplayValues) && is_array($row->ItemInfo->Features->DisplayValues)) {
                        foreach ($row->ItemInfo->Features->DisplayValues as $feature) {
                            $content .= '<ul><li>' . $feature . '</li></ul>';
                        }
                    }

                    // Append Product Description from `ProductInfo.ProductDescription`, if available
                    if (empty($content) && isset($row->ItemInfo->ProductInfo->ProductDescription)) {
                        $content .= '<p>' . $row->ItemInfo->ProductInfo->ProductDescription . '</p>';
                    }

                    // Fallback: Set default message if content is still empty
                    if (empty($content)) {
                        $content = '<p>No detailed information available for this product.</p>';
                    }
                }
            }



            $user_id = get_current_user();
            if(isset($_POST['prod_id']) && !empty($_POST['prod_id'])){
                $post_id = $_POST['prod_id'];
                $postData= array('ID' => $post_id);

                $run_content = 1;
                if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                    if (get_option('product_description_cron') != 1) {
                        $run_content = 0;
                    }
                    
                    if( 1 == get_option('product_name_cron', true) ) {
                        $postData['post_title'] = stripslashes($title);
                        $postData['post_name'] = sanitize_title( $title );
                    }
                }

                if(!empty($run_content)) {
                     $postData['post_content'] = $content;
                }
                
                wp_update_post($postData); 
            }else{
                // Get status settings
                $importStatus = get_option( 'ams_product_import_status', true );

                $post_id = wp_insert_post(array(
                    'post_author'  => $user_id,
                    'post_title'   => stripslashes($title),
                    'post_content' => $content,
                    'post_status'  => $importStatus,
                    'post_type'    => "product",
                    'post_parent'  => '',
                ));
            }

            if(!isset($variations->VariationDimensions) || empty($variations->VariationDimensions)){
                wp_set_object_terms( $post_id, 'simple', 'product_type');
                $product = wc_get_product( $post_id );
                $product->save(); // Update
                if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                    if (get_option('product_sku_cron') == 1) {
                        update_post_meta( $post_id, '_sku', $asin );
                    }
                }else {
                    update_post_meta( $post_id, '_sku', $asin );
                }
            } else {
                wp_set_object_terms( $post_id, 'variable', 'product_type');
                $product = wc_get_product( $post_id );
                $product->save(); // Update
                if($parentASIN){
                    if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                        if (get_option('product_sku_cron') == 1) {
                            update_post_meta( $post_id, '_sku', $parentASIN );
                        }
                    }else {
                        update_post_meta( $post_id, '_sku', $parentASIN );
                    }
                }
            }

            // Import/Update categories
            if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
               // Category cron
               if (get_option('product_category_cron', true) == 1) {
                   // Get default category setting
                   $ams_default_category = get_option('ams_default_category');
                   
                   if (!empty($ams_default_category)) {
                       if ($ams_default_category == '_auto_import_amazon') {
                           $product_category = '';

                           // Check if BrowseNodes exist in the API response
                           if (isset($row->BrowseNodeInfo->BrowseNodes)) {
                               $category_paths = [];

                               // Fetch the dynamically configured min and max depth from the settings
                               $min_depth = intval(get_option('ams_category_min_depth', 1));
                               $max_depth = intval(get_option('ams_category_max_depth', 6));

                               $browse_nodes = $row->BrowseNodeInfo->BrowseNodes;

                               // Loop through each BrowseNode to gather category path and ancestors
                               foreach ($browse_nodes as $node) {
                                   $current_node = $node;
                                   $node_parts = [];

                                   // Traverse the ancestors to get the full category hierarchy for this node
                                   while (isset($current_node->Ancestor)) {
                                       array_unshift($node_parts, $current_node->Ancestor->DisplayName);
                                       $current_node = $current_node->Ancestor;
                                   }

                                   array_push($node_parts, $node->DisplayName);

                                   $cleaned_path = array_filter($node_parts, function ($category) {
                                       return !in_array($category, ['Categories', 'Home', 'Root']);
                                   });

                                   // Ensure we get paths between min_depth and max_depth
                                   if (count($cleaned_path) >= $min_depth) {
                                       $trimmed_path = array_slice($cleaned_path, 0, $max_depth);
                                       $category_paths[] = implode(' > ', $trimmed_path);
                                   }
                               }

                               $product_category = !empty($category_paths) ? $category_paths[0] : '';
                           }

                           if (empty($product_category)) {
                               if (isset($row->ItemInfo->Classifications->Binding->DisplayValue) &&
                                   strtolower($row->ItemInfo->Classifications->Binding->DisplayValue) !== 'unknown binding') {
                                   $product_category = $row->ItemInfo->Classifications->Binding->DisplayValue;
                               } elseif (isset($row->ItemInfo->Classifications->ProductGroup->DisplayValue)) {
                                   $product_category = $row->ItemInfo->Classifications->ProductGroup->DisplayValue;
                               }
                           }

                           if (empty($product_category)) {
                               $product_category = 'Uncategorized';
                               logImportVerification('No valid category found, setting to Uncategorized');
                           }

                           if (!empty($product_category)) {
                               $categories = explode(' > ', $product_category);
                               $parent_id = 0;
                               $category_ids = [];

                               foreach ($categories as $category_name) {
                                   $category_name = trim($category_name);

                                   // Skip invalid UUID-like categories
                                   if (preg_match('/[a-f0-9\-]{36,}/', $category_name)) {
                                       continue;
                                   }

                                   // Check if the category exists under the parent
                                   $existing_category = term_exists($category_name, 'product_cat', $parent_id);

                                   if (!$existing_category) {
                                       // Create the category if it doesn't exist, with the correct parent
                                       $new_category = wp_insert_term($category_name, 'product_cat', array(
                                           'description' => $category_name,
                                           'parent' => $parent_id
                                       ));
                                       if (!is_wp_error($new_category)) {
                                           $parent_id = $new_category['term_id'];
                                           $category_ids[] = $parent_id;
                                           logImportVerification('Created new category: ' . $category_name);
                                       }
                                   } else {
                                       // If the category already exists, fetch its ID
                                       $term = get_term_by('name', esc_attr($category_name), 'product_cat');
                                       if ($term) {
                                           $parent_id = $term->term_id;
                                           $category_ids[] = $parent_id;
                                       } else {
                                           $parent_id = $existing_category['term_id'];
                                           $category_ids[] = $parent_id;
                                       }
                                   }
                               }

                               // Assign ALL categories in the hierarchy to the product
                               if (!empty($category_ids)) {
                                   update_post_meta($post_id, '_product_categories_hierarchy', $product_category);
                                   wp_set_object_terms($post_id, $category_ids, 'product_cat');
                                   logImportVerification('Updated product categories: ' . $product_category);
                               }
                           }
                       } else {
                           // Manual category assignment
                           $term = null;
                           if (is_numeric($ams_default_category)) {
                               $term = get_term($ams_default_category, 'product_cat');
                           }
                           if (!$term) {
                               $term = get_term_by('id', $ams_default_category, 'product_cat');
                           }
                           if (!$term) {
                               $term = get_term_by('slug', $ams_default_category, 'product_cat');
                           }
                           if (!$term) {
                               $term = get_term_by('name', $ams_default_category, 'product_cat');
                           }

                           // Set the category
                           if ($term && !is_wp_error($term)) {
                               wp_set_object_terms($post_id, $term->term_id, 'product_cat');
                               logImportVerification('Category Updated: ' . $term->name);
                           } else {
                               wp_set_object_terms($post_id, 'Uncategorized', 'product_cat');
                               logImportVerification('Category set to Uncategorized (no valid term found)');
                           }
                       }
                   }
               }
            } else {
               // Initial import category handling
               $ams_default_category = get_option('ams_default_category');
               if (!empty($ams_default_category)) {
                   if ($ams_default_category == '_auto_import_amazon') {
                       $product_category = '';

                       // Check if BrowseNodes exist in the API response
                       if (isset($row->BrowseNodeInfo->BrowseNodes)) {
                           $category_paths = [];

                           // Fetch the dynamically configured min and max depth from the settings
                           $min_depth = intval(get_option('ams_category_min_depth', 1));
                           $max_depth = intval(get_option('ams_category_max_depth', 6));

                           $browse_nodes = $row->BrowseNodeInfo->BrowseNodes;

                           foreach ($browse_nodes as $node) {
                               $current_node = $node;
                               $node_parts = [];

                               while (isset($current_node->Ancestor)) {
                                   array_unshift($node_parts, $current_node->Ancestor->DisplayName);
                                   $current_node = $current_node->Ancestor;
                               }

                               array_push($node_parts, $node->DisplayName);

                               $cleaned_path = array_filter($node_parts, function ($category) {
                                   return !in_array($category, ['Categories', 'Home', 'Root']);
                               });

                               if (count($cleaned_path) >= $min_depth) {
                                   $trimmed_path = array_slice($cleaned_path, 0, $max_depth);
                                   $category_paths[] = implode(' > ', $trimmed_path);
                               }
                           }

                           $product_category = !empty($category_paths) ? $category_paths[0] : '';
                       }

                       if (empty($product_category)) {
                           if (isset($row->ItemInfo->Classifications->Binding->DisplayValue) &&
                               strtolower($row->ItemInfo->Classifications->Binding->DisplayValue) !== 'unknown binding') {
                               $product_category = $row->ItemInfo->Classifications->Binding->DisplayValue;
                           } elseif (isset($row->ItemInfo->Classifications->ProductGroup->DisplayValue)) {
                               $product_category = $row->ItemInfo->Classifications->ProductGroup->DisplayValue;
                           }
                       }

                       if (empty($product_category)) {
                           $product_category = 'Uncategorized';
                           logImportVerification('No valid category found during import, setting to Uncategorized');
                       }

                       if (!empty($product_category)) {
                           $categories = explode(' > ', $product_category);
                           $parent_id = 0;
                           $category_ids = [];

                           foreach ($categories as $category_name) {
                               $category_name = trim($category_name);

                               if (preg_match('/[a-f0-9\-]{36,}/', $category_name)) {
                                   continue;
                               }

                               $existing_category = term_exists($category_name, 'product_cat', $parent_id);

                               if (!$existing_category) {
                                   $new_category = wp_insert_term($category_name, 'product_cat', array(
                                       'description' => $category_name,
                                       'parent' => $parent_id
                                   ));
                                   if (!is_wp_error($new_category)) {
                                       $parent_id = $new_category['term_id'];
                                       $category_ids[] = $parent_id;
                                       logImportVerification('Created new category during import: ' . $category_name);
                                   }
                               } else {
                                   $term = get_term_by('name', esc_attr($category_name), 'product_cat');
                                   if ($term) {
                                       $parent_id = $term->term_id;
                                       $category_ids[] = $parent_id;
                                   } else {
                                       $parent_id = $existing_category['term_id'];
                                       $category_ids[] = $parent_id;
                                   }
                               }
                           }

                           if (!empty($category_ids)) {
                               update_post_meta($post_id, '_product_categories_hierarchy', $product_category);
                               wp_set_object_terms($post_id, $category_ids, 'product_cat');
                               logImportVerification('Set initial product categories: ' . $product_category);
                           }
                       }
                   } else {
                       // Manual category assignment during import
                       $term = null;
                       if (is_numeric($ams_default_category)) {
                           $term = get_term($ams_default_category, 'product_cat');
                       }
                       if (!$term) {
                           $term = get_term_by('id', $ams_default_category, 'product_cat');
                       }
                       if (!$term) {
                           $term = get_term_by('slug', $ams_default_category, 'product_cat');
                       }
                       if (!$term) {
                           $term = get_term_by('name', $ams_default_category, 'product_cat');
                       }

                       if ($term && !is_wp_error($term)) {
                           wp_set_object_terms($post_id, $term->term_id, 'product_cat');
                           logImportVerification('Set initial category: ' . $term->name);
                       } else {
                           wp_set_object_terms($post_id, 'Uncategorized', 'product_cat');
                           logImportVerification('Set initial category to Uncategorized (no valid term found)');
                       }
                   }
               }
            }


            // Import Brand Name
            if (isset($row->ItemInfo->ByLineInfo->Brand->DisplayValue)) {
                $brandName = trim($row->ItemInfo->ByLineInfo->Brand->DisplayValue);

                logImportVerification('Brand: ' . $brandName);

                // Check if the brand already exists as a term
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

                    // Assign the brand to the product
                    wp_set_object_terms($post_id, intval($brandTermId), 'product_brand');

                    // Store the brand in post meta for further use
                    update_post_meta($post_id, '_product_brand', $brandName);

                    logImportVerification('Brand assigned to product successfully.');
                }
            } else {
                logImportVerification('Brand not found in the API response.');
            }
            // Import Brand Name

            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, 'total_sales', '0' );
            update_post_meta( $post_id, '_downloadable', 'no' );
            update_post_meta( $post_id, '_virtual', 'yes' );

            if (!isset($variations->VariationDimensions) || empty($variations->VariationDimensions)) {
                if (empty($saving_amount)) {
                    $price_to_set = $this->parsePriceStringnew($amount);
                    if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                        if (get_option('product_price_cron') == 1) {
                            update_post_meta($post_id, '_regular_price', $price_to_set);
                            update_post_meta($post_id, '_sale_price', $price_to_set);
                            update_post_meta($post_id, '_price', $price_to_set);
                        }
                    } else {
                        update_post_meta($post_id, '_regular_price', $price_to_set);
                        update_post_meta($post_id, '_sale_price', $price_to_set);
                        update_post_meta($post_id, '_price', $price_to_set);
                    }
                } else {
                    $regular_price = $this->parsePriceStringnew($saving_amount);
                    $sale_price = $this->parsePriceStringnew($amount);
                    if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                        if (get_option('product_price_cron') == 1) {
                            update_post_meta($post_id, '_regular_price', $regular_price);
                            update_post_meta($post_id, '_sale_price', $sale_price);
                            update_post_meta($post_id, '_price', $sale_price);
                        }
                    } else {
                        update_post_meta($post_id, '_regular_price', $regular_price);
                        update_post_meta($post_id, '_sale_price', $sale_price);
                        update_post_meta($post_id, '_price', $sale_price);
                    }
                }
            } else {
                wp_set_object_terms( $post_id, 'variable', 'product_type');
            }

            // Get the image and variation limits from settings
            $image_limit = get_option('ams_variation_image_limit', 5);
            $variation_limit = get_option('ams_variation_limit', 5);

            // Import/Update Images
            if ($image || $gallery) {
                // Remove existing images and URLs
                delete_product_images($post_id);
                reset_product_thumbnail_url($post_id, $flag = 0); // Reset featured image URL
                delete_product_gallery_images($post_id);
                reset_product_thumbnail_url($post_id, $flag = 1); // Reset gallery URL

                $gallery_url = [];
                $gallery = is_array($gallery) ? $gallery : [];

                if ('Yes' === get_option('ams_remote_amazon_images')) {
                    // Set featured image URL (remote)
                    attach_product_thumbnail_url_api($post_id, $image, 0);

                    // Process and set gallery images (remote)
                    $gallery_url = [];
                    foreach ($gallery as $img) {
                        if (isset($img->{$thumbnail_size})) {
                            $gallery_url[] = $img->{$thumbnail_size}->URL;
                        } else {
                            // Fallback to any available size if specific size is missing
                            $imageArray = (array) $img;
                            $key = key($imageArray);
                            $gallery_url[] = $img->{$key}->URL;
                        }
                    }

                    if (!empty($gallery_url)) {
                        attach_product_thumbnail_url_api($post_id, $gallery_url, 1);
                    }

                } else {
                    // Download and set the featured image locally
                    attach_product_thumbnail_api($post_id, $image, 0);

                    // Download and set the gallery images locally
                    if (count($gallery) > 0) {
                        foreach ($gallery as $img) {
                            if (isset($img->{$thumbnail_size})) {
                                attach_product_thumbnail_api($post_id, $img->{$thumbnail_size}->URL, 1);
                            } else {
                                $imageArray = (array) $img;
                                $key = key($imageArray);
                                attach_product_thumbnail_api($post_id, $img->{$key}->URL, 1);
                            }
                        }
                    }
                }

                // Import/Update Images - Cron Job
                if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                    // Import/Update Images
                    if ($image || $gallery) {
                        // Remove existing images and URLs
                        delete_product_images($post_id);
                        reset_product_thumbnail_url($post_id, $flag = 0); // Reset featured image URL
                        delete_product_gallery_images($post_id);
                        reset_product_thumbnail_url($post_id, $flag = 1); // Reset gallery URL

                        $gallery_url = [];
                        $gallery = is_array($gallery) ? $gallery : [];

                        if ('Yes' === get_option('ams_remote_amazon_images')) {
                            // Set featured image URL (remote)
                            attach_product_thumbnail_url_api($post_id, $image, 0);

                            // Process and set gallery images (remote)
                            $gallery_url = [];
                            foreach ($gallery as $image) {
                                if (isset($image->{$thumbnail_size})) {
                                    $gallery_url[] = $image->{$thumbnail_size}->URL;
                                } else {
                                    // Fallback to any available size if specific size is missing
                                    $imageArray = (array) $image;
                                    $key = key($imageArray);
                                    $gallery_url[] = $image->{$key}->URL;
                                }
                            }

                            if (!empty($gallery_url)) {
                                attach_product_thumbnail_url_api($post_id, $gallery_url, 1);
                            }

                        } else {
                            // Download and set the featured image locally
                            attach_product_thumbnail_api($post_id, $image, 0);

                            // Download and set the gallery images locally
                            if (count($gallery) > 0) {
                                foreach ($gallery as $image) {
                                    if (isset($image->{$thumbnail_size})) {
                                        attach_product_thumbnail_api($post_id, $image->{$thumbnail_size}->URL, 1);
                                    } else {
                                        $imageArray = (array) $image;
                                        $key = key($imageArray);
                                        attach_product_thumbnail_api($post_id, $image->{$key}->URL, 1);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Import/Update reviews
            if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                // Review cron
                if ('1' == get_option('enable_amazon_review', true) && '1' == get_option('product_review_cron', true)) {
                    // Get product URL
                    $product_url = esc_url_raw($row->DetailPageURL);
                    
                    // Get user agent
                    $user_agent = $this->user_agent();
                    
                    // Get product data
                    $response_body = fetchAndValidateProductData($product_url, $user_agent, false);
                    if (is_string($response_body) && !empty($response_body)) {
                        if (!class_exists('simple_html_dom')) {
                            require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
                        }
                        
                        $html = new \simple_html_dom();
                        $html->load($response_body);

                        // Check for broken page
                        $message = check_for_broken_page($response_body, $html);
                        if ($message === null) {
                            // Get review limit from settings
                            $review_limit = get_option('multiple_import_review_limit', 10);
                            
                            // Scrape the reviews
                            $reviews = scrape_amazon_reviews($html, $review_limit);
                            
                            logImportVerification("Processed " . count($reviews) . " reviews for product ID: " . $post_id);

                            if (!empty($reviews)) {
                                // Get existing reviews
                                $existing_reviews = get_comments([
                                    'post_id' => $post_id,
                                    'type' => 'review',
                                    'status' => 'approve'
                                ]);
                                
                                // Create array of existing review hashes
                                $existing_hashes = [];
                                foreach ($existing_reviews as $existing_review) {
                                    $existing_hash = get_comment_meta($existing_review->comment_ID, 'review_hash', true);
                                    if (!empty($existing_hash)) {
                                        $existing_hashes[$existing_hash] = $existing_review->comment_ID;
                                    }
                                }

                                // Initialize rating totals
                                $rating_sum = 0;
                                $rating_count = 0;

                                // Process each review
                                foreach ($reviews as $review_hash => $review) {
                                    // Skip if review already exists
                                    if (isset($existing_hashes[$review_hash])) {
                                        logImportVerification("Skipping duplicate review: " . $review['title']);
                                        continue;
                                    }

                                    // Prepare comment data
                                    $commentdata = [
                                        'comment_post_ID' => $post_id,
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

                                        logImportVerification("Added review: " . $review['title'] . " with ID: " . $comment_id);
                                    }
                                }

                                // Update product rating if we added any new reviews
                                if ($rating_count > 0) {
                                    $product = wc_get_product($post_id);
                                    if ($product) {
                                        // Get actual count of approved reviews
                                        $actual_review_count = get_comments([
                                            'post_id' => $post_id,
                                            'type' => 'review',
                                            'status' => 'approve',
                                            'count' => true
                                        ]);

                                        // Calculate actual rating sum
                                        $actual_rating_sum = 0;
                                        $product_reviews = get_comments([
                                            'post_id' => $post_id,
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
                                        update_post_meta($post_id, '_wc_average_rating', round($new_average, 2));
                                        update_post_meta($post_id, '_wc_rating_count', $actual_review_count);
                                        update_post_meta($post_id, '_wc_review_count', $actual_review_count);
                                        update_post_meta($post_id, '_wc_rating_sum', $actual_rating_sum);

                                        // Clear all relevant caches
                                        delete_transient('wc_product_reviews_' . $post_id);
                                        delete_transient('wc_average_rating_' . $post_id);
                                        wp_cache_delete($post_id, 'product');
                                        
                                        if (function_exists('wc_delete_product_transients')) {
                                            wc_delete_product_transients($post_id);
                                        }

                                        logImportVerification("Updated product rating. New average: " . round($new_average, 2));
                                    }
                                }

                                logImportVerification("Completed review import. Added " . $rating_count . " new reviews");
                            }
                        } else {
                            logImportVerification("Broken page detected: " . $message);
                        }
                    }
                }
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


            ########### Create product Variations ###########
            // Get the limits from settings
            $variation_limit = (int) get_option('ams_variation_limit', 5);
            $image_limit = (int) get_option('ams_variation_image_limit', 5);
            $remove_unavailable = get_option('ams_remove_unavailable_products') === 'Yes';

            if (isset($_POST['is_cron']) && $_POST['is_cron'] == 1) {
                if (get_option('product_variants_cron') == 1) {
                    if (isset($variations->VariationDimensions) && !empty($variations->VariationDimensions)) {
                        $attributeChecks = [];
                        $attributes_data = [];

                        foreach ($variations->VariationDimensions as $attribute => $term_name ) {
                            $attr_label = $term_name->DisplayName;
                            $values     = $term_name->Values;
                            $values_array = implode('|', $values);
                            $attr_slug  = sanitize_title($term_name->Name);

                            $values = array_map('trim', $values);
                            $attributeChecks[$attr_slug] = sanitize_title($attr_label);

                            $attributes_data[] = array(
                                'name'      => $attr_label,
                                'slug'      => $attr_slug,
                                'options'   => $values,
                                'visible'   => 1,
                                'variation' => 1
                            );
                        }

                        wc_update_product_attributes($post_id, $attributes_data);

                        // Load the product object
                        $product = wc_get_product($post_id);

                        // Filter out unavailable variations if setting is enabled
                        if ($remove_unavailable) {
                            $Variationlist = array_filter($Variationlist, function($variation) {
                                return !empty($variation['stock_status']) &&
                                       ($variation['regular_price'] > 0 || $variation['sale_price'] > 0);
                            });
                        }

                        // Apply the limit to the Variationlist
                        $limited_variations = array_slice($Variationlist, 0, $variation_limit);

                        // Collect SKUs/ASINs for the limited variations
                        $limited_variation_skus = array();
                        foreach ($limited_variations as $variation) {
                            $limited_variation_skus[] = $variation['sku'];
                        }

                        // Get existing variations
                        $existing_variations = $product->get_children();

                        // Remove variations not in the limited set
                        foreach ($existing_variations as $variation_id) {
                            $variation_sku = get_post_meta($variation_id, '_sku', true);
                            if (!in_array($variation_sku, $limited_variation_skus)) {
                                wp_delete_post($variation_id, true); // Permanently delete
                            }
                        }

                        // Update Variationlist to use only limited variations
                        $Variationlist = $limited_variations;

                        foreach ($Variationlist as $SingleVariation) {
                            // Skip if variation is unavailable and setting is enabled
                            if ($remove_unavailable &&
                                (empty($SingleVariation['stock_status']) ||
                                 ($SingleVariation['regular_price'] == 0 && $SingleVariation['sale_price'] == 0))) {
                                $existing_variation = get_product_by_sku($SingleVariation['sku']);
                                if ($existing_variation !== null) {
                                    wp_delete_post($existing_variation->get_id(), true);
                                }
                                continue;
                            }

                            $variation_post = array(
                                'post_title'  => $SingleVariation['post_title'],
                                'post_name'   => 'product-'.$post_id.'-variation-'.$SingleVariation['sku'],
                                'post_status' => 'publish',
                                'post_parent' => $post_id,
                                'post_type'   => 'product_variation',
                                'guid'        => $product->get_permalink()
                            );

                            $existing_variation = get_product_by_sku($SingleVariation['sku']);
                            if ($existing_variation !== null) {
                                $variation_post['ID'] = $variation_id = $existing_variation->get_id();
                                wp_update_post($variation_post);
                            } else {
                                $variation_id = wp_insert_post($variation_post);
                            }

                            $variation_obj = new \WC_Product_Variation($variation_id);

                            // Assign attributes
                            if (count($SingleVariation['attributes']) > 0) {
                                foreach ($SingleVariation['attributes'] as $attribute => $term_name) {
                                    $taxonomy   = 'pa_' . $attribute;
                                    $term_name  = esc_attr($term_name);

                                    // 1) If the taxonomy doesn't exist, skip
                                    if (!taxonomy_exists($taxonomy)) {
                                        continue;
                                    }

                                    // 2) If term doesn't exist, create it
                                    $maybe_term = term_exists($term_name, $taxonomy);
                                    if (!$maybe_term) {
                                        $inserted = wp_insert_term($term_name, $taxonomy);
                                        if (is_wp_error($inserted)) {
                                            continue;
                                        }
                                        $maybe_term = $inserted;
                                    }

                                    // 3) Fetch the term object
                                    $term_obj = get_term_by('name', $term_name, $taxonomy);
                                    if (!$term_obj) {
                                        continue;
                                    }

                                    // 4) Assign this term to the parent product
                                    wp_set_object_terms($post_id, [$term_obj->term_id], $taxonomy, true);

                                    // 5) Update variation meta
                                    update_post_meta($variation_id, 'attribute_' . $taxonomy, $term_obj->slug);
                                }
                            }

                            // Set SKU
                            if (!empty($SingleVariation['sku'])) {
                                $variation_obj->set_sku($SingleVariation['sku']);
                            }

                            // Set prices
                            if (empty($SingleVariation['sale_price'])) {
                                $variation_obj->set_price($SingleVariation['regular_price']);
                            } else {
                                $variation_obj->set_price($SingleVariation['sale_price']);
                                $variation_obj->set_sale_price($SingleVariation['sale_price']);
                            }
                            $variation_obj->set_regular_price($SingleVariation['regular_price']);

                            if (isset($SingleVariation['regular_price']) && !empty($SingleVariation['regular_price'])) {
                                update_post_meta($variation_id, 'regular_price', $SingleVariation['regular_price']);
                            }
                            if (isset($SingleVariation['sale_price']) && !empty($SingleVariation['sale_price'])) {
                                update_post_meta($variation_id, 'sale_price', $SingleVariation['sale_price']);
                            }

                            // Stock status
                            if (!empty($data['stock_status'])) {
                                $variation_obj->set_stock_status($data['stock_status']);
                            } else {
                                $variation_obj->set_manage_stock(false);
                            }

                            // Variant images
                            if (!empty($SingleVariation['product_image_gallery'])) {
                                // Remove existing thumbnail
                                $existing_thumbnail_id = get_post_thumbnail_id($variation_id);
                                if ($existing_thumbnail_id) {
                                    delete_post_thumbnail($variation_id);
                                    wp_delete_attachment($existing_thumbnail_id, true);
                                }

                                // Remove existing gallery
                                $existing_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
                                if ($existing_gallery) {
                                    $existing_gallery_ids = explode(',', $existing_gallery);
                                    foreach ($existing_gallery_ids as $gallery_image_id) {
                                        wp_delete_attachment($gallery_image_id, true);
                                    }
                                    delete_post_meta($variation_id, '_product_image_gallery');
                                }

                                // Reset Product Gallery Meta
                                delete_post_meta($variation_id, '_product_image_gallery');

                                // Apply image limit
                                $limited_gallery = array_slice($SingleVariation['product_image_gallery'], 0, $image_limit);

                                if ('Yes' === get_option('ams_remote_amazon_images')) {
                                    $gallery_image_urls = [];
                                    
                                    // Process gallery images
                                    foreach ($limited_gallery as $attachment) {
                                        $gallery_image_urls[] = esc_url($attachment);
                                    }

                                    // Set gallery images
                                    if (!empty($gallery_image_urls)) {
                                        // Set gallery
                                        attach_product_thumbnail_url_api($variation_id, $gallery_image_urls, 1);
                                        
                                        // Set featured image
                                        if (!empty($gallery_image_urls[0])) {
                                            attach_product_thumbnail_url_api($variation_id, $gallery_image_urls[0], 0);
                                        }
                                    }
                                } else {
                                    $gallery_image_ids = [];
                                    
                                    // Process gallery images
                                    foreach ($limited_gallery as $attachment) {
                                        $attachment_id = attach_product_thumbnail_api($variation_id, $attachment, 1);
                                        if ($attachment_id) {
                                            $gallery_image_ids[] = $attachment_id;
                                        }
                                    }

                                    // Set gallery images
                                    if (!empty($gallery_image_ids)) {
                                        update_post_meta($variation_id, '_product_image_gallery', implode(',', $gallery_image_ids));
                                        
                                        // Set featured image
                                        if (!empty($gallery_image_ids[0])) {
                                            set_post_thumbnail($variation_id, $gallery_image_ids[0]);
                                        }
                                    }
                                }
                            }

                            $variation_obj->set_weight('');
                            $variation_obj->save();
                        }
                    }
                }
            } else {
                // NOT CRON
                if (isset($variations->VariationDimensions) && !empty($variations->VariationDimensions)) {
                    $attributeChecks = [];
                    $attributes_data = [];
                    foreach ($variations->VariationDimensions as $attribute => $term_name ) {
                        $attr_label = $term_name->DisplayName;
                        $values     = $term_name->Values;
                        $values_array = implode('|', $values);
                        $attr_slug  = sanitize_title($term_name->Name);

                        $values = array_map('trim', $values);
                        $attributeChecks[$attr_slug] = sanitize_title($attr_label);

                        $attributes_data[] = array(
                            'name'      => $attr_label,
                            'slug'      => $attr_slug,
                            'options'   => $values,
                            'visible'   => 1,
                            'variation' => 1
                        );
                    }

                    wc_update_product_attributes($post_id, $attributes_data);

                    $product = wc_get_product($post_id);

                    // Filter out unavailable variations if setting is enabled
                    if ($remove_unavailable) {
                        $Variationlist = array_filter($Variationlist, function($variation) {
                            return !empty($variation['stock_status']) &&
                                ($variation['regular_price'] > 0 || $variation['sale_price'] > 0);
                        });
                        // Reindex
                        $Variationlist = array_values($Variationlist);
                    }

                    // Apply variation limit
                    $Variationlist = array_slice($Variationlist, 0, $variation_limit);

                    foreach ($Variationlist as $SingleVariation) {
                        // Skip if unavailable
                        if ($remove_unavailable &&
                            (empty($SingleVariation['stock_status']) ||
                             ($SingleVariation['regular_price'] == 0 && $SingleVariation['sale_price'] == 0))) {
                            $existing_variation = get_product_by_sku($SingleVariation['sku']);
                            if ($existing_variation !== null) {
                                wp_delete_post($existing_variation->get_id(), true);
                            }
                            continue;
                        }

                        $variation_post = array(
                            'post_title'  => $SingleVariation['post_title'],
                            'post_name'   => 'product-'.$post_id.'-variation-'.$SingleVariation['sku'],
                            'post_status' => 'publish',
                            'post_parent' => $post_id,
                            'post_type'   => 'product_variation',
                            'guid'        => $product->get_permalink()
                        );

                        $existing_variation = get_product_by_sku($SingleVariation['sku']);
                        if ($existing_variation !== null) {
                            $variation_post['ID'] = $variation_id = $existing_variation->get_id();
                            wp_update_post($variation_post);
                        } else {
                            $variation_id = wp_insert_post($variation_post);
                        }

                        $variation_obj = new \WC_Product_Variation($variation_id);

                        if (count($SingleVariation['attributes']) > 0) {
                            foreach ($SingleVariation['attributes'] as $attribute => $term_name) {
                                $taxonomy  = 'pa_' . $attribute;
                                $term_name = esc_attr($term_name);

                                // 1) If the taxonomy doesn't exist, skip
                                if (!taxonomy_exists($taxonomy)) {
                                    continue;
                                }

                                // 2) If term doesn't exist, create it
                                $maybe_term = term_exists($term_name, $taxonomy);
                                if (!$maybe_term) {
                                    $inserted = wp_insert_term($term_name, $taxonomy);
                                    if (is_wp_error($inserted)) {
                                        continue;
                                    }
                                    $maybe_term = $inserted;
                                }

                                // 3) Get term object
                                $term_obj = get_term_by('name', $term_name, $taxonomy);
                                if (!$term_obj) {
                                    continue;
                                }

                                // 4) Assign this term to the parent
                                wp_set_object_terms($post_id, [$term_obj->term_id], $taxonomy, true);

                                // 5) Assign to variation
                                update_post_meta($variation_id, 'attribute_'.$taxonomy, $term_obj->slug);
                            }
                        }

                        // Set SKU
                        if (!empty($SingleVariation['sku'])) {
                            $variation_obj->set_sku($SingleVariation['sku']);
                        }

                        // Set prices
                        if (empty($SingleVariation['sale_price'])) {
                            $variation_obj->set_price($SingleVariation['regular_price']);
                        } else {
                            $variation_obj->set_price($SingleVariation['sale_price']);
                            $variation_obj->set_sale_price($SingleVariation['sale_price']);
                        }
                        $variation_obj->set_regular_price($SingleVariation['regular_price']);

                        if (isset($SingleVariation['regular_price']) && !empty($SingleVariation['regular_price'])) {
                            update_post_meta($variation_id, 'regular_price', $SingleVariation['regular_price']);
                        }
                        if (isset($SingleVariation['sale_price']) && !empty($SingleVariation['sale_price'])) {
                            update_post_meta($variation_id, 'sale_price', $SingleVariation['sale_price']);
                        }

                        // Stock
                        if (!empty($data['stock_status'])) {
                            $variation_obj->set_stock_status($data['stock_status']);
                        } else {
                            $variation_obj->set_manage_stock(false);
                        }

                        // Variation images
                        if (!empty($SingleVariation['product_image_gallery'])) {
                            // Remove existing thumbnail
                            $existing_thumbnail_id = get_post_thumbnail_id($variation_id);
                            if ($existing_thumbnail_id) {
                                delete_post_thumbnail($variation_id);
                                wp_delete_attachment($existing_thumbnail_id, true);
                            }

                            // Remove existing gallery
                            $existing_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
                            if ($existing_gallery) {
                                $existing_gallery_ids = explode(',', $existing_gallery);
                                foreach ($existing_gallery_ids as $gallery_image_id) {
                                    wp_delete_attachment($gallery_image_id, true);
                                }
                                delete_post_meta($variation_id, '_product_image_gallery');
                            }

                            // Reset Product Gallery Meta
                            delete_post_meta($variation_id, '_product_image_gallery');

                            // Apply image limit
                            $limited_gallery = array_slice($SingleVariation['product_image_gallery'], 0, $image_limit);

                            if ('Yes' === get_option('ams_remote_amazon_images')) {
                                $gallery_image_urls = [];
                                foreach ($limited_gallery as $attachment) {
                                    $gallery_image_urls[] = esc_url($attachment);
                                }
                                if (!empty($gallery_image_urls)) {
                                    attach_product_thumbnail_url_api($variation_id, $gallery_image_urls, 1);
                                    if (!empty($gallery_image_urls[0])) {
                                        attach_product_thumbnail_url_api($variation_id, $gallery_image_urls[0], 0);
                                    }
                                }
                            } else {
                                $gallery_image_ids = [];
                                foreach ($limited_gallery as $attachment) {
                                    $attachment_id = attach_product_thumbnail_api($variation_id, $attachment, 1);
                                    if ($attachment_id) {
                                        $gallery_image_ids[] = $attachment_id;
                                    }
                                }
                                if (!empty($gallery_image_ids)) {
                                    update_post_meta($variation_id, '_product_image_gallery', implode(',', $gallery_image_ids));
                                    if (!empty($gallery_image_ids[0])) {
                                        set_post_thumbnail($variation_id, $gallery_image_ids[0]);
                                    }
                                }
                            }
                        }

                        $variation_obj->set_weight('');
                        $variation_obj->save();
                    }
                }
            }
            ########### End Create product Variations ###########


            $Current = get_post_meta( $post_id, '_stock_status',true);
            if(!isset($_POST['is_cron']) || $_POST['is_cron'] != 1){ 
                update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
                update_post_meta( $post_id, 'ams_last_cron_status',0);
                update_post_meta( $post_id, '_stock_status', $product_status );
                $message = "Success";
            } else {
                update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
                update_post_meta( $post_id, 'ams_last_cron_status',1);
                
                if (get_option('product_out_of_stock_cron') == 1) {
                    update_post_meta( $post_id, '_stock_status', $product_status );
                }else {
                    update_post_meta( $post_id, '_stock_status', $Current );
                }
                
                $short_title = wp_trim_words($title, 3, '...');
                $short_url = (strlen($detail_page_url) > 25) ? substr($detail_page_url, 0, 22) . '...' : $detail_page_url;
                $message = sprintf(
                    '%s %s <a href="%s" target="_blank" class="text-white">%s</a>',
                    esc_html__('Updated:', 'ams-wc-amazon'),
                    esc_html($short_title),
                    esc_url($detail_page_url),
                    esc_html($short_url)
                );
            }
            echo wp_kses_post($message);
        }
    } catch (Throwable $th) {
        update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
        update_post_meta( $post_id, 'ams_last_cron_status',0);
        echo wp_kses_post($th->getMessage());
    }
    wp_die();
}

// API Update
private function ams_product_api_update($post_id, $asin) {
    try {
        // Check License
        if (ams_plugin_license_status() === false) {
            echo "<script>console.log('Plugin license not activated');</script>";
            $license = sprintf(esc_html__('Activate License!','ams-wc-amazon'));
            echo wp_kses_post($license);
            wp_die();
        }

        $product_region = get_post_meta( $post_id, '_region', true );
        $locale = get_option( 'ams_amazon_country' );
        $regions = ams_get_amazon_regions();
        $marketplace = 'www.amazon.'. get_option( 'ams_amazon_country' );
        $service_name = 'ProductAdvertisingAPI';
        $region = $regions[ $locale ]['RegionCode'];
        $access_key = get_option( 'ams_access_key_id' );
        $secret_key = get_option( 'ams_secret_access_key' );
        $payload_arr = array();
        $payload_arr['ItemIds'] = array( $asin );

        $payload_arr['Resources'] = array(
            // Category and Relationships
            "BrowseNodeInfo.BrowseNodes.Ancestor",  // Include category information
            "ParentASIN",

            // Images
            "Images.Primary.Small",
            "Images.Primary.Medium",
            "Images.Primary.Large",
            "Images.Variants.Small",
            "Images.Variants.Medium",
            "Images.Variants.Large",

            // Basic Product Information
            "ItemInfo.ByLineInfo",
            "ItemInfo.ContentInfo",
            "ItemInfo.ContentRating",
            "ItemInfo.Classifications",
            "ItemInfo.ExternalIds",
            "ItemInfo.Features",
            "ItemInfo.ManufactureInfo",
            "ItemInfo.ProductInfo",
            "ItemInfo.TechnicalInfo",
            "ItemInfo.Title",
            "ItemInfo.TradeInInfo",

            // Offers and Availability
            "Offers.Listings.Availability.MaxOrderQuantity",
            "Offers.Listings.Availability.Message",
            "Offers.Listings.Availability.MinOrderQuantity",
            "Offers.Listings.Availability.Type",
            "Offers.Listings.Condition",
            "Offers.Listings.Condition.ConditionNote",
            "Offers.Listings.Condition.SubCondition",
            "Offers.Listings.DeliveryInfo.IsAmazonFulfilled",
            "Offers.Listings.DeliveryInfo.IsFreeShippingEligible",
            "Offers.Listings.DeliveryInfo.IsPrimeEligible",
            "Offers.Listings.DeliveryInfo.ShippingCharges",
            "Offers.Listings.IsBuyBoxWinner",
            "Offers.Listings.LoyaltyPoints.Points",
            "Offers.Listings.MerchantInfo",
            "Offers.Listings.Price",
            "Offers.Listings.ProgramEligibility.IsPrimeExclusive",
            "Offers.Listings.ProgramEligibility.IsPrimePantry",
            "Offers.Listings.Promotions",
            "Offers.Listings.SavingBasis",

            // Offer Summaries
            "Offers.Summaries.HighestPrice",
            "Offers.Summaries.LowestPrice",
            "Offers.Summaries.OfferCount"
        );

        $payload_arr['PartnerTag'] = get_option( 'ams_associate_tag' );
        $payload_arr['PartnerType'] = 'Associates';
        $payload_arr['Marketplace'] = $marketplace;
        $payload_arr['Operation'] = 'GetItems';
        $payload = wp_json_encode( $payload_arr );
        $host = $regions[ $locale ]['Host'];
        $uri_path = "/paapi5/getitems";
        $api = new \Amazon\Affiliate\Api\Amazon_Product_Api ( $access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems' );
        $response = $api->do_request();

        // Check if ItemsResult and Items are set before accessing them
        if (isset($response->ItemsResult) && isset($response->ItemsResult->Items)) {
            $results = $response->ItemsResult->Items;
        } else {
            $results = null;
        }

        if(empty($results)) {
            sleep(3);
            $apiRetried = new \Amazon\Affiliate\Api\Amazon_Product_Api($access_key, $secret_key, $region, $service_name, $uri_path, $payload, $host, 'GetItems');
            $response = $apiRetried->do_request();
            
            // Check if ItemsResult and Items are set before accessing them
            if (isset($response->ItemsResult) && isset($response->ItemsResult->Items)) {
                $results = $response->ItemsResult->Items;
            } else {
                $results = null;
            }

            if(empty($results)) {
                update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
                update_post_meta( $post_id, 'ams_last_cron_status', 0 );
                return FALSE;
            }
        }

        foreach ($results as $row) {
            $thumbnail_size  = get_option( 'ams_product_thumbnail_size' );
            $asin            = $row->ASIN;
            $parentASIN = isset($row->ParentASIN) ? $row->ParentASIN : null;

            $detail_page_url = $row->DetailPageURL;
            if(isset($row->Images->Primary->{$thumbnail_size})) {
                $image = $row->Images->Primary->{$thumbnail_size}->URL;
            } else {
                $key = key((array)$row->Images->Primary);
                $image = $row->Images->Primary->{$key}->URL;
            }
            $gallery = isset($row->Images->Variants) ? $row->Images->Variants : null;

            $amount = isset($row->Offers->Listings[0]->Price->Amount) ? $row->Offers->Listings[0]->Price->Amount : 0;
            $saving_amount = isset($row->Offers->Listings[0]->SavingBasis->Amount) ? $row->Offers->Listings[0]->SavingBasis->Amount : 0;
            $DisplayAmount = isset($row->Offers->Listings[0]->Price->DisplayAmount) ? $row->Offers->Listings[0]->Price->DisplayAmount : '';
            $SavingDisplayAmount = isset($row->Offers->Listings[0]->SavingBasis->DisplayAmount) ? $row->Offers->Listings[0]->SavingBasis->DisplayAmount : null;
            $product_status = isset($row->Offers->Listings[0]->Availability->Message) ? $row->Offers->Listings[0]->Availability->Message : '';
            $product_status  = !empty($product_status) ? 'instock' : 'outofstock';
            $title           = $row->ItemInfo->Title->DisplayValue;
            $features = isset($row->ItemInfo->Features->DisplayValues) ? $row->ItemInfo->Features->DisplayValues : null;
            $payload_arr2 = array();
            $payload_arr2['ASIN']     = $asin;//'B00T0C9XRK';

            $payload_arr2['Resources'] = array(
                "ParentASIN", "ItemInfo.Title", "Offers.Listings.Price",
                "Offers.Listings.ProgramEligibility.IsPrimeExclusive", 
                "Offers.Listings.ProgramEligibility.IsPrimePantry", 
                "Offers.Listings.Promotions", 
                "Offers.Listings.SavingBasis", 
                "Offers.Listings.Availability.Message", 
                "Offers.Summaries.HighestPrice", 
                "Offers.Summaries.LowestPrice", 
                "VariationSummary.Price.HighestPrice", 
                "VariationSummary.Price.LowestPrice", 
                "VariationSummary.VariationDimension",
                "Images.Variants.Large",
                "Images.Variants.Medium",
                "Images.Variants.Small"
            );

            $payload_arr2['PartnerTag']  = get_option( 'ams_associate_tag' );
            $payload_arr2['PartnerType'] = 'Associates';
            $payload_arr2['Marketplace'] = $marketplace; //'www.amazon.com';
            $payload_arr2['Operation']   = 'GetVariations';
            $payload2                   = json_encode($payload_arr2);
            $host                      = $regions[ $locale ]['Host'];
            $uri_path                  = "/paapi5/getvariations";
            $api2                      = new  \Amazon\Affiliate\Api\Amazon_Product_Api ($access_key, $secret_key,$region, $service_name, $uri_path, $payload2, $host, 'getVariation' );

            $response2 = $api2->do_request();

            if (isset($response2->VariationsResult)) {
                $variations = $response2->VariationsResult->VariationSummary;
                $attributes = $response2->VariationsResult->Items;
            } else {
                $variations = null;
                $attributes = null;
            }

            $VariationPage = 2;
            $Variationlist = [];


            // Variants
            $Variationlist = array(); // Initialize empty array
            $variation_limit = get_option('ams_variation_limit', 5);
            $image_limit = get_option('ams_variation_image_limit', 5);
            $remove_unavailable = get_option('ams_remove_unavailable_products') === 'Yes';

            if (isset($variations->PageCount) && $variations->PageCount >= 1) {
                // Load the product object using the post_id
                $product = wc_get_product($post_id);

                // Check if the product exists and is valid
                if (!$product) {
                    throw new Exception("Product not found or invalid for post_id: " . $post_id);
                }

                // Apply the limit to the list of variations
                $limited_variations = array_slice($response2->VariationsResult->Items, 0, $variation_limit);

                // Collect ASINs for the limited variations
                $limited_variation_asins = array();
                foreach ($limited_variations as $item) {
                    // Check availability
                    $amount = isset($item->Offers->Listings[0]->Price->Amount) ? $item->Offers->Listings[0]->Price->Amount : 0;
                    $saving_amount = isset($item->Offers->Listings[0]->SavingBasis->Amount) ? $item->Offers->Listings[0]->SavingBasis->Amount : 0;
                    $product_stock = isset($item->Offers->Listings[0]->Availability->Message) ? $item->Offers->Listings[0]->Availability->Message : '';

                    // Skip if unavailable and setting is enabled
                    if ($remove_unavailable && (empty($product_stock) || ($amount == 0 && $saving_amount == 0))) {
                        continue;
                    }

                    $limited_variation_asins[] = $item->ASIN;
                }

                // Fetch existing variations for the product
                $existing_variations = $product->get_children();

                // Loop through the existing variations and remove those not in the limited set
                foreach ($existing_variations as $variation_id) {
                    $variation_sku = get_post_meta($variation_id, '_sku', true); // Assuming ASIN is stored as SKU

                    // If the existing variation SKU is not in the limited set, delete it
                    if (!in_array($variation_sku, $limited_variation_asins)) {
                        wp_delete_post($variation_id, true); // Permanently delete the variation
                    }
                }

                // Process the limited variations
                foreach ($limited_variations as $item) {
                    if (!isset($item->ASIN) || empty($item->ASIN)) {
                        continue; // Skip if ASIN is missing
                    }

                    // Check if variation should be skipped due to unavailability
                    $amount = isset($item->Offers->Listings[0]->Price->Amount) ? $item->Offers->Listings[0]->Price->Amount : 0;
                    $DisplayAmount = isset($item->Offers->Listings[0]->Price->DisplayAmount) ? $item->Offers->Listings[0]->Price->DisplayAmount : null;
                    $saving_amount = isset($item->Offers->Listings[0]->SavingBasis->Amount) ? $item->Offers->Listings[0]->SavingBasis->Amount : 0;
                    $SavingDisplayAmount = isset($item->Offers->Listings[0]->SavingBasis->DisplayAmount) ? $item->Offers->Listings[0]->SavingBasis->DisplayAmount : null;
                    $product_stock = isset($item->Offers->Listings[0]->Availability->Message) ? $item->Offers->Listings[0]->Availability->Message : '';
                    $stock_status = !empty($product_stock) ? 'instock' : 'outofstock';

                    // Skip unavailable variations if setting is enabled
                    if ($remove_unavailable && (empty($product_stock) || ($amount == 0 && $saving_amount == 0))) {
                        // Remove if it exists
                        $existing_variation = get_product_by_sku($item->ASIN);
                        if ($existing_variation !== null) {
                            wp_delete_post($existing_variation->get_id(), true);
                        }
                        continue;
                    }

                    $VariationAttribute = [];
                    if (isset($item->VariationAttributes)) {
                        foreach ($item->VariationAttributes as $ItemVariationAttribute) {
                            if (isset($ItemVariationAttribute->Name) && isset($ItemVariationAttribute->Value)) {
                                $VariationAttribute[$ItemVariationAttribute->Name] = trim($ItemVariationAttribute->Value);
                            }
                        }
                    }

                    // Set prices
                    if (empty($saving_amount)) {
                        $sale_price = $amount;
                        $regular_price = $amount;
                    } else {
                        $sale_price = $amount;
                        $regular_price = $saving_amount;
                    }

                    // Process variant images
                    $v_gallery = [];
                    
                    // Check for variant images
                    if (isset($item->Images->Variants)) {
                        foreach ($item->Images->Variants as $variant_image) {
                            if (isset($variant_image->Large->URL)) {
                                $v_gallery[] = $variant_image->Large->URL;
                            }
                        }
                    }
                    
                    // Fallback to primary image if no variant images
                    if (empty($v_gallery) && isset($item->Images->Primary->Large->URL)) {
                        $v_gallery[] = $item->Images->Primary->Large->URL;
                    }

                    // Apply image limit
                    if ($image_limit > 0) {
                        $v_gallery = array_slice($v_gallery, 0, $image_limit);
                    }

                    // Only add to Variationlist if we have required fields
                    if (isset($item->ItemInfo->Title->DisplayValue)) {
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
                }
            }

            // Process additional variation pages
            while ($VariationPage <= ($variations->PageCount ?? 0)) {
                // Skip if we've already reached our variation limit
                if (count($Variationlist) >= $variation_limit) {
                    break;
                }

                $payload_arr2['VariationPage'] = $VariationPage;
                $payload3 = json_encode($payload_arr2);
                $api3 = new \Amazon\Affiliate\Api\Amazon_Product_Api(
                    $access_key, $secret_key, $region, $service_name, $uri_path, $payload3, $host, 'getVariation'
                );
                $response3 = $api3->do_request();

                if (isset($response3->VariationsResult) && isset($response3->VariationsResult->Items)) {
                    foreach ($response3->VariationsResult->Items as $item) {
                        // Break if we've reached the variation limit
                        if (count($Variationlist) >= $variation_limit) {
                            break 2; // Break both foreach and while loops
                        }

                        if (!isset($item->ASIN) || empty($item->ASIN)) {
                            continue; // Skip if ASIN is missing
                        }

                        // Check if variation should be skipped due to unavailability
                        $amount = isset($item->Offers->Listings[0]->Price->Amount) ? $item->Offers->Listings[0]->Price->Amount : 0;
                        $DisplayAmount = isset($item->Offers->Listings[0]->Price->DisplayAmount) ? $item->Offers->Listings[0]->Price->DisplayAmount : null;
                        $saving_amount = isset($item->Offers->Listings[0]->SavingBasis->Amount) ? $item->Offers->Listings[0]->SavingBasis->Amount : 0;
                        $SavingDisplayAmount = isset($item->Offers->Listings[0]->SavingBasis->DisplayAmount) ? $item->Offers->Listings[0]->SavingBasis->DisplayAmount : null;
                        $product_stock = isset($item->Offers->Listings[0]->Availability->Message) ? $item->Offers->Listings[0]->Availability->Message : '';
                        $stock_status = !empty($product_stock) ? 'instock' : 'outofstock';

                        // Skip unavailable variations if setting is enabled
                        if ($remove_unavailable && (empty($product_stock) || ($amount == 0 && $saving_amount == 0))) {
                            // Remove if it exists
                            $existing_variation = get_product_by_sku($item->ASIN);
                            if ($existing_variation !== null) {
                                wp_delete_post($existing_variation->get_id(), true);
                            }
                            continue;
                        }

                        $VariationAttribute = [];
                        if (isset($item->VariationAttributes)) {
                            foreach ($item->VariationAttributes as $ItemVariationAttribute) {
                                if (isset($ItemVariationAttribute->Name) && isset($ItemVariationAttribute->Value)) {
                                    $VariationAttribute[$ItemVariationAttribute->Name] = trim($ItemVariationAttribute->Value);
                                }
                            }
                        }

                        // Set prices
                        if (empty($saving_amount)) {
                            $sale_price = $amount;
                            $regular_price = $amount;
                        } else {
                            $sale_price = $amount;
                            $regular_price = $saving_amount;
                        }

                        // Process variant images
                        $v_gallery = [];
                        if (isset($item->Images->Variants)) {
                            foreach ($item->Images->Variants as $variant_image) {
                                if (isset($variant_image->Large->URL)) {
                                    $v_gallery[] = $variant_image->Large->URL;
                                }
                            }
                        }
                        if (empty($v_gallery) && isset($item->Images->Primary->Large->URL)) {
                            $v_gallery[] = $item->Images->Primary->Large->URL;
                        }

                        // Apply image limit
                        if ($image_limit > 0) {
                            $v_gallery = array_slice($v_gallery, 0, $image_limit);
                        }

                        // Only add to Variationlist if we have required fields
                        if (isset($item->ItemInfo->Title->DisplayValue)) {
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
                    }
                }

                $VariationPage++;
            }

            // Check if $variations is a valid object and if VariationDimensions exists and is not empty
            if (!is_object($variations) || !isset($variations->VariationDimensions) || empty($variations->VariationDimensions)) {
                // Set product type to 'simple' if no variations are available
                wp_set_object_terms($post_id, 'simple', 'product_type');
            } else {
                // Set product type to 'variable' if variations are available
                wp_set_object_terms($post_id, 'variable', 'product_type');
            }

            // Main product SKU update
            if (get_option('product_sku_cron', true)) {
                // Check if ASIN exists to update the main product SKU
                if (!empty($asin)) {
                    update_post_meta($post_id, '_sku', $asin);
                    //error_log("Main product SKU updated for post ID: " . $post_id);
                } else {
                    //error_log("Main product SKU not available for post ID: " . $post_id);
                }
            }

            // Product name update
            if (get_option('product_name_cron', true) == 1) {
                $postData = array('ID' => $post_id);
                $postData['post_title'] = stripslashes($title);
                wp_update_post($postData);
            }


            // Product description update
            if (get_option('product_description_cron', true) == 1) {
                $content = '';

                // Check if `Features.DisplayValues` has content and add it to the description
                if (isset($row->ItemInfo->Features->DisplayValues) && is_array($row->ItemInfo->Features->DisplayValues)) {
                    foreach ($row->ItemInfo->Features->DisplayValues as $feature) {
                        $content .= '<ul><li>' . $feature . '</li></ul>';
                    }
                }

                // Update post content if not empty
                if (!empty($content)) {
                    $postData = array('ID' => $post_id, 'post_content' => $content);
                    wp_update_post($postData);
                }
            }


            // Product category update
            if (get_option('product_category_cron', true) == 1) {
                $ams_default_category = get_option('ams_default_category', true);

                // If auto-import from Amazon is selected
                if (!empty($ams_default_category) && $ams_default_category == '_auto_import_amazon') {
                    $category_paths = [];
                    $min_depth = intval(get_option('ams_category_min_depth', 1));
                    $max_depth = intval(get_option('ams_category_max_depth', 6));

                    // Check if BrowseNodes exist in the API response
                    if (isset($row->BrowseNodeInfo->BrowseNodes)) {
                        $browse_nodes = $row->BrowseNodeInfo->BrowseNodes;

                        foreach ($browse_nodes as $node) {
                            $current_node = $node;
                            $node_parts = [];

                            while (isset($current_node->Ancestor)) {
                                array_unshift($node_parts, $current_node->Ancestor->DisplayName);
                                $current_node = $current_node->Ancestor;
                            }

                            array_push($node_parts, $node->DisplayName);

                            $cleaned_path = array_filter($node_parts, function ($category) {
                                return !in_array($category, ['Categories', 'Home', 'Root']);
                            });

                            // Ensure we get paths between min_depth and max_depth
                            if (count($cleaned_path) >= $min_depth) {
                                // Trim the cleaned path to match the max depth if necessary
                                $trimmed_path = array_slice($cleaned_path, 0, $max_depth);
                                // Store paths with '>' separator
                                $category_paths[] = implode(' > ', $trimmed_path);
                            }
                        }

                        if (!empty($category_paths)) {
                            $category_path = $category_paths[0];
                            $categories = explode(' > ', $category_path);
                            $parent_id = 0;
                            $category_ids = array();

                            foreach ($categories as $category_name) {
                                $category_name = trim($category_name);

                                if (preg_match('/[a-f0-9\-]{36,}/', $category_name)) {
                                    continue;
                                }

                                $existing_category = term_exists($category_name, 'product_cat', $parent_id);

                                if (!$existing_category) {
                                    $new_category = wp_insert_term($category_name, 'product_cat', array(
                                        'description' => $category_name,
                                        'parent' => $parent_id
                                    ));
                                    if (!is_wp_error($new_category)) {
                                        $parent_id = $new_category['term_id'];
                                        $category_ids[] = $parent_id;
                                    }
                                } else {
                                    $term = get_term_by('name', esc_attr($category_name), 'product_cat');
                                    if ($term) {
                                        $parent_id = $term->term_id;
                                        $category_ids[] = $parent_id;
                                    } else {
                                        $parent_id = $existing_category['term_id'];
                                        $category_ids[] = $parent_id;
                                    }
                                }
                            }

                            if (!empty($category_ids)) {
                                update_post_meta($post_id, '_product_categories_hierarchy', $category_path);
                                wp_set_object_terms($post_id, $category_ids, 'product_cat');
                            }
                        }
                    }
                } else {
                    // If a specific category is selected (not auto-import)
                    if (!empty($ams_default_category)) {
                        // Try to get the term
                        $term = null;
                        if (is_numeric($ams_default_category)) {
                            $term = get_term($ams_default_category, 'product_cat');
                        }
                        if (!$term) {
                            $term = get_term_by('id', $ams_default_category, 'product_cat');
                        }
                        if (!$term) {
                            $term = get_term_by('slug', $ams_default_category, 'product_cat');
                        }
                        if (!$term) {
                            $term = get_term_by('name', $ams_default_category, 'product_cat');
                        }

                        // Set the category
                        if ($term && !is_wp_error($term)) {
                            wp_set_object_terms($post_id, $term->term_id, 'product_cat');
                            //error_log('AMS Debug: Setting category to ' . $term->name . ' (ID: ' . $term->term_id . ')');
                        } else {
                            //error_log('AMS Debug: Could not find category for ' . print_r($ams_default_category, true));
                        }
                    }
                }

                // Clear the term cache for this post
                clean_post_cache($post_id);
                wp_cache_delete($post_id, 'product_cat_relationships');
            }

            // Update the brand name
            if (isset($row->ItemInfo->ByLineInfo->Brand->DisplayValue)) {
                $brandName = trim($row->ItemInfo->ByLineInfo->Brand->DisplayValue);

                logImportVerification('Brand: ' . $brandName);

                // Check if the brand already exists as a term
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

                    // Assign the brand to the product
                    wp_set_object_terms($post_id, intval($brandTermId), 'product_brand');

                    // Store the brand in post meta for further use
                    update_post_meta($post_id, '_product_brand', $brandName);

                    logImportVerification('Brand assigned to product successfully.');
                }
            } else {
                logImportVerification('Brand not found in the API response.');
            }
            // Update the brand name


            // Update images
            if ('1' == get_option('product_image_cron', true)) {
                if ($image || $gallery) {
                    // Remove existing images and URLs
                    delete_product_images($post_id);
                    reset_product_thumbnail_url($post_id, $flag = 0); // Reset featured image URL
                    delete_product_gallery_images($post_id);
                    reset_product_thumbnail_url($post_id, $flag = 1); // Reset gallery URL

                    $gallery_url = [];
                    $gallery = is_array($gallery) ? $gallery : [];

                    if ('Yes' === get_option('ams_remote_amazon_images')) {
                        // Set featured image URL (remote)
                        attach_product_thumbnail_url_api($post_id, $image, 0);

                        // Process and set gallery images (remote)
                        foreach ($gallery as $image) {
                            if (isset($image->{$thumbnail_size})) {
                                $gallery_url[] = $image->{$thumbnail_size}->URL;
                            } else {
                                // Fallback to any available size if specific size is missing
                                $imageArray = (array) $image;
                                $key = key($imageArray);
                                $gallery_url[] = $image->{$key}->URL;
                            }
                        }

                        // Apply the gallery image limit from plugin settings
                        $image_limit = get_option('ams_variation_image_limit', 5);
                        if ($image_limit > 0) {
                            $gallery_url = array_slice($gallery_url, 0, $image_limit);
                        }

                        if (!empty($gallery_url)) {
                            attach_product_thumbnail_url_api($post_id, $gallery_url, 1);
                        }

                    } else {
                        // Download and set the featured image locally
                        attach_product_thumbnail_api($post_id, $image, 0);

                        // Download and set the gallery images locally with image limit applied
                        if (count($gallery) > 0) {
                            $image_limit = get_option('ams_variation_image_limit', 5);
                            $limited_gallery = array_slice($gallery, 0, $image_limit);
                            foreach ($limited_gallery as $image) {
                                if (isset($image->{$thumbnail_size})) {
                                    attach_product_thumbnail_api($post_id, $image->{$thumbnail_size}->URL, 1);
                                } else {
                                    $imageArray = (array) $image;
                                    $key = key($imageArray);
                                    attach_product_thumbnail_api($post_id, $image->{$key}->URL, 1);
                                }
                            }
                        }
                    }
                }
            }


            $user_id = get_current_user();
            if(!isset($variations->VariationDimensions) || empty($variations->VariationDimensions)){
                // wp_set_object_terms( $post_id, 'simple', 'product_type');
                if( get_option('product_sku_cron', true) == 1 ) {
                    update_post_meta( $post_id, '_sku', $asin );
                }
                if( get_option('product_price_cron', true) == 1 ) {
                    if( empty($saving_amount) ) {
                        $price = $this->parsePriceStringnew($amount);
                        update_post_meta( $post_id, '_price', $price );
                        update_post_meta( $post_id, '_sale_price', $price );
                        update_post_meta( $post_id, '_regular_price', $price );
                    } else {
                        $sale_price = $this->parsePriceStringnew($amount);
                        $regular_price = $this->parsePriceStringnew($saving_amount);
                        update_post_meta( $post_id, '_price', $sale_price );
                        update_post_meta( $post_id, '_sale_price', $sale_price );
                        update_post_meta( $post_id, '_regular_price', $regular_price );
                    }
                }
            } else {
                // SKU
                if(get_option('product_sku_cron', true) == 1 && $parentASIN) {
                    update_post_meta( $post_id, '_sku', $parentASIN );
                }
            }


            ########### Update product Variations ###########        
            if (get_option('product_variants_cron', true) == 1) {
                if (isset($variations->VariationDimensions) && !empty($variations->VariationDimensions)) {
                    wp_set_object_terms($post_id, 'variable', 'product_type');

                    $postData = [
                      'ID'           => $post_id,
                      'post_excerpt' => '',
                    ];
                    wp_update_post($postData);



                    $attributeChecks = [];
                    $attributes_data = [];
            
                    foreach ($variations->VariationDimensions as $attribute => $term_name) {
                        if (isset($term_name->Values, $term_name->DisplayName, $term_name->Name) && is_array($term_name->Values)) {
                            $values = array_map('trim', $term_name->Values);
                            $attr_label = $term_name->DisplayName;
                            $attr_slug = sanitize_title($term_name->Name);
            
                            $attributeChecks[$attr_slug] = sanitize_title($attr_label);
            
                            $attributes_data[] = array(
                                'name' => $attr_label, 
                                'slug' => $attr_slug, 
                                'options' => $values, 
                                'visible' => 1, 
                                'variation' => 1 
                            );
                        }
                    }
            
                    wc_update_product_attributes($post_id, $attributes_data);
            
                    $product = wc_get_product($post_id);

                    // Get the settings
                    $variation_limit = (int) get_option('ams_variation_limit', 5);
                    $remove_unavailable = get_option('ams_remove_unavailable_products') === 'Yes';

                    // Filter unavailable variations if setting is enabled
                    if ($remove_unavailable) {
                        $Variationlist = array_filter($Variationlist, function($variation) {
                            return isset($variation['stock_status']) && 
                                   $variation['stock_status'] !== 'outofstock' && 
                                   ($variation['regular_price'] > 0 || $variation['sale_price'] > 0);
                        });
                        // Reindex array after filtering
                        $Variationlist = array_values($Variationlist);
                    }

                    // Apply variation limit
                    $Variationlist = array_slice($Variationlist, 0, $variation_limit);

                    // Get existing variations to handle cleanup
                    $existing_variations = $product->get_children();
                    
                    // Collect SKUs of variations we'll keep
                    $valid_variation_skus = array_map(function($variation) {
                        return $variation['sku'];
                    }, $Variationlist);

                    // Remove variations that are not in our final list
                    foreach ($existing_variations as $variation_id) {
                        $variation_sku = get_post_meta($variation_id, '_sku', true);
                        if (!in_array($variation_sku, $valid_variation_skus)) {
                            wp_delete_post($variation_id, true);
                        }
                    }

                    foreach ($Variationlist as $SingleVariation) {
                        // Skip variations that don't meet our criteria
                        if (!isset($SingleVariation['sku']) || 
                            empty($SingleVariation['attributes']) || 
                            ($remove_unavailable && (
                                $SingleVariation['stock_status'] == 'outofstock' || 
                                ($SingleVariation['regular_price'] == 0 && $SingleVariation['sale_price'] == 0)
                            ))) {
                            continue;
                        }
            
                        $variation_post = array(
                            'post_title'  => $SingleVariation['post_title'],
                            'post_name'   => 'product-' . $post_id . '-variation-' . $SingleVariation['sku'],
                            'post_status' => 'publish',
                            'post_parent' => $post_id,
                            'post_type'   => 'product_variation',
                            'guid'        => $product->get_permalink()
                        );

                        $existing_variation = get_product_by_sku($SingleVariation['sku']);
                        if ($existing_variation !== null) {
                            $variation_post['ID'] = $variation_id = $existing_variation->get_id();
                            wp_update_post($variation_post);
                        } else {
                            $variation_id = wp_insert_post($variation_post);
                        }

                        $variation = new \WC_Product_Variation($variation_id);

                        if (count($SingleVariation['attributes']) > 0) {
                            foreach ($SingleVariation['attributes'] as $attribute => $term_name) {
                                $taxonomy = 'pa_' . $attribute;

                                // Ensure the taxonomy exists
                                if (!taxonomy_exists($taxonomy)) {
                                    continue; // Skip if the taxonomy doesn't exist
                                }

                                // If the term doesn't exist, create it
                                $maybe_term = term_exists($term_name, $taxonomy);
                                if (!$maybe_term) {
                                    $inserted = wp_insert_term($term_name, $taxonomy);
                                    if (is_wp_error($inserted)) {
                                        continue; // Skip if we fail to create
                                    }
                                    $maybe_term = $inserted;
                                }

                                // Fetch the term object
                                $term_obj = get_term_by('name', $term_name, $taxonomy);
                                if (!$term_obj) {
                                    continue; // Safety check
                                }

                                // Assign this term to the parent product
                                wp_set_object_terms($post_id, [$term_obj->term_id], $taxonomy, true);

                                // Assign the attribute to the variation
                                update_post_meta($variation_id, 'attribute_' . $taxonomy, $term_obj->slug);
                            }
                        }

                        if (!empty($SingleVariation['sku'])) {
                            $variation->set_sku($SingleVariation['sku']);
                        }

                        if (empty($SingleVariation['sale_price'])) {
                            $variation->set_price($SingleVariation['regular_price'] ?? '');
                        } else {
                            $variation->set_price($SingleVariation['sale_price'] ?? '');
                            $variation->set_sale_price($SingleVariation['sale_price'] ?? '');
                        }
                        $variation->set_regular_price($SingleVariation['regular_price'] ?? '');

                        if (!empty($SingleVariation['stock_status'])) {
                            $variation->set_stock_status($SingleVariation['stock_status']);
                        } else {
                            $variation->set_manage_stock(false);
                        }




                        // Product variant image gallery
                        if (!empty($SingleVariation['product_image_gallery'])) {
                            // Remove existing thumbnail
                            $existing_thumbnail_id = get_post_thumbnail_id($variation_id);
                            if ($existing_thumbnail_id) {
                                delete_post_thumbnail($variation_id);
                                wp_delete_attachment($existing_thumbnail_id, true);
                            }

                            // Remove existing gallery
                            $existing_gallery = get_post_meta($variation_id, '_product_image_gallery', true);
                            if ($existing_gallery) {
                                $existing_gallery_ids = explode(',', $existing_gallery);
                                foreach ($existing_gallery_ids as $gallery_image_id) {
                                    wp_delete_attachment($gallery_image_id, true);
                                }
                                delete_post_meta($variation_id, '_product_image_gallery');
                            }

                            // Reset Product Gallery Meta
                            delete_post_meta($variation_id, '_product_image_gallery');

                            // Apply image limit before processing
                            $limited_gallery = array_slice($SingleVariation['product_image_gallery'], 0, $image_limit);

                            if ('Yes' === get_option('ams_remote_amazon_images')) {
                                $gallery_image_urls = [];
                                
                                // Process gallery images
                                foreach ($limited_gallery as $attachment) {
                                    $gallery_image_urls[] = esc_url($attachment);
                                }

                                // Set gallery images
                                if (!empty($gallery_image_urls)) {
                                    // Set gallery
                                    attach_product_thumbnail_url_api($variation_id, $gallery_image_urls, 1);
                                    
                                    // Set featured image
                                    if (!empty($gallery_image_urls[0])) {
                                        attach_product_thumbnail_url_api($variation_id, $gallery_image_urls[0], 0);
                                    }
                                }
                            } else {
                                $gallery_image_ids = [];
                                
                                // Process gallery images using the limited gallery
                                foreach ($limited_gallery as $attachment) {
                                    $attachment_id = attach_product_thumbnail_api($variation_id, $attachment, 1);
                                    if ($attachment_id) {
                                        $gallery_image_ids[] = $attachment_id;
                                    }
                                }

                                // Set gallery images
                                if (!empty($gallery_image_ids)) {
                                    // Set gallery
                                    update_post_meta($variation_id, '_product_image_gallery', implode(',', $gallery_image_ids));
                                    
                                    // Set featured image
                                    if (!empty($gallery_image_ids[0])) {
                                        set_post_thumbnail($variation_id, $gallery_image_ids[0]);
                                    }
                                }
                            }
                        }

                        $variation->set_weight('');
                        $variation->save();
                    }
                }
            }
            ########### Update product Variations ###########


            // Update Product Review
            if ('1' == get_option('enable_amazon_review', true) && '1' == get_option('product_review_cron', true)) {
                // Get product URL
                $product_url = esc_url_raw($row->DetailPageURL);
                
                // Get user agent
                $user_agent = $this->user_agent();
                
                // Get product data
                $response_body = fetchAndValidateProductData($product_url, $user_agent, false);
                if (is_string($response_body) && !empty($response_body)) {
                    if (!class_exists('simple_html_dom')) {
                        require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
                    }
                    
                    $html = new \simple_html_dom();
                    $html->load($response_body);

                    // Check for broken page
                    $message = check_for_broken_page($response_body, $html);
                    if ($message === null) {
                        // Get review limit from settings
                        $review_limit = get_option('multiple_import_review_limit', 10);
                        
                        // Scrape the reviews
                        $reviews = scrape_amazon_reviews($html, $review_limit);

                        if (!empty($reviews)) {
                            // Get existing reviews
                            $existing_reviews = get_comments([
                                'post_id' => $post_id,
                                'type' => 'review',
                                'status' => 'approve'
                            ]);
                            
                            // Create array of existing review hashes
                            $existing_hashes = [];
                            foreach ($existing_reviews as $existing_review) {
                                $existing_hash = get_comment_meta($existing_review->comment_ID, 'review_hash', true);
                                if (!empty($existing_hash)) {
                                    $existing_hashes[$existing_hash] = $existing_review->comment_ID;
                                }
                            }

                            // Process each review
                            foreach ($reviews as $review_hash => $review) {
                                // Check if review exists
                                if (isset($existing_hashes[$review_hash])) {
                                    continue; // Skip existing reviews in cron
                                }

                                // Prepare comment data for new review
                                $commentdata = [
                                    'comment_post_ID' => $post_id,
                                    'comment_author' => $review['reviewer_name'],
                                    'comment_content' => $review['text'],
                                    'comment_date' => $review['date'],
                                    'comment_date_gmt' => get_gmt_from_date($review['date']),
                                    'comment_approved' => 1,
                                    'comment_type' => 'review',
                                    'user_id' => 0,
                                    'comment_author_email' => '',
                                    'comment_author_url' => '',
                                    'comment_author_IP' => '',
                                    'comment_agent' => ''
                                ];

                                // Insert new review
                                $comment_id = wp_insert_comment($commentdata);

                                // Add comment meta if insert successful
                                if ($comment_id) {
                                    add_comment_meta($comment_id, 'rating', $review['rating']);
                                    add_comment_meta($comment_id, 'review_hash', $review_hash);
                                    add_comment_meta($comment_id, 'verified', 1);
                                    add_comment_meta($comment_id, 'title', $review['title']);

                                    if (!empty($review['reviewer_image'])) {
                                        add_comment_meta($comment_id, 'reviewer_image', $review['reviewer_image']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Update Product Review    

            update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
            update_post_meta( $post_id, 'ams_last_cron_status',1);
            
            $Current = get_post_meta( $post_id, '_stock_status', true );

            if( get_option('product_out_of_stock_cron', true) == 1 ) {
                update_post_meta( $post_id, '_stock_status', $product_status );
            } else {
                update_post_meta( $post_id, '_stock_status', $Current );
            }
        } 

        clean_completed_woocommerce_actions();
        clean_all_actionscheduler_logs();

        return TRUE;
    } catch (Throwable $th) {
        update_post_meta( $post_id, 'ams_last_cron_update',date('Y-m-d H:i:s'));
        update_post_meta( $post_id, 'ams_last_cron_status',0);
        return FALSE;
    }
}

// NO-API Update
public function product_update_request() {
    try {
        if (!wp_verify_nonce($_POST['nonce'], 'product_update_request')) die('Busted!');

        if (ams_plugin_license_status() === false) {
            echo "<script>console.log('Plugin license not activated');</script>";
            $license = sprintf(esc_html__('Activate License!', 'ams-wc-amazon'));
            echo wp_kses_post($license);
            wp_die();
        }

        $post_id = sanitize_text_field($_POST['post_id']);
        $product_url = $_POST['product_url'];
        logImportVerification('Product URL: ', $product_url);

        if ('1' == get_post_meta($post_id, '_import_method', true)) {
            if (ams_plugin_license_status() === false) {
                echo "<script>console.log('Plugin license not activated');</script>";
                $license = sprintf(esc_html__('Activate License!', 'ams-wc-amazon'));
                echo wp_kses_post($license);
                wp_die();
            }
            $sku = $this->getSkuFromUrl($product_url);
            $response = $this->ams_product_api_update($post_id, $sku);

            if ($response) {
                echo 'Success';
            } else {
                echo 'Try again!';
            }
            wp_die();
        }

        // If Added in CRON            
        $product_sku_cron = get_option('product_sku_cron', true);
        $product_tags_cron = get_option('product_tags_cron', true);
        $product_name_cron = get_option('product_name_cron', true);
        $product_price_cron = get_option('product_price_cron', true);
        $product_image_cron = get_option('product_image_cron', true);
        $product_review_cron = get_option('product_review_cron', true);
        $enable_amazon_review = get_option('enable_amazon_review', true);
        $product_variants_cron = get_option('product_variants_cron', true);
        $product_variant_image_cron = get_option('product_variant_image_cron', true);
        $product_category_cron = get_option('product_category_cron', true);
        $product_description_cron = get_option('product_description_cron', true);
        $product_out_of_stock_cron = get_option('product_out_of_stock_cron', true);

        // Get product data first time
        $user_agent = $this->user_agent();
        $response_body = fetchAndValidateProductData($product_url, $user_agent, false);

        if (is_string($response_body) && strlen($response_body)) {
            if (!class_exists('simple_html_dom')) {
                require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
            }

            $html = new \simple_html_dom();
            $html->load($response_body);
            logImportVerification('Product update started...', null);

            // Check for broken page
            $message = check_for_broken_page($response_body, $html);
            if ($message !== null) {
                echo wp_kses_post($message);
                logImportVerification($message, null);
                wp_die();
            }

            // Extract Asin from product_url
            $asin = extractAsin($html, $product_url);
            if (empty($asin)) {
                die(esc_html__('ASIN not found!', 'ams-wc-amazon'));
            }

            // Get Parent ASIN from html
            $parentSku = $this->getParentSkuFromHtml($html);
            if (!empty($parentSku)) {
                logImportVerification('Valid parent SKU found: ', $parentSku);
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

            // Get Product Category
            $product_category = $this->syncAndGetCategory($html);

            // Get Product Content
            $content = $this->fetchContentFromHtml($html);
            //echo '<pre>'; dd( $content ); echo '</pre>';
            //echo '<pre>'; dd( $content ); echo '</pre>'; exit;

            // Get Product Short Description
            $short_description = $this->fetchShortDescriptionFromHtml($html);

            // Get Product Additional Content
            $additional_description = $this->fetchAdditionalContentFromHtml($html);
            //echo '<pre>'; dd( $additional_description ); echo '</pre>'; exit;

            clean_completed_woocommerce_actions();
            clean_all_actionscheduler_logs();


            // Update Product SKU
            if ($product_sku_cron) {
                $asinElements = $html->find('#ASIN');
                $asin = !empty($asinElements) ? $asinElements[0]->value : '';

                if (empty($asin)) {
                    $elements = $html->find('input[name="ASIN.0"]');
                    $asin = !empty($elements) ? $elements[0]->value : '';
                }

                if (empty($asin)) {
                    $asin = $this->getSkuFromUrl($product_url);
                }
                update_post_meta($post_id, '_sku', $asin);
            }

            // Update Product title
            if ($product_name_cron) {
                $product_update = array(
                    'ID' => $post_id,
                    'post_title' => $title,
                    'post_name' => sanitize_title($title)
                );
                wp_update_post($product_update);
                logImportVerification('Product Title Updated: ', $title);
            }
            // echo '<pre>'; dd( $title ); echo '</pre>';exit;

            // Update Product main content
            if ($product_description_cron) {
                if (!empty(trim($content))) {
                    $product_update = array(
                        'ID' => $post_id,
                        'post_content' => $content
                    );
                    wp_update_post($product_update);
                    logImportVerification('Product Content Updated!', null);
                }
            }

            // Update Product Category
            if ($product_category_cron) {
                $product_category = $this->syncAndGetCategory($html);
                if (!empty($product_category)) {
                    wp_set_object_terms($post_id, $product_category, 'product_cat');
                    logImportVerification('Product Category Updated: ', $product_category);
                }
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


            // Check remote amazon images.
            if($product_image_cron) {
                // Set product feature image.
                $gallery = $this->fetchImagesFromHtml($html);

                $image = array_shift($gallery);
                if( $image ) {
                    // Remove featured image and url.
                    delete_product_images($post_id);
                    reset_product_thumbnail_url($post_id, $flag=0);
                }
                
                if( count($gallery) > 0 ) {
                    // Remove product gallery images and url.
                    delete_product_gallery_images($post_id);
                    reset_product_thumbnail_url($post_id, $flag=1);
                }
                
                if ( 'Yes' === get_option( 'ams_remote_amazon_images' ) ) {
                    // Set featured image url
                    if( $image ) {
                        attach_product_thumbnail_url( $post_id, $image, 0 );
                    }
                    // Set featured image gallary
                    if( count($gallery) > 0 ) {
                        attach_product_thumbnail_url( $post_id, $gallery, 1 );
                    }
                } else {
                    // Set featured image url
                    if( $image ) {
                        attach_product_thumbnail($post_id, $image, 0);
                    }
                    // Set featured image gallary
                    if( count($gallery) > 0 ) {
                        foreach( $gallery as $image ) {
                            // Set gallery image.
                            attach_product_thumbnail( $post_id, $image, 1 );
                        }
                    }
                }
            }

            // Update Product Review
            if ($enable_amazon_review && $product_review_cron) {
                // Get review limit from settings
                $review_limit = get_option('multiple_import_review_limit', 10);
                
                // Scrape the reviews
                $reviews = scrape_amazon_reviews($html, $review_limit);
                
                logImportVerification("Processed " . count($reviews) . " reviews");

                if (!empty($reviews) && isset($post_id)) {
                    // Get existing reviews
                    $existing_reviews = get_comments([
                        'post_id' => $post_id,
                        'type' => 'review',
                        'status' => 'approve'
                    ]);
                    
                    // Create array of existing review hashes
                    $existing_hashes = [];
                    foreach ($existing_reviews as $existing_review) {
                        $existing_hash = get_comment_meta($existing_review->comment_ID, 'review_hash', true);
                        if (!empty($existing_hash)) {
                            $existing_hashes[$existing_hash] = $existing_review->comment_ID;
                        }
                    }

                    // Initialize rating totals
                    $rating_sum = 0;
                    $rating_count = 0;

                    // Process each review
                    foreach ($reviews as $review_hash => $review) {
                        // Skip if review already exists
                        if (isset($existing_hashes[$review_hash])) {
                            logImportVerification("Skipping duplicate review: " . $review['title']);
                            continue;
                        }

                        // Prepare comment data
                        $commentdata = [
                            'comment_post_ID' => $post_id,
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

                            logImportVerification("Added review: " . $review['title'] . " with ID: " . $comment_id);
                        }
                    }

                    // Update product rating if we added any new reviews
                    if ($rating_count > 0) {
                        $product = wc_get_product($post_id);
                        if ($product) {
                            // Get actual count of approved reviews
                            $actual_review_count = get_comments([
                                'post_id' => $post_id,
                                'type' => 'review',
                                'status' => 'approve',
                                'count' => true
                            ]);

                            // Calculate actual rating sum
                            $actual_rating_sum = 0;
                            $product_reviews = get_comments([
                                'post_id' => $post_id,
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
                            update_post_meta($post_id, '_wc_average_rating', round($new_average, 2));
                            update_post_meta($post_id, '_wc_rating_count', $actual_review_count);
                            update_post_meta($post_id, '_wc_review_count', $actual_review_count);
                            update_post_meta($post_id, '_wc_rating_sum', $actual_rating_sum);

                            // Clear all relevant caches
                            delete_transient('wc_product_reviews_' . $post_id);
                            delete_transient('wc_average_rating_' . $post_id);
                            wp_cache_delete($post_id, 'product');
                            
                            if (function_exists('wc_delete_product_transients')) {
                                wc_delete_product_transients($post_id);
                            }

                            logImportVerification("Updated product rating. New average: " . round($new_average, 2));
                        }
                    }

                    logImportVerification("Completed review import. Added " . $rating_count . " new reviews");
                }
            }
            // Update Product Review


            // Get Product attributes
            $attributes = $this->getProductAttributeFromHtml($html);
            //echo '<pre>'; dd( $attributes ); echo '</pre>';


            // Set variable if the product is simple despite attributes
            if (count($attributes) > 0) {
                // Check product type
                $terms = wp_get_object_terms($post_id, 'product_type');
                if (empty($terms) || $terms[0]->slug === 'simple') {
                    // Convert to variable
                    wp_set_object_terms($post_id, 'variable', 'product_type');
                    
                    // Clear cache
                    clean_post_cache($post_id);
                    wc_delete_product_transients($post_id);
                }
            }

            //Run if variable//
            if (count($attributes) > 0) {

                if($parentSku) {
                    update_post_meta($post_id, '_sku', $parentSku);
                }
                //echo '<pre>'; dd( $parentSku ); echo '</pre>'; exit;

                // Delete product short description
                $postData = array(
                    'ID' => $post_id,
                    'post_excerpt' => ''
                );
                wp_update_post($postData);

                // Update Additional Description
                if (!empty($additional_description)) {
                    update_post_meta($post_id, '_ams_additional_information', $additional_description);
                    logImportVerification('Additional description updated.', null);
                }

                $skus = $imported_skus = $product_variations = [];

                // Get all variants based on the SKUs found
                $all_skus = $this->getSkusFromHtml($html);
                //echo '<pre>'; print_r($all_skus); echo '</pre>';

                $variation_ids = $this->getProductFirstVariationFromHtml($html, $parentSku, $product_url, $all_skus);
                //echo '<pre>'; print_r($variation_ids); echo '</pre>';exit;

                // variations to process
                $variation_limit = get_option('ams_variation_limit', 5);

                // Update Variants
                if ($product_variants_cron && count($attributes) > 0) {

                    // Check if there are variation IDs:
                    if (!empty($variation_ids) && count($variation_ids) > 0) {

                    // Apply the dynamic variations to process
                    $variation_ids = array_slice($variation_ids, 0, $variation_limit);
                    //echo '<pre>'; print_r($variation_ids); echo '</pre>';

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

                        if ($product_variant_image_cron) {
                            $v_gallery = $this->fetchImagesFromHtml($loop_html);
                            $image_limit = get_option('ams_variation_image_limit', 5);
                            if ($image_limit > 0) {
                                $v_gallery = array_slice($v_gallery, 0, $image_limit);
                            }
                        }

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
                    //echo '<pre>'; dd( $product_variations ); echo '</pre>';
                    if (count($product_variations) > 0) {
                        wc_update_product_variations($post_id, $product_variations, $parentSku);
                    }
                }

                logImportVerification('Variable product updated!', null);
            }

            //Run if simple//
            else {
                // Initialize the $product object
                $product = wc_get_product($post_id);

                // Product price Start
                $productPrice = $this->fetchPriceFromHtml($html);
                $regular_price = isset($productPrice['regular_price']) ? $productPrice['regular_price'] : 0;
                $sale_price = isset($productPrice['sale_price']) ? $productPrice['sale_price'] : 0;
                logImportVerification('Regular price: ', $regular_price);
                logImportVerification('Sale price: ', $sale_price);

                // Currency
                $currency = $this->fetchCurrencyFromHtml($html);
                logImportVerification('Currency: ', $currency);

                // Determine initial stock status based on price availability
                if ($regular_price > 0 || $sale_price > 0) {
                    $product_status = 'instock';
                } else {
                    // If no prices found, proceed with the original stock check
                    $product_status = check_product_stock_status($html);
                }

                logImportVerification('Initial Stock status: ', $product_status);

                // Out Of Stock check moved to the beginning
                if ($product_out_of_stock_cron) {
                    if ($product_status == 'outofstock' && 'Yes' == get_option('ams_remove_unavailable_products')) {
                        removeProductIfNotExists($post_id);
                        logImportVerification('Product removal: ', "Product with ID $post_id has been processed for removal as it is out of stock.");
                        wp_die(
                            esc_html__('OutOfStock!', 'ams-wc-amazon'),
                            ['response' => 200]
                        );
                    }

                    // Quantity
                    $quantity = 0;
                    $qty = $html->find('#availability span', 0);
                    if ($qty) {
                        $quantity = $this->parseNumberFromString($qty->text());
                    }

                    if ($quantity > 0) {
                        update_post_meta($post_id, '_stock', $quantity);
                        update_post_meta($post_id, '_manage_stock', 'yes');
                        update_post_meta($post_id, '_stock_status', $product_status);
                    } else {
                        update_post_meta($post_id, '_stock', '');
                        update_post_meta($post_id, '_manage_stock', 'no');
                        update_post_meta($post_id, '_stock_status', $product_status);
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

                // Update Short Description
                if (!empty($short_description)) {
                    $product->set_short_description($short_description);
                    logImportVerification('Short description updated.', null);
                    $product->save();
                }

                // Update Additional Description
                if (!empty($additional_description)) {
                    update_post_meta($post_id, '_ams_additional_information', $additional_description);
                    logImportVerification('Additional description updated.', null);
                }


                // Product images + feature image
                if ($product_image_cron) {
                    // Get the image limit from plugin settings
                    $image_limit = get_option('ams_variation_image_limit', 5);
                    
                    // Set product feature image.
                    $gallery = $this->fetchImagesFromHtml($html);
                    
                    // Apply the limit to the gallery
                    $gallery = array_slice($gallery, 0, $image_limit);
                    
                    $image = array_shift($gallery);
                    $use_remote_images = ('Yes' === get_option('ams_remote_amazon_images'));
                    
                    // Always remove existing images and URLs
                    if ($image) {
                        // Remove featured image and url.
                        delete_product_images($post_id);
                        reset_product_thumbnail_url($post_id, $flag = 0);
                    }
                    
                    if (count($gallery) > 0) {
                        // Remove product gallery images and url.
                        delete_product_gallery_images($post_id);
                        reset_product_thumbnail_url($post_id, $flag = 1);
                    }
                    
                    if ($use_remote_images) {
                        // Set featured image url
                        if ($image) {
                            attach_product_thumbnail_url($post_id, $image, 0);
                        }
                        // Set featured image gallery
                        if (count($gallery) > 0) {
                            attach_product_thumbnail_url($post_id, $gallery, 1);
                        }
                        // Remove any locally stored images
                        delete_local_product_images($post_id);
                    } else {
                        // Set featured image
                        if ($image) {
                            attach_product_thumbnail($post_id, $image, 0);
                        }
                        // Set featured image gallery
                        foreach ($gallery as $gallery_image) {
                            // Set gallery image.
                            attach_product_thumbnail($post_id, $gallery_image, 1);
                        }
                        // Remove any stored image URLs
                        delete_product_image_urls($post_id);
                    }
                }

                update_post_meta($post_id, '_product_currency', $currency);
                update_post_meta($post_id, 'ams_last_cron_update', date('Y-m-d H:i:s'));
                update_post_meta($post_id, 'ams_last_cron_status', 0);

                logImportVerification('Simple product updated!', null);
            }

            update_post_meta($_POST['post_id'], 'ams_last_cron_update', date('Y-m-d H:i:s'));
            update_post_meta($_POST['post_id'], 'ams_last_cron_status', 1);

            logImportVerification('Product Updated!',null);
            echo esc_html__('Success', 'ams-wc-amazon');
            wc_delete_product_transients($post_id);
            clean_completed_woocommerce_actions();
            clean_all_actionscheduler_logs();
        } else {
            error_log("Failed to fetch product data");
            echo 'Failed to fetch product data';
        }
    } catch (\Throwable $th) {
        error_log("Exception occurred: " . $th->getMessage());
        echo 'Try again!';
        update_post_meta($_POST['post_id'], 'ams_last_cron_update', date('Y-m-d H:i:s'));
        update_post_meta($_POST['post_id'], 'ams_last_cron_status', 0);
    }
    wp_die();        
}
    
public function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

public function parseNumberFromString($numberString) {
    $numberString = preg_replace('/\s*/m', '', $numberString);
    $numberString = preg_replace('#[^0-9\.,]#', '', $numberString);
    $numberString = preg_replace('/[^0-9]+/', '', $numberString);
    $number = (int) $numberString;
    return $number;
}

// remove currency symbols extra spaces and comma dot from price except decimal separater
public function parsePrice($priceString) {
    $pattern = '/\s*/m';
    $replace = '';
    $priceString = preg_replace($pattern, $replace, $priceString);
    $priceString = preg_replace('#[^0-9\.,]#', '', $priceString);
    $pattern = '/[.,]\d{1,2}$/';
    $hasDecimal = preg_match_all($pattern, $priceString);
    $priceString = preg_replace('/[^0-9]+/', '', $priceString);
    if($hasDecimal) {
        $priceString = number_format(substr_replace($priceString, '.', -2, 0),2);
        $price = $priceString;
    }else {
        $price = (float)$priceString;
    }
    return $price;
}

public function parsePricenew($priceString) {
    $pattern = '/\s*/m';
    $replace = '';
    $priceString = preg_replace($pattern, $replace, $priceString);
    $priceString = preg_replace('#[^0-9\.,]#', '', $priceString);
    $pattern = '/[.,]\d{1,2}$/';
    $hasDecimal = preg_match_all($pattern, $priceString);
    $priceString = preg_replace('/[^0-9]+/', '', $priceString);
    if($hasDecimal) {
        $priceString = number_format(substr_replace($priceString, '.', -2, 0),2);
        $price = $priceString;
    }else {
        $price = (float)$priceString;
    }
    //Product will be added to site cart when will checkout than add order list for DropShip
    $percentage_profit = (float) get_option('ams_percentage_profit');
    if (strtolower(get_option('ams_buy_action_btn')) === strtolower('dropship')) {
        if (!empty($price)) {
            $profit = ($price / 100) * $percentage_profit;
            $price = $price + $profit;
        }
    }
    return $price;
}

public function parsePriceStringnew($priceString) {

    $price = (float)$priceString;
    
    //Product will be added to site cart when will checkout than add order list for DropShip
    $percentage_profit = (float) get_option('ams_percentage_profit');
    if (strtolower(get_option('ams_buy_action_btn')) === strtolower('dropship')) {
        if (!empty($price)) {
            $profit = ($price / 100) * $percentage_profit;
            $price = $price + $profit;
        }
    }
    return $price;
}

public static function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}
    
public function syncAndGetCategory($html) {
    $cat = null;
    $ams_default_category = get_option('ams_default_category');

    $min_depth = intval(get_option('ams_category_min_depth', 1));
    $max_depth = intval(get_option('ams_category_max_depth', 6));

    if ($ams_default_category && $ams_default_category == '_auto_import_amazon') {

        $breadcrumbHtml = $html->find('#wayfinding-breadcrumbs_feature_div ul li span a');

        if (!empty($breadcrumbHtml)) {
            $category_paths = [];
            $last_id = 0;
            $category_ids = []; 

            // Build the category path from breadcrumbs
            foreach ($breadcrumbHtml as $catHtml) {
                $category_name = trim($catHtml->plaintext);

                if (!empty($category_name)) {
                    $category_paths[] = $category_name;
                }
            }

            // Ensure the cleaned path respects min-max depth
            if (count($category_paths) >= $min_depth) {
                // Trim the path to match the max depth if necessary
                $trimmed_path = array_slice($category_paths, 0, $max_depth);

                // Process each category in the trimmed path and create a hierarchy in WordPress
                foreach ($trimmed_path as $category_name) {
                    $category_name = trim($category_name);

                    // Check if the category exists under the parent
                    $existing_category = term_exists($category_name, 'product_cat', $last_id);

                    if (!$existing_category) {
                        // Create the category if it doesn't exist, with the correct parent
                        $new_category = wp_insert_term($category_name, 'product_cat', array(
                            'description' => $category_name,
                            'parent' => $last_id
                        ));

                        if (!is_wp_error($new_category)) {
                            $last_id = $new_category['term_id'];
                            $category_ids[] = $last_id;
                        }
                    } else {
                        // If the category already exists, fetch its ID and continue
                        $term = get_term_by('name', esc_attr($category_name), 'product_cat');
                        if ($term) {
                            $last_id = $term->term_id;
                            $category_ids[] = $last_id;
                        } else {
                            $last_id = $existing_category['term_id'];
                            $category_ids[] = $last_id;
                        }
                    }
                }

                // Assign all categories in the hierarchy to the product
                if (!empty($category_ids)) {
                    return $category_ids;
                }
            }
        }
    } else if ($ams_default_category) {

        return $ams_default_category;
    }

    return $cat;
}

public function fetchImagesFromHtml($html) {
    $gallery = [];
    $ams_product_thumbnail_size = get_option('ams_product_thumbnail_size');
    $az_index = [
        "hd" => 4,
        "extra_large" => 3,
        "Large" => 2,
        "Medium" => 1,
        "Small" => 0,
    ];

    // Primary method: #imageBlock_feature_div
    $imagesHtmlDataArray = $html->find('#imageBlock_feature_div');
    if (!empty($imagesHtmlDataArray)) {
        $imagesHtmlData = $this->get_string_between($imagesHtmlDataArray[0], 'colorImages', 'colorToAsin');
        preg_match_all('/main(.*?)variant/s', $imagesHtmlData, $imagesArray);
        preg_match_all('/hiRes(.*?)thumb/s', $imagesHtmlData, $hiresimagesArray);
        for ($i = 0; $i < sizeof($imagesArray[0]); $i++) {
            $imagesArray[0][$i] .= $hiresimagesArray[0][$i];
            preg_match_all('/https?:\/\/[^"\']+\.(jpg|jpeg|png|webp)/i', $imagesArray[0][$i], $matches);
            
            if ((!isset($matches[0][$az_index[$ams_product_thumbnail_size]]) || $az_index[$ams_product_thumbnail_size] == 4) 
                && isset($matches[0][sizeof($matches[0])-1])) {
                $gallery[] = $matches[0][sizeof($matches[0])-1];
            } elseif (isset($matches[0][$az_index[$ams_product_thumbnail_size]])) {
                $gallery[] = $matches[0][$az_index[$ams_product_thumbnail_size]];
            }
        }
    }

    // Fallback methods
    $methods = [
        '#mainImageContainer',
        '#main-image-container',
        '#imageBlockContainer',
        '#dp-container',
        '#imgTagWrapperId', // Common container for Amazon images
        '.imgTagWrapper',   // Class-based selection
        '#altImages',       // Alternative images container
        '.a-dynamic-image'  // For dynamically loaded images
    ];

    foreach ($methods as $method) {
        if (empty($gallery)) {
            $containerArray = $html->find($method);
            if (!empty($containerArray)) {
                // Try to extract image from a child <img> element first.
                $imgElement = $containerArray[0]->find('img', 0);
                if ($imgElement) {
                    if ($imgElement->getAttribute('data-old-hires')) {
                        $gallery[] = $imgElement->getAttribute('data-old-hires');
                    } elseif ($imgElement->src) {
                        $gallery[] = $imgElement->src;
                    }
                    if (!empty($gallery)) {
                        break;
                    }
                }
                
                // Existing fallback logic: use innertext with regex if no <img> was found.
                $container = $containerArray[0]->innertext;
                if ($method === '#dp-container') {
                    $container = $this->get_string_between($container, '{"landingImageUrl":"', '"}');
                }

                // Check for data-a-dynamic-image attribute if applicable.
                if (strpos($method, 'dynamic-image') !== false) {
                    if (isset($containerArray[0]) && property_exists($containerArray[0], 'data-a-dynamic-image')) {
                        $dataAttr = $containerArray[0]->{'data-a-dynamic-image'};
                        if ($dataAttr) {
                            $jsonData = json_decode($dataAttr, true);
                            if ($jsonData) {
                                $gallery = array_keys($jsonData);
                                break;
                            }
                        }
                    }
                }

                preg_match_all('/https?:\/\/[^"\']+\.(jpg|jpeg|png|webp)/i', $container, $matches);
                if (isset($matches[0][sizeof($matches[0])-1])) {
                    $gallery[] = $matches[0][sizeof($matches[0])-1];
                    break;
                }
            }
        }
    }

    // Last resort: try to find iUrl
    if (empty($gallery)) {
        $iUrl = $this->get_string_between($html, 'var iUrl = "', '";');
        preg_match_all('/https?:\/\/[^"\']+\.(jpg|jpeg|png|webp)/i', $iUrl, $matches);
        if (isset($matches[0][sizeof($matches[0])-1])) {
            $gallery[] = $matches[0][sizeof($matches[0])-1];
        }
    }

    // Additional fallback for JSON-LD structured data
    if (empty($gallery)) {
        $jsonLd = $html->find('script[type="application/ld+json"]');
        if (!empty($jsonLd)) {
            $jsonData = json_decode($jsonLd[0]->innertext, true);
            if (isset($jsonData['image'])) {
                $gallery = is_array($jsonData['image']) ? $jsonData['image'] : [$jsonData['image']];
            }
        }
    }

    return $gallery;
}

public function fetchPriceFromHtml($loop_html, $skus = []) {
    $debug_info = [];
    $sku_prices = [];

    // Function to find the ppd div with multiple attempts
    $findPpdDiv = function($html) use (&$debug_info) {
        $attempts = [
            'div#ppd',
            'div[id="ppd"]',
            'div[id^="ppd"]',
            'div[id*="ppd"]'
        ];

        foreach ($attempts as $attempt) {
            $ppd = $html->find($attempt, 0);
            if ($ppd) {
                $debug_info['ppd_selector'] = $attempt;
                return $ppd;
            }
        }

        return null;
    };

    // Find the relevant section starting from ppd div
    $ppd = $findPpdDiv($loop_html);
    
    if ($ppd) {
        $relevant_section = $ppd;
        $debug_info['search_area'] = 'ppd';
    } else {
        // Search in Northstar-Buybox
        $northstar = $loop_html->find('#Northstar-Buybox', 0);
        if ($northstar) {
            $relevant_section = $northstar;
            $debug_info['search_area'] = 'Northstar-Buybox';
        } else {
            $relevant_section = $loop_html;
            $debug_info['search_area'] = 'entire HTML';
            $debug_info['warning'] = "Could not find ppd div or Northstar-Buybox. Using entire HTML.";
        }
    }

    // Helper function to find and parse price
    $findPrice = function($selectors, $price_type) use ($relevant_section, &$debug_info) {
        foreach ($selectors as $selector) {
            $elements = $relevant_section->find($selector);
            foreach ($elements as $element) {
                if (strpos($element->class, 'pricePerUnit') !== false) {
                    continue;
                }
                
                // Check for the specific price structure
                $price_symbol = $element->find('.a-price-symbol', 0);
                $price_whole = $element->find('.a-price-whole', 0);
                $price_fraction = $element->find('.a-price-fraction', 0);
                
                if ($price_symbol && $price_whole && $price_fraction) {
                    $full_price = $price_symbol->plaintext . $price_whole->plaintext . $price_fraction->plaintext;
                } else {
                    $full_price = $element->plaintext;
                }
                
                $price = $this->parsePrice($full_price);
                $debug_info[$price_type][] = [
                    'selector' => $selector,
                    'found_price' => $full_price,
                    'parsed_price' => $price
                ];
                if ($price > 0) {
                    return $price;
                }
            }
        }
        return 0;
    };

    // Find main price and crossed-out price
    $main_price = $findPrice([
        'span.a-price[data-a-size="xl"] span[aria-hidden="true"]',
        'span.a-price[data-a-size="l"] span[aria-hidden="true"]',
        '.priceToPay span[aria-hidden="true"]',
        '.apexPriceToPay span[aria-hidden="true"]',
        '.priceToPay .a-offscreen',
        '.apexPriceToPay .a-offscreen',
        '#priceblock_ourprice',
        '#priceblock_dealprice',
        '#priceblock_saleprice',
        'span.a-price[data-a-size="xl"] .a-offscreen',
        'span.a-price[data-a-size="l"] .a-offscreen',
        'span.a-price:not([data-a-strike="true"]) .a-offscreen',
        '#corePriceDisplay_desktop_feature_div .priceToPay .a-offscreen',
        '.a-section.a-spacing-none.aok-align-center.aok-relative .a-price .a-offscreen',
        'span.a-size-base.a-color-price.a-color-price',
        '#kindle-price',
        '.a-section.a-spacing-none.aok-align-center.aok-relative .a-price.a-text-price span[aria-hidden="true"]',
        '.dimension-slot-info .a-price span[aria-hidden="true"]',
        '.twister_swatch_price .olpWrapper',
        '.apex_on_twister_price .a-price span[aria-hidden="true"]'
    ], 'main_price');

    $crossed_out_price = $findPrice([
        '.a-text-price .a-offscreen',
        '#listPrice',
        '.priceBlockStrikePriceString .a-text-strike',
        '#corePriceDisplay_desktop_feature_div .a-text-price .a-offscreen',
        'span[data-a-strike="true"] .a-offscreen',
        '.a-section.a-spacing-small.aok-align-center .a-price.a-text-price[data-a-strike="true"] .a-offscreen'
    ], 'crossed_out_price');

    // For variable products, find prices for specific SKUs
    if (!empty($skus)) {
        foreach ($skus as $sku) {
            $sku_element = $relevant_section->find("[data-asin='$sku']", 0);
            if ($sku_element) {
                $priceElement = $sku_element->find('.twisterSwatchPrice', 0);
                if ($priceElement && isset($priceElement->plaintext)) {
                    $sku_prices[$sku] = $this->parsePrice($priceElement->plaintext);
                }
            }
        }
    }

    if (!empty($sku_prices)) {
        // For variable products
        $regular_price = max($sku_prices);
        $sale_price = min($sku_prices);
    } else {
        // For simple products or fallback
        if ($crossed_out_price > 0 && $crossed_out_price > $main_price) {
            $regular_price = $crossed_out_price;
            $sale_price = $main_price;
        } else {
            $regular_price = $main_price;
            $sale_price = $main_price;
        }
    }

    $debug_info['sku_prices'] = $sku_prices;
    $debug_info['final_prices'] = [
        'regular_price' => $regular_price,
        'sale_price' => $sale_price
    ];

    //print_r($debug_info);

    return [
        'regular_price' => $regular_price,
        'sale_price' => $sale_price,
        'sku_prices' => $sku_prices,
        'debug_info' => $debug_info
    ];
}

private function cleanPriceText($element) {
    if (is_object($element) && property_exists($element, 'plaintext')) {
        return trim(strip_tags($element->plaintext));
    } elseif (is_string($element)) {
        return trim(strip_tags($element));
    } else {
        // Handle unexpected input type
        return '';
    }
}

function parsePriceToFloat($string_price) {
    // Remove any whitespace
    $string_price = trim($string_price);

    // Remove currency symbols and any other non-numeric characters except . and ,
    $cleaned_price = preg_replace('/[^0-9.,]/', '', $string_price);

    // Check for different price formats
    if (preg_match('/^(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)$/', $cleaned_price)) {
        // US format: 1,234.56
        $cleaned_price = str_replace(',', '', $cleaned_price);
    } elseif (preg_match('/^(\d{1,3}(?:.\d{3})*(?:,\d{2})?)$/', $cleaned_price)) {
        // European format: 1.234,56
        $cleaned_price = str_replace('.', '', $cleaned_price);
        $cleaned_price = str_replace(',', '.', $cleaned_price);
    } elseif (preg_match('/^(\d+(?:,\d{2})?)$/', $cleaned_price)) {
        // Format like: 1234,56
        $cleaned_price = str_replace(',', '.', $cleaned_price);
    }

    // Convert to float
    $float_price = (float) $cleaned_price;

    // Round to the number of decimal places specified in WooCommerce
    $precision = function_exists('wc_get_price_decimals') ? wc_get_price_decimals() : 2;
    $float_price = round($float_price, $precision);

    return $float_price;
}

// Group functions to extract and format content
public function fetchContentFromHtml($html) {
    $content = $this->extractContent($html);
    return $this->formatContentForWordPress($content);
}

private function matchesPlaceholder($src, $patterns) {
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $src)) {
            return true;
        }
    }
    return false;
}

private function removeUnwantedElements($element) {
    // Adjust remove patterns to ensure important content (headings) are not stripped
    $removePatterns = [
        '*' => ['Collapse All', 'Expand All']  // Only removing truly unwanted patterns
    ];

    foreach ($removePatterns as $tag => $patterns) {
        foreach ($element->find($tag) as $subElement) {
            foreach ($patterns as $pattern) {
                if (stripos($subElement->innertext, $pattern) !== false) {
                    $subElement->outertext = ''; // Remove unwanted elements
                }
            }
        }
    }

    // Avoid removing entire important divs like in the old function
    $ignoreDivs = [
        '#productSpecifications_dp_warranty_and_support',
        '#productDetails_feedback_sections',
        '#productDetails_expanderTables_depthRightSections',
    ];

    foreach ($ignoreDivs as $ignoreDiv) {
        foreach ($element->find($ignoreDiv) as $ignored) {
            $ignored->outertext = '';
        }
    }
}

private function handleLazyLoadedImages($element) {
    $lazyAttributes = ['data-a-hires', 'data-src', 'data-lazy-src', 'data-srcset', 'data-original'];
    $placeholderPatterns = [
        '/^data:image\/.*base64/i',
        '/^data:image\/svg\+xml/i',
        '/placeholder\.png$/i',
        '/^about:blank$/i',
        '/grey-pixel\.gif$/i'
    ];

    // First, remove lazy-loaded placeholder images
    foreach ($element->find('img.a-lazy-loaded') as $img) {
        $img->outertext = '';
    }

    // Then handle any remaining images
    foreach ($element->find('img') as $img) {
        $currentSrc = $img->getAttribute('src');
        $isPlaceholder = empty($currentSrc) || $this->matchesPlaceholder($currentSrc, $placeholderPatterns);

        if (!$isPlaceholder) {
            continue;
        }

        foreach ($lazyAttributes as $attr) {
            $lazySource = $img->getAttribute($attr);
            if (!empty($lazySource) && !$this->matchesPlaceholder($lazySource, $placeholderPatterns)) {
                $img->setAttribute('src', $lazySource);
                break;
            }
        }
    }

    // Remove empty div.a-text-center after images
    foreach ($element->find('div.background-image') as $imgDiv) {
        $nextDiv = $imgDiv->next_sibling();
        if ($nextDiv && $nextDiv->class == 'a-section a-text-center' && trim($nextDiv->innertext) === '') {
            $nextDiv->outertext = '';
        }
    }

    // Remove all empty p tags
    foreach ($element->find('p') as $p) {
        if (trim($p->innertext) === '') {
            $p->outertext = '';
        }
    }
}

private function formatContentForWordPress($content) {
    // Remove problematic hover spans before processing
    $content = $this->removeHoverSpans($content);

    // Remove only unsafe or heavy elements
    $content = preg_replace([
        '/<(script|style)\b[^>]*>(.*?)<\/\1>/is', // JS + CSS
        '/<!--(.|\s)*?-->/is',                   // HTML comments
        '/<(iframe|embed|object|area)[^>]*>.*?<\/\1>/is', // media containers
        '/<(input|button|select|textarea|form|option)[^>]*>.*?<\/\1>/is',
        '/<(input|button|select|textarea|form|option)[^>]*>/is'
    ], '', $content);

    // Remove all <hr> elements, regardless of class
    $content = preg_replace('/<hr[^>]*>/', '', $content);

    // Remove empty <div> elements that may cause spacing issues
    $content = preg_replace('/<div class="a-section a-text-center">\s*<\/div>/', '', $content);

    // Remove any remaining empty <div> or <span> tags
    $content = preg_replace('/<div[^>]*>\s*<\/div>/', '', $content);
    $content = preg_replace('/<span[^>]*>\s*<\/span>/', '', $content);

    // Sanitize with wp_kses_post to retain standard post formatting without stripping images
    $content = wp_kses_post($content);

    // Apply wpautop to ensure consistent paragraph structure
    $content = wpautop($content);

    // Remove any empty <p> tags and normalize spaces
    $content = preg_replace('/<p>\s*<\/p>/', '', $content);
    $content = preg_replace('/\s+/', ' ', $content);
    $content = preg_replace('/\n\s*\n/', "\n", $content);

    return '<div class="woocommerce-product-details">' . $content . '</div>';
}

private function removeHoverSpans($content) {
    // Only remove the specific hover spans, nothing else
    $content = preg_replace(
        '/<span tabindex=0 class="hover-point" data-inline-content="[^"]*" data-position="triggerHorizontal" \/>/is',
        '',
        $content
    );
    
    return $content;
}

private function extractContent($html) {
    $content = '';
    $uniqueTextContent = []; // Track unique text content for better comparison

    // Define a new selector for the "About this Item" section
    $aboutSelectors = [
        'h3.product-facts-title' // Add more selectors if needed
    ];
    // Loop through selectors to scrape content
    foreach ($aboutSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Check if the title matches dynamically
            $dynamicTitle = trim($element->plaintext);
            if (stripos($dynamicTitle, 'About this item') !== false) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                
                // Locate the unordered list dynamically
                $aboutList = $element->parent()->find('.a-unordered-list', 0); // Adjust parent as needed
                if ($aboutList) {
                    $content .= '<ul>';
                    foreach ($aboutList->find('li') as $item) {
                        $listItem = $item->find('span.a-list-item', 0);
                        if ($listItem) {
                            $itemText = trim($listItem->plaintext);
                            if (!empty($itemText)) {
                                $content .= '<li>' . htmlspecialchars($itemText) . '</li>';
                            }
                        }
                    }
                    $content .= '</ul>';
                }
            }
        }
    }



    // Define a new selector for the "From the Brand" section
    $brandSelectors = [
        '#aplusBrandStory_feature_div' // Add more selectors if needed
    ];
    // Loop through selectors to scrape content
    foreach ($brandSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Initialize a flag to check if there's actual content
            $hasBrandContent = false;
            $sectionContent = '';

            // Extract the dynamic title
            $titleElement = $element->find('h2', 0);
            $dynamicTitle = $titleElement ? trim($titleElement->plaintext) : 'Brand Information';

            // Extract the first main image (avoid duplicates)
            $imageHashes = []; // To store hashes of processed images
            $mainImage = $element->find('.apm-brand-story-background-image img', 0);
            if ($mainImage) {
                $imageSrc = $mainImage->getAttribute('data-src') ?: $mainImage->getAttribute('src');
                if (!empty($imageSrc) && !in_array(md5($imageSrc), $imageHashes)) {
                    $imageAlt = $mainImage->getAttribute('alt') ?: 'Brand Image';
                    $sectionContent .= '<div><img src="' . htmlspecialchars($imageSrc) . '" alt="' . htmlspecialchars($imageAlt) . '" style="max-width: 100%;"></div>';
                    $imageHashes[] = md5($imageSrc);
                    $hasBrandContent = true; // Mark that content exists
                }
            }

            // Extract main brand text
            $brandText = $element->find('.apm-brand-story-text-bottom', 0);
            if ($brandText) {
                $sectionContent .= '<p>' . htmlspecialchars(trim($brandText->plaintext)) . '</p>';
                $hasBrandContent = true; // Mark that content exists
            }

            // Only append the title and content if there's actual brand content
            if ($hasBrandContent) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                $content .= $sectionContent;
            }
        }
    }




    // Define a new selector for the "From the Manufacturer" section
    $manufacturerSelectors = [
        '#aplus_feature_div' // Add more selectors if needed
    ];
    // Loop through the selectors to scrape content
    foreach ($manufacturerSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Pre-clean hover spans before processing
            $element->outertext = $this->removeHoverSpans($element->outertext);

            // Convert h2 to h3 to preserve titles
            $element->outertext = preg_replace('/<h2(.*?)>(.*?)<\/h2>/', '<h3 class="$1">$2</h3>', $element->outertext);

            // Get the HTML and text content
            $elementHtml = trim($element->outertext);
            $elementText = trim($element->plaintext);

            // Create a unique key based on the text content to detect duplicates
            $contentKey = md5($elementText);

            // Check for unique content
            if (!empty($elementText) && !in_array($contentKey, $uniqueTextContent)) {
                $uniqueTextContent[] = $contentKey;
                $content .= $elementHtml;
            }
        }
    }




    // Define a new selector for the "Product Description" section
    $productDescriptionSelectors = [
        '#productDescription_feature_div' // Add more selectors if needed
    ];
    // Initialize an array to track processed content
    $processedSections = [];
    // Loop through selectors to scrape content
    foreach ($productDescriptionSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Extract the dynamic title
            $titleElement = $element->find('h2', 0); // Find the title dynamically
            $dynamicTitle = $titleElement ? trim($titleElement->plaintext) : 'Product Description';

            // Extract the product description text
            $descriptionElement = $element->find('#productDescription', 0); // Locate the main description
            $descriptionText = $descriptionElement ? trim($descriptionElement->plaintext) : '';

            // Generate a unique key to track duplicates
            $sectionKey = md5($dynamicTitle . $descriptionText);

            // Check if the section is already processed
            if (!empty($descriptionText) && !isset($processedSections[$sectionKey])) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                $content .= '<p>' . htmlspecialchars($descriptionText) . '</p>';

                // Extract the first main image (if any)
                $mainImage = $element->find('img', 0); // Find the first image
                if ($mainImage) {
                    $imageSrc = $mainImage->getAttribute('data-src') ?: $mainImage->getAttribute('src');
                    if (!empty($imageSrc)) {
                        $imageAlt = $mainImage->getAttribute('alt') ?: 'Product Image';
                        $content .= '<div><img src="' . htmlspecialchars($imageSrc) . '" alt="' . htmlspecialchars($imageAlt) . '" style="max-width: 100%;"></div>';
                    }
                }

                // Mark the section as processed
                $processedSections[$sectionKey] = true;
            }
        }
    }




    // Define a new selector for the "Technical Specifications" section
    $technicalSpecificationsSelectors = [
        '#technicalSpecifications_feature_div' // Add more selectors if needed
    ];
    // Initialize an array to track processed technical specifications
    $processedTechnicalSpecifications = [];
    // Loop through selectors to scrape content
    foreach ($technicalSpecificationsSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Extract the dynamic title (if any)
            $titleElement = $element->find('h2', 0); // Find the title dynamically
            $dynamicTitle = $titleElement ? trim($titleElement->plaintext) : 'Technical Specifications';

            // Extract the technical specifications table
            $tableElement = $element->find('table', 0); // Locate the table
            $tableHtml = '';
            if ($tableElement) {
                $tableHtml .= '<table>';
                foreach ($tableElement->find('tr') as $row) {
                    $tableHtml .= '<tr>';
                    foreach ($row->find('th, td') as $cell) {
                        $tableHtml .= '<' . $cell->tag . '>' . htmlspecialchars(trim($cell->plaintext)) . '</' . $cell->tag . '>';
                    }
                    $tableHtml .= '</tr>';
                }
                $tableHtml .= '</table>';
            }

            // Generate a unique key to track duplicates
            $sectionKey = md5($dynamicTitle . $tableHtml);

            // Check if the section is already processed
            if (!empty($tableHtml) && !isset($processedTechnicalSpecifications[$sectionKey])) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                $content .= $tableHtml;

                // Mark the section as processed
                $processedTechnicalSpecifications[$sectionKey] = true;
            }
        }
    }



    // Define a new selector for the "Detail Bullets" section
    $detailBulletsSelectors = [
        '#detailBulletsWrapper_feature_div' // Add more selectors if needed
    ];
    // Initialize an array to track processed sections
    $processedDetailBullets = [];
    // Loop through selectors to scrape content
    foreach ($detailBulletsSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Extract the dynamic title (if any)
            $titleElement = $element->find('h2', 0); // Find the title dynamically
            $dynamicTitle = $titleElement ? trim($titleElement->plaintext) : 'Product Details';

            // Extract the detail bullets list
            $bulletsElement = $element->find('.a-unordered-list', 0); // Locate the unordered list
            $bulletsHtml = '';
            if ($bulletsElement) {
                $bulletsHtml .= '<ul>';
                foreach ($bulletsElement->find('li') as $item) {
                    $itemText = trim($item->plaintext);

                    // Remove or replace special direction marks
                    $itemText = str_replace(['&lrm;', '&rlm;'], '', $itemText);

                    // Clean any extra whitespace
                    $itemText = preg_replace('/\s+/', ' ', $itemText);

                    $bulletsHtml .= '<li>' . htmlspecialchars($itemText) . '</li>';
                }
                $bulletsHtml .= '</ul>';
            }

            // Generate a unique key to track duplicates
            $sectionKey = md5($dynamicTitle . $bulletsHtml);

            // Check if the section is already processed
            if (!empty($bulletsHtml) && !isset($processedDetailBullets[$sectionKey])) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                $content .= $bulletsHtml;

                // Mark the section as processed
                $processedDetailBullets[$sectionKey] = true;
            }
        }
    }



    // Define a new selector for the "Legal Disclaimer" section
    $legalSelectors = [
        '#legalEUBtf_feature_div' // Add more selectors if needed
    ];
    // Initialize an array to track processed sections
    $processedLegalSections = [];
    // Loop through selectors to scrape content
    foreach ($legalSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Extract the dynamic title (if any)
            $titleElement = $element->find('h2', 0); // Find the title dynamically
            $dynamicTitle = $titleElement ? trim($titleElement->plaintext) : 'Legal Disclaimer';

            // Extract the legal content
            $legalContentElement = $element->find('.a-section', 0); // Locate the content dynamically
            $legalContentHtml = '';
            if ($legalContentElement) {
                // Clean the text content
                $legalContentText = trim($legalContentElement->plaintext);

                // Remove or replace special direction marks
                $legalContentText = str_replace(['&lrm;', '&rlm;'], '', $legalContentText);

                // Clean any extra whitespace
                $legalContentText = preg_replace('/\s+/', ' ', $legalContentText);

                $legalContentHtml = '<p>' . htmlspecialchars($legalContentText) . '</p>';
            }

            // Generate a unique key to track duplicates
            $sectionKey = md5($dynamicTitle . $legalContentHtml);

            // Check if the section is already processed
            if (!empty($legalContentHtml) && !isset($processedLegalSections[$sectionKey])) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                $content .= $legalContentHtml;

                // Mark the section as processed
                $processedLegalSections[$sectionKey] = true;
            }
        }
    }



    // Define a new selector for the "Buffet Service Card" section
    $buffetServiceSelectors = [
        '#buffetServiceCard_feature_div' // Add more selectors if needed
    ];
    // Initialize an array to track processed sections
    $processedBuffetServiceSections = [];
    // Loop through selectors to scrape content
    foreach ($buffetServiceSelectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Extract the dynamic title (if any)
            $titleElement = $element->find('h2', 0); // Find the title dynamically
            $dynamicTitle = $titleElement ? trim($titleElement->plaintext) : 'Buffet Service Card';

            // Extract the buffet content
            $buffetContentElement = $element->find('.a-section', 0); // Locate the content dynamically
            $buffetContentHtml = '';
            if ($buffetContentElement) {
                $buffetContentText = trim($buffetContentElement->plaintext);

                // Clean up special characters and extra whitespace
                $buffetContentText = str_replace(['&lrm;', '&rlm;'], '', $buffetContentText);
                $buffetContentText = preg_replace('/\s+/', ' ', $buffetContentText);

                $buffetContentHtml = '<p>' . htmlspecialchars($buffetContentText) . '</p>';
            }

            // Generate a unique key to track duplicates
            $sectionKey = md5($dynamicTitle . $buffetContentHtml);

            // Check if the section is already processed
            if (!empty($buffetContentHtml) && !isset($processedBuffetServiceSections[$sectionKey])) {
                $content .= '<h3>' . htmlspecialchars($dynamicTitle) . '</h3>';
                $content .= $buffetContentHtml;

                // Mark the section as processed
                $processedBuffetServiceSections[$sectionKey] = true;
            }
        }
    }


    // Define all selectors for remaining sections
    $selectors = [
        '#productDetails_techSpec_section_1',
        '#tech',
        '#bondAboutThisItem_feature_div'
    ];

    // Loop through selectors to scrape additional content
    foreach ($selectors as $selector) {
        $elements = $html->find($selector);
        foreach ($elements as $element) {
            // Handle unwanted elements and lazy-loaded images
            $this->removeUnwantedElements($element);
            $this->handleLazyLoadedImages($element);

            // Pre-clean hover spans before processing
            $element->outertext = $this->removeHoverSpans($element->outertext);

            // Convert h2 to h3 to preserve titles
            $element->outertext = preg_replace('/<h2(.*?)>(.*?)<\/h2>/', '<h3 class="$1">$2</h3>', $element->outertext);

            // Get the HTML and text content
            $elementHtml = trim($element->outertext);
            $elementText = trim($element->plaintext);

            // Create a unique key based on the text content to detect duplicates
            $contentKey = md5($elementText);

            // Check for unique content
            if (!empty($elementText) && !in_array($contentKey, $uniqueTextContent)) {
                $uniqueTextContent[] = $contentKey;
                $content .= $elementHtml;
            }
        }
    }

    return $content;
}
// Group functions to extract and format content//


    public function fetchAdditionalContentFromHtml($html) {
        // Start content with WooCommerce standard table class
        $content = '<table class="woocommerce-product-attributes shop_attributes">';

        // First, try to get data from the product overview
        $rows = $html->find('#productOverview_feature_div table tr');
        
        // If no product overview, try the original selectors (fallback)
        if (empty($rows)) {
            $rows = $html->find('#productDetails_detailBullets_sections1 tr, #productDetails_db_sections .prodDetTable tr');
        }

        // Check if the #bond-technical-specfications-desktop section exists and expand it to get the table
        if (empty($rows)) {
            $section = $html->find('#bond-technical-specfications-desktop', 0);
            if ($section) {
                $table = $section->find('.a-expander-content table', 0);
                if ($table) {
                    $rows = $table->find('tr');
                }
            }
        }

        // Check if the #nic-po-expander-section-desktop section exists and expand it to get the table
        if (empty($rows)) {
            $nicSection = $html->find('#nic-po-expander-section-desktop', 0);
            if ($nicSection) {
                $nicTable = $nicSection->find('.a-expander-content table', 0);
                if ($nicTable) {
                    $rows = $nicTable->find('tr');
                }
            }
        }

        // New Section: Check for "Product details" dynamically
        $productDetailsSection = $html->find('#productFactsDesktop_feature_div', 0); // Locate "Product details" section
        if ($productDetailsSection) {
            $content .= '<tr><th colspan="2" class="woocommerce-product-attributes-item__label">Product details</th></tr>'; // Add title dynamically

            // Find all rows of "Product details" dynamically
            $details = $productDetailsSection->find('.a-fixed-left-grid');
            foreach ($details as $detail) {
                $label = $detail->find('.a-col-left span', 0);
                $value = $detail->find('.a-col-right span', 0);

                if ($label && $value) {
                    $labelText = trim($label->plaintext);
                    $valueText = trim($value->plaintext);

                    $content .= '<tr class="woocommerce-product-attributes-item">';
                    $content .= '<th class="woocommerce-product-attributes-item__label">' . esc_html($labelText) . '</th>';
                    $content .= '<td class="woocommerce-product-attributes-item__value">' . esc_html($valueText) . '</td>';
                    $content .= '</tr>';
                }
            }
        }

        // Loop through the rows and scrape data
        foreach ($rows as $row) {
            $label = $row->find('th', 0) ?: $row->find('td.a-span3', 0);
            $value = $row->find('td', 1) ?: $row->find('td.a-span9', 0);
            
            if ($label && $value) {
                $labelText = trim($label->plaintext);
                $valueText = trim($value->plaintext);
                
                // Add scraped data to the content
                $content .= '<tr class="woocommerce-product-attributes-item">';
                $content .= '<th class="woocommerce-product-attributes-item__label">' . esc_html($labelText) . '</th>';
                $content .= '<td class="woocommerce-product-attributes-item__value">' . esc_html($valueText) . '</td>';
                $content .= '</tr>';
            }
        }

        $content .= '</table>';

        return $content;
    }

    public function fetchShortDescriptionFromHtml($html) {
        $content = '';
        $data = $html->find('#feature-bullets ul li .a-list-item');
        if(isset($data[0])){
            $content .= '<ul>';
            foreach ( $data as $element) {
                $content .= '<li>' .  $element->innertext .'</li>';
            }
            $content .= '</ul>';
        }

        $data = $html->find('#bookDescription_feature_div noscript'); 
        if(isset($data[0])){
            $headlines = array();
            foreach($data as $header) {
                $headlines[] = $header->innertext;
                $content .= $header->innertext;
            }
        }

        $data = $html->find('#bookDescription_feature_div .a-expander-collapsed-height .a-expander-content span');
        if(isset($data[0])){
            $headlines = array();
            foreach($data as $header) {
                $headlines[] = $header->innertext;
                $content .= $header->innertext;
            }
        }

        return $content;
    }

    public function fetchVariationContentFromHtml($html) {
        $content = '';
        $data = $html->find('#feature-bullets ul li .a-list-item');
        if($data) {
            $content .= '<ul>';
            foreach ( $data as $element ) {
                $classes = explode(' ', $element->getAttribute('class'));
                if( in_array('aok-hidden', $classes) ) continue;
                $content .= '<li>' .  $element->text() .'</li>';
            }
            $content .= '</ul>';
        }

        $data = $html->find('#productFactsDesktop_feature_div ul li');
        if($data) {
            $content .= '<ul>';
            foreach ( $data as $element ) {
                $content .= '<li>' .  $element->text() .'</li>';
            }
            $content .= '</ul>';
        }

        $data = $html->find('#productFactsDesktop_feature_div .pfDescContent p', 0);
        if($data) {
            $content .= '<p>' .  $data->text() .'</p>';
        }

        return $content;
    }
   
    public function fetchVariationsDetails() {
    }
   
    public function getAmazonPageHtml($url) {
        return wp_remote_get(esc_url_raw($url));
    }
  
    public function fetchCurrencyFromHtml($html) {
        $currency = null;
    
        // Attempt to find the currency symbol using the new selector
        $currencySymbol = $html->find('.a-price-symbol', 0);
        if ($currencySymbol) {
            $currency = $currencySymbol->text() ?: null;
        }
    
        // If the currency symbol is still not found, you can keep the existing logic
        if (null === $currency) {
            $currencySymbol = $html->find('#apex_desktop span.priceToPay span[aria-hidden] .a-price-symbol', 0);
            if ($currencySymbol) {
                $currency = $currencySymbol->text() ?: null;
            }
        }
    
        if (null === $currency) {
            $currencySymbol = $html->find('#dp-container .a-size-medium.a-color-price', 0);
            if ($currencySymbol) {
                $matches = preg_split('/\d|[1-9]/', trim($currencySymbol->text()));
                $currency = (trim($matches[0]) == 'EUR') ? '€' : ($matches[0] ?: null);
            }
        }
    
        if (null === $currency) {
            $currencySymbol = $html->find('#apex_desktop .apexPriceToPay .a-offscreen', 0);
            if ($currencySymbol) {
                $matches = preg_split('/\d|[1-9]/', trim($currencySymbol->text()));
                $currency = $matches[0] ?: null;
            }
        }
    
        if (null === $currency) {
            $currencySymbol = $html->find('#apex_desktop #sns-base-price', 0);
            if ($currencySymbol) {
                if ($currencySymbol->has_child()) {
                    $currencySymbol->removeChild($currencySymbol->find('span', 0));
                }
                $matches = preg_split('/\d|[1-9]/', trim($currencySymbol->text()));
                $currency = end($matches) ?: null;
            }
        }
    
        return $currency;
    }

    public function user_agent() {
        $user_agent = array(
            "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
            "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)",
            "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
            "Mozilla/4.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
            "Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)",
            "Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))",
            "Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 4.0; InfoPath.3; MS-RTC LM 8; .NET4.0C; .NET4.0E)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 4.0; Tablet PC 2.0; InfoPath.3; .NET4.0C; .NET4.0E)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; chromeframe/11.0.696.57)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0) chromeframe/10.0.648.205",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0; chromeframe/11.0.696.57)",
            "Mozilla/5.0 ( ; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)",
            "Mozilla/4.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.0)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 7.1; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; AskTB5.5)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; InfoPath.2; .NET4.0C; .NET4.0E)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; FDM; .NET CLR 1.1.4322; .NET4.0C; .NET4.0E; Tablet PC 2.0)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/4.0; Media Center PC 4.0; SLCC1; .NET CLR 3.0.04320)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 1.1.4322)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/4.0; InfoPath.1; SV1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 3.0.04506.30)",
            "Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.0; FBSMTWB; .NET CLR 2.0.34861; .NET CLR 3.0.3746.3218; .NET CLR 3.5.33652; msn OptimizedIE8;ENUS)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; Media Center PC 6.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.3; .NET4.0C; .NET4.0E; .NET CLR 3.5.30729; .NET CLR 3.0.30729; MS-RTC LM 8)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.2)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 3.0)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; msn OptimizedIE8;ZHCN)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MS-RTC LM 8; InfoPath.3; .NET4.0C; .NET4.0E) chromeframe/8.0.552.224",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MS-RTC LM 8; .NET4.0C; .NET4.0E; Zune 4.7; InfoPath.3)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MS-RTC LM 8; .NET4.0C; .NET4.0E; Zune 4.7)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MS-RTC LM 8)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; Zune 4.0)",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; MS-RTC LM 8; Zune 4.7)",
            "Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre",
            "Mozilla/5.0 (X11; Linux x86_64; rv:2.2a1pre) Gecko/20100101 Firefox/4.2a1pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.2a1pre) Gecko/20110323 Firefox/4.2a1pre",
            "Mozilla/5.0 (X11; Linux x86_64; rv:2.0b9pre) Gecko/20110111 Firefox/4.0b9pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b9pre) Gecko/20101228 Firefox/4.0b9pre",
            "Mozilla/5.0 (Windows NT 5.1; rv:2.0b9pre) Gecko/20110105 Firefox/4.0b9pre",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b8pre) Gecko/20101114 Firefox/4.0b8pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b8pre) Gecko/20101213 Firefox/4.0b8pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b8pre) Gecko/20101128 Firefox/4.0b8pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b8pre) Gecko/20101114 Firefox/4.0b8pre",
            "Mozilla/5.0 (Windows NT 5.1; rv:2.0b8pre) Gecko/20101127 Firefox/4.0b8pre",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0b8) Gecko/20100101 Firefox/4.0b8",
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0b7pre) Gecko/20100921 Firefox/4.0b7pre",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b7) Gecko/20101111 Firefox/4.0b7",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b7) Gecko/20100101 Firefox/4.0b7",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b6pre) Gecko/20100903 Firefox/4.0b6pre",
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0b6pre) Gecko/20100903 Firefox/4.0b6pre Firefox/4.0b6pre",
            "Mozilla/5.0 (X11; Linux x86_64; rv:2.0b4) Gecko/20100818 Firefox/4.0b4",
            "Mozilla/5.0 (X11; Linux i686; rv:2.0b3pre) Gecko/20100731 Firefox/4.0b3pre",
            "Mozilla/5.0 (Windows NT 5.2; rv:2.0b13pre) Gecko/20110304 Firefox/4.0b13pre",
            "Mozilla/5.0 (Windows NT 5.1; rv:2.0b13pre) Gecko/20110223 Firefox/4.0b13pre",
            "Mozilla/5.0 (X11; Linux i686; rv:2.0b12pre) Gecko/20110204 Firefox/4.0b12pre",
            "Mozilla/5.0 (X11; Linux i686; rv:2.0b12pre) Gecko/20100101 Firefox/4.0b12pre",
            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b11pre) Gecko/20110128 Firefox/4.0b11pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b11pre) Gecko/20110131 Firefox/4.0b11pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b11pre) Gecko/20110129 Firefox/4.0b11pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b11pre) Gecko/20110128 Firefox/4.0b11pre",
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0b11pre) Gecko/20110126 Firefox/4.0b11pre",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0b11pre) Gecko/20110126 Firefox/4.0b11pre",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b10pre) Gecko/20110118 Firefox/4.0b10pre",
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0b10pre) Gecko/20110113 Firefox/4.0b10pre",
            "Mozilla/5.0 (X11; Linux i686; rv:2.0b10) Gecko/20100101 Firefox/4.0b10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:2.0b10) Gecko/20110126 Firefox/4.0b10",
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0b10) Gecko/20110126 Firefox/4.0b10",
            "Mozilla/5.0 (X11; U; Linux x86_64; pl-PL; rv:2.0) Gecko/20110307 Firefox/4.0",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:2.0) Gecko/20110404 Fedora/16-dev Firefox/4.0",
            "Mozilla/5.0 (X11; Arch Linux i686; rv:2.0) Gecko/20110321 Firefox/4.0",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.3) Gecko/20100401 Firefox/4.0 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows NT 6.1; rv:2.0) Gecko/20110319 Firefox/4.0",
            "Mozilla/5.0 (Windows NT 6.1; rv:1.9) Gecko/20100101 Firefox/4.0",
            "Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/20121223 Ubuntu/9.25 (jaunty) Firefox/3.8",
            "Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.25 (jaunty) Firefox/3.8",
            "Mozilla/5.0 (X11; U; Linux i686; it-IT; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.25 (jaunty) Firefox/3.8",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401 Mozilla/5.0 (X11; U; Linux i686; it-IT; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.25 (jaunty) Firefox/3.8",
            "Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.3a5pre) Gecko/20100526 Firefox/3.7a5pre",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2) Gecko/20091218 Firefox 3.6b5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.2b4) Gecko/20091124 Firefox/3.6b4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2b4) Gecko/20091124 Firefox/3.6b4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2b1) Gecko/20091014 Firefox/3.6b1 GTB5",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2a1pre) Gecko/20090428 Firefox/3.6a1pre",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2a1pre) Gecko/20090405 Firefox/3.6a1pre",
            "Mozilla/5.0 (X11; U; Linux i686; ru-RU; rv:1.9.2a1pre) Gecko/20090405 Ubuntu/9.04 (jaunty) Firefox/3.6a1pre",
            "Mozilla/5.0 (Windows; Windows NT 5.1; es-ES; rv:1.9.2a1pre) Gecko/20090402 Firefox/3.6a1pre",
            "Mozilla/5.0 (Windows; Windows NT 5.1; en-US; rv:1.9.2a1pre) Gecko/20090402 Firefox/3.6a1pre",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ja; rv:1.9.2a1pre) Gecko/20090402 Firefox/3.6a1pre (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.9) Gecko/20100915 Gentoo Firefox/3.6.9",
            "Mozilla/5.0 (X11; U; FreeBSD i386; en-US; rv:1.9.2.9) Gecko/20100913 Firefox/3.6.9",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.9) Gecko/20100824 Firefox/3.6.9 ( .NET CLR 3.5.30729; .NET CLR 4.0.20506)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-GB; rv:1.9.2.9) Gecko/20100824 Firefox/3.6.9",
            "Mozilla/5.0 (X11; U; OpenBSD i386; en-US; rv:1.9.2.8) Gecko/20101230 Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.8) Gecko/20100804 Gentoo Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.8) Gecko/20100723 SUSE/3.6.8-0.1.1 Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux i686; zh-CN; rv:1.9.2.8) Gecko/20100722 Ubuntu/10.04 (lucid) Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.2.8) Gecko/20100723 Ubuntu/10.04 (lucid) Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux i686; fi-FI; rv:1.9.2.8) Gecko/20100723 Ubuntu/10.04 (lucid) Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.8) Gecko/20100727 Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; Linux i686; de-DE; rv:1.9.2.8) Gecko/20100725 Gentoo Firefox/3.6.8",
            "Mozilla/5.0 (X11; U; FreeBSD i386; de-CH; rv:1.9.2.8) Gecko/20100729 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; pt-BR; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; it; rv:1.9.2.8) Gecko/20100722 AskTbADAP/3.9.1.14019 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.8) Gecko/20100722 Firefox 3.6.8 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8 ( .NET CLR 3.5.30729; .NET4.0C)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.2.8) Gecko/20100722 Firefox 3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.2.3) Gecko/20121221 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; zh-TW; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; tr; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8 ( .NET CLR 3.5.30729; .NET4.0E",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.7) Gecko/20100809 Fedora/3.6.7-1.fc14 Firefox/3.6.7",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.7) Gecko/20100723 Fedora/3.6.7-1.fc13 Firefox/3.6.7",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.7) Gecko/20100726 CentOS/3.6-3.el5.centos Firefox/3.6.7",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; hu; rv:1.9.2.7) Gecko/20100713 Firefox/3.6.7 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.7 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-PT; rv:1.9.2.7) Gecko/20100713 Firefox/3.6.7 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.6) Gecko/20100628 Ubuntu/10.04 (lucid) Firefox/3.6.6 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.6) Gecko/20100628 Ubuntu/10.04 (lucid) Firefox/3.6.6 GTB7.0",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.6) Gecko/20100628 Ubuntu/10.04 (lucid) Firefox/3.6.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.6) Gecko/20100628 Ubuntu/10.04 (lucid) Firefox/3.6.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; pt-PT; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; it; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; zh-CN; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; nl; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; it; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729; .NET4.0E)",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.4) Gecko/20100614 Ubuntu/10.04 (lucid) Firefox/3.6.4",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.4) Gecko/20100625 Gentoo Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-TW; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.4) Gecko/20100513 Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ja; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; cs; rv:1.9.2.4) Gecko/20100513 Firefox/3.6.4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; zh-CN; rv:1.9.2.4) Gecko/20100513 Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja; rv:1.9.2.4) Gecko/20100513 Firefox/3.6.4 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; fr; rv:1.9.2.4) Gecko/20100523 Firefox/3.6.4 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.4) Gecko/20100527 Firefox/3.6.4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.4) Gecko/20100527 Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.4) Gecko/20100523 Firefox/3.6.4 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.4) Gecko/20100513 Firefox/3.6.4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-CA; rv:1.9.2.4) Gecko/20100523 Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 GTB7.0 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4) Gecko/20100513 Firefox/3.6.4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.2.4) Gecko/20100503 Firefox/3.6.4 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; nb-NO; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ko; rv:1.9.2.4) Gecko/20100523 Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.2.4) Gecko/20100611 Firefox/3.6.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3pre) Gecko/20100405 Firefox/3.6.3plugin1 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.2.3) Gecko/20100403 Fedora/3.6.3-4.fc13 Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.3) Gecko/20100403 Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.2.3) Gecko/20100401 SUSE/3.6.3-1.1 Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux i686; ko-KR; rv:1.9.2.3) Gecko/20100423 Ubuntu/10.04 (lucid) Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.3) Gecko/20100404 Ubuntu/10.04 (lucid) Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.3) Gecko/20100423 Ubuntu/10.04 (lucid) Firefox/3.6.3",
            "Mozilla/5.0 (X11; U; Linux AMD64; en-US; rv:1.9.2.3) Gecko/20100403 Ubuntu/10.10 (maverick) Firefox/3.6.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; pl; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; it; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; hu; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 GTB7.0 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; cs; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ca; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux i686; fr; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 GTB7.0",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.2) Gecko/20100316 AskTbSPC2/3.9.1.14019 Firefox/3.6.2",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 GTB6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 ( .NET CLR 3.0.04506.648)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 ( .NET CLR 3.0.04506.30)",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.7; en-US; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.10pre) Gecko/20100902 Ubuntu/9.10 (karmic) Firefox/3.6.1pre",
            "Mozilla/5.0 (X11; U; Linux x86_64; ja-JP; rv:1.9.2.16) Gecko/20110323 Ubuntu/10.10 (maverick) Firefox/3.6.16",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.16) Gecko/20110323 Ubuntu/9.10 (karmic) Firefox/3.6.16 FirePHP/0.5",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; pl; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ko; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16 ( .NET CLR 3.5.30729; .NET4.0E)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en; rv:1.9.1.13) Gecko/20100914 Firefox/3.6.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.16) Gecko/20110319 AskTbUTR/3.11.3.15590 Firefox/3.6.16",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.16pre) Gecko/20110304 Ubuntu/10.10 (maverick) Firefox/3.6.15pre",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.15) Gecko/20110303 Ubuntu/10.04 (lucid) Firefox/3.6.15 FirePHP/0.5",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.15) Gecko/20110330 CentOS/3.6-1.el5.centos Firefox/3.6.15",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15 ( .NET CLR 3.5.30729; .NET4.0C) FirePHP/0.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.15) Gecko/20110303 AskTbBT4/3.11.3.15590 Firefox/3.6.15 ( .NET CLR 3.5.30729; .NET4.0C)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.14pre) Gecko/20110105 Firefox/3.6.14pre",
            "Mozilla/5.0 (X11; U; Linux armv7l; en-US; rv:1.9.2.14) Gecko/20110224 Firefox/3.6.14 MB860/Version.0.43.3.MB860.AmericaMovil.en.MX",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.14) Gecko/20110218 Firefox/3.6.14",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-AU; rv:1.9.2.14) Gecko/20110218 Firefox/3.6.14",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.14) Gecko/20110218 Firefox/3.6.14 GTB7.1 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9.2.13) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; pl-PL; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; nb-NO; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; it; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.2.13) Gecko/20110103 Fedora/3.6.13-1.fc14 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101223 Gentoo Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101219 Gentoo Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101206 Red Hat/3.6-3.el4 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101206 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-NZ; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.10 (maverick) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-GB; rv:1.9.2.13) Gecko/20101206 Ubuntu/9.10 (karmic) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-GB; rv:1.9.2.13) Gecko/20101206 Red Hat/3.6-2.el5 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; da-DK; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.10 (maverick) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux MIPS32 1074Kf CPS QuadCore; en-US; rv:1.9.2.13) Gecko/20110103 Fedora/3.6.13-1.fc14 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.10 (maverick) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux i686; pt-BR; rv:1.9.2.13) Gecko/20101209 Fedora/3.6.13-1.fc13 Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux i686; es-ES; rv:1.9.2.13) Gecko/20101206 Ubuntu/9.10 (karmic) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.13) Gecko/20101209 CentOS/3.6-2.el5.centos Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.10 (maverick) Firefox/3.6.13",
            "Mozilla/5.0 (X11; U; NetBSD i386; en-US; rv:1.9.2.12) Gecko/20101030 Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux x86_64; es-MX; rv:1.9.2.12) Gecko/20101027 Ubuntu/10.04 (lucid) Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux x86_64; es-ES; rv:1.9.2.12) Gecko/20101027 Fedora/3.6.12-1.fc13 Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux x86_64; es-ES; rv:1.9.2.12) Gecko/20101026 SUSE/3.6.12-0.7.1 Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.12) Gecko/20101102 Gentoo Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.12) Gecko/20101102 Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux ppc; fr; rv:1.9.2.12) Gecko/20101027 Ubuntu/10.10 (maverick) Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux i686; ko-KR; rv:1.9.2.12) Gecko/20101027 Ubuntu/10.10 (maverick) Firefox/3.6.12",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.2.12) Gecko/20101027 Ubuntu/10.10 (maverick) Firefox/3.6.12 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.12) Gecko/20101027 Fedora/3.6.12-1.fc13 Firefox/3.6.12",
            "Mozilla/5.0 (X11; FreeBSD x86_64; rv:2.0) Gecko/20100101 Firefox/3.6.12",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12 ( .NET CLR 3.5.30729; .NET4.0E)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; sv-SE; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12 (.NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729; .NET CLR 3.5.21022)",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; de; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12 GTB5",
            "Mozilla/5.0 (X11; U; Linux x86_64; ru; rv:1.9.2.11) Gecko/20101028 CentOS/3.6-2.el5.centos Firefox/3.6.11",
            "Mozilla/5.0 (X11; U; Linux armv7l; en-GB; rv:1.9.2.3pre) Gecko/20100723 Firefox/3.6.11",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; ru; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; it; rv:1.9.2.11) Gecko/20101012 Firefox/3.6.11 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; pt-BR; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; pl-PL; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; el-GR; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; cs-CZ; rv:1.9.2.10) Gecko/20100915 Ubuntu/10.04 (lucid) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.2.10) Gecko/20100915 Ubuntu/10.04 (lucid) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; fr-FR; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; es-AR; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.10) Gecko/20100915 Ubuntu/9.04 (jaunty) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.2.11) Gecko/20101013 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; en-CA; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.10) Gecko/20100915 Ubuntu/9.10 (karmic) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.10) Gecko/20100915 Ubuntu/10.04 (lucid) Firefox/3.6.10",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.2.10) Gecko/20100914 SUSE/3.6.10-0.3.1 Firefox/3.6.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ro; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; nl; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.1) Gecko/20100122 firefox/3.6.1",
            "Mozilla/5.0(Windows; U; Windows NT 7.0; rv:1.9.2) Gecko/20100101 Firefox/3.6",
            "Mozilla/5.0(Windows; U; Windows NT 5.2; rv:1.9.2) Gecko/20100101 Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2) Gecko/20100222 Ubuntu/10.04 (lucid) Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2) Gecko/20100130 Gentoo Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.2) Gecko/20100308 Ubuntu/10.04 (lucid) Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.2pre) Gecko/20100312 Ubuntu/9.04 (jaunty) Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2) Gecko/20100128 Gentoo Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2) Gecko/20100115 Ubuntu/10.04 (lucid) Firefox/3.6",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6 FirePHP/0.4",
            "Mozilla/5.0 (X11; Linux i686; rv:2.0) Gecko/20100101 Firefox/3.6",
            "Mozilla/5.0 (X11; FreeBSD i686) Firefox/3.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru-RU; rv:1.9.2) Gecko/20100105 MRA 5.6 (build 03278) Firefox/3.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; lt; rv:1.9.2) Gecko/20100115 Firefox/3.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.3a3pre) Gecko/20100306 Firefox3.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.8) Gecko/20100806 Firefox/3.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.2.3) Gecko/20100401 Firefox/3.6;MEGAUPLOAD 1.0",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ar; rv:1.9.2) Gecko/20100115 Firefox/3.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.2) Gecko/20100115 Firefox/3.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.2) Gecko/20100105 Firefox/3.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; pl; rv:1.9.2) Gecko/20100115 Firefox/3.6 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1b5pre) Gecko/20090517 Firefox/3.5b4pre (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1b4pre) Gecko/20090409 Firefox/3.5b4pre",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1b4pre) Gecko/20090401 Firefox/3.5b4pre",
            "Mozilla/5.0 (X11; U; Linux i686; nl-NL; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4 GTB5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; fr; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1b4) Gecko/20090423 Firefox/3.5b4 GTB5",
            "Mozilla/5.0 (X11; U; Linux x86_64; it; rv:1.9.1.9) Gecko/20100402 Ubuntu/9.10 (karmic) Firefox/3.5.9 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; it; rv:1.9.1.9) Gecko/20100330 Fedora/3.5.9-2.fc12 Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.1.9) Gecko/20100317 SUSE/3.5.9-0.1.1 Firefox/3.5.9 GTB7.0",
            "Mozilla/5.0 (X11; U; Linux x86_64; es-CL; rv:1.9.1.9) Gecko/20100402 Ubuntu/9.10 (karmic) Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux x86_64; cs-CZ; rv:1.9.1.9) Gecko/20100317 SUSE/3.5.9-0.1.1 Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux i686; nl; rv:1.9.1.9) Gecko/20100401 Ubuntu/9.10 (karmic) Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux i686; hu-HU; rv:1.9.1.9) Gecko/20100330 Fedora/3.5.9-1.fc12 Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux i686; es-ES; rv:1.9.1.9) Gecko/20100317 SUSE/3.5.9-0.1 Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100401 Ubuntu/9.10 (karmic) Firefox/3.5.9 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100315 Ubuntu/9.10 (karmic) Firefox/3.5.9",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.4) Gecko/20091028 Ubuntu/9.10 (karmic) Firefox/3.5.9",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; tr; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; hu; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; et; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; nl; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; es-ES; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9 GTB5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.13) Gecko/20101203 Firefox/3.5.9 (de)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9 GTB7.0 (.NET CLR 3.0.30618)",
            "Mozilla/5.0 (X11; U; Linux x86_64; ru; rv:1.9.1.8) Gecko/20100216 Fedora/3.5.8-1.fc12 Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux x86_64; es-ES; rv:1.9.1.8) Gecko/20100216 Fedora/3.5.8-1.fc11 Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.8) Gecko/20100318 Gentoo Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux i686; zh-CN; rv:1.9.1.8) Gecko/20100216 Fedora/3.5.8-1.fc12 Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux i686; ja-JP; rv:1.9.1.8) Gecko/20100216 Fedora/3.5.8-1.fc12 Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux i686; es-AR; rv:1.9.1.8) Gecko/20100214 Ubuntu/9.10 (karmic) Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.8) Gecko/20100214 Ubuntu/9.10 (karmic) Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8",
            "Mozilla/5.0 (X11; U; FreeBSD i386; ja-JP; rv:1.9.1.8) Gecko/20100305 Firefox/3.5.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; sl; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8 (.NET CLR 3.5.30729) FirePHP/0.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8 GTB6",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ja; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8 GTB7.0 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2) Gecko/20100305 Gentoo Firefox/3.5.7",
            "Mozilla/5.0 (X11; U; Linux x86_64; cs-CZ; rv:1.9.1.7) Gecko/20100106 Ubuntu/9.10 (karmic) Firefox/3.5.7",
            "Mozilla/5.0 (X11; U; Linux i686; es-ES; rv:1.9.1.7) Gecko/20091222 SUSE/3.5.7-1.1.1 Firefox/3.5.7",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7 GTB6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7 (.NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.30729; .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; fr; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7 (.NET CLR 3.0.04506.648)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fa; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.7) Gecko/20091221 MRA 5.5 (build 02842) Firefox/3.5.7 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.1.6) Gecko/20091215 Ubuntu/9.10 (karmic) Firefox/3.5.6",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.6) Gecko/20100117 Gentoo Firefox/3.5.6",
            "Mozilla/5.0 (X11; U; Linux i686; zh-CN; rv:1.9.1.6) Gecko/20091216 Fedora/3.5.6-1.fc11 Firefox/3.5.6 GTB6",
            "Mozilla/5.0 (X11; U; Linux i686; es-ES; rv:1.9.1.6) Gecko/20091201 SUSE/3.5.6-1.1.1 Firefox/3.5.6 GTB6",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.6) Gecko/20100118 Gentoo Firefox/3.5.6",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.1.6) Gecko/20091215 Ubuntu/9.10 (karmic) Firefox/3.5.6 GTB6",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.6) Gecko/20091215 Ubuntu/9.10 (karmic) Firefox/3.5.6 GTB7.0",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.6) Gecko/20091215 Ubuntu/9.10 (karmic) Firefox/3.5.6",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.6) Gecko/20091201 SUSE/3.5.6-1.1.1 Firefox/3.5.6",
            "Mozilla/5.0 (X11; U; Linux i686; cs-CZ; rv:1.9.1.6) Gecko/20100107 Fedora/3.5.6-1.fc12 Firefox/3.5.6",
            "Mozilla/5.0 (X11; U; Linux i686; ca; rv:1.9.1.6) Gecko/20091215 Ubuntu/9.10 (karmic) Firefox/3.5.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; it; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; id; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.6) Gecko/20091201 MRA 5.4 (build 02647) Firefox/3.5.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; nl; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.6) Gecko/20091201 MRA 5.5 (build 02842) Firefox/3.5.6 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.6) Gecko/20091201 MRA 5.5 (build 02842) Firefox/3.5.6",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 GTB6 (.NET CLR 3.5.30729) FBSMTWB",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729) FBSMTWB",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.1.5) Gecko/20091109 Ubuntu/9.10 (karmic) Firefox/3.5.5",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.8pre) Gecko/20091227 Ubuntu/9.10 (karmic) Firefox/3.5.5",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.5) Gecko/20091114 Gentoo Firefox/3.5.5",
            "Mozilla/5.0 (X11; U; Linux i686 (x86_64); en-US; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; uk; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.5) Gecko/20091102 MRA 5.5 (build 02842) Firefox/3.5.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ru; rv:1.9.1.5) Gecko/20091102 MRA 5.5 (build 02842) Firefox/3.5.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; zh-CN; rv:1.9.1.5) Gecko/Firefox/3.5.5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.5) Gecko/20091102 MRA 5.5 (build 02842) Firefox/3.5.5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.5) Gecko/20091102 MRA 5.5 (build 02842) Firefox/3.5.5",
            "Mozilla/5.0 (Windows NT 5.1; U; zh-cn; rv:1.8.1) Gecko/20091102 Firefox/3.5.5",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; pl; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 FBSMTWB",
            "Mozilla/5.0 (X11; U; Linux x86_64; ja; rv:1.9.1.4) Gecko/20091016 SUSE/3.5.4-1.1.2 Firefox/3.5.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.4) Gecko/20091016 Firefox/3.5.4 (.NET CLR 3.5.30729) FBSMTWB",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.9.1.4) Gecko/20091007 Firefox/3.5.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.9.1.4) Gecko/20091016 Firefox/3.5.4 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.1.4) Gecko/20091016 Firefox/3.5.4 ( .NET CLR 3.5.30729; .NET4.0E)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9.1.4) Gecko/20091007 Firefox/3.5.4",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.1.5) Gecko/20091109 Ubuntu/9.10 (karmic) Firefox/3.5.3pre",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.3) Gecko/20090914 Slackware/13.0_stable Firefox/3.5.3",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.3) Gecko/20090913 Firefox/3.5.3",
            "Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.1.3) Gecko/20091020 Ubuntu/9.10 (karmic) Firefox/3.5.3",
            "Mozilla/5.0 (X11; U; Linux i686; fr; rv:1.9.1.3) Gecko/20090913 Firefox/3.5.3",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.3) Gecko/20090919 Firefox/3.5.3",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.3) Gecko/20090912 Gentoo Firefox/3.5.3 FirePHP/0.3",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 GTB5",
            "Mozilla/5.0 (X11; U; FreeBSD i386; ru-RU; rv:1.9.1.3) Gecko/20090913 Firefox/3.5.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.5.3;MEGAUPLOAD 1.0 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de-DE; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ko; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; fi; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 2.0.50727; .NET CLR 3.0.30618; .NET CLR 3.5.21022; .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; bg; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ko; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; pl; rv:1.9.1.2) Gecko/20090911 Slackware Firefox/3.5.2",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.2) Gecko/20090803 Slackware Firefox/3.5.2",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.2) Gecko/20090803 Firefox/3.5.2 Slackware",
            "Mozilla/5.0 (X11; U; Linux i686; ru-RU; rv:1.9.1.2) Gecko/20090804 Firefox/3.5.2",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.2) Gecko/20090729 Slackware/13.0 Firefox/3.5.2",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (X11; U; Linux i686 (x86_64); fr; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-CN; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; pl; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB7.1 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; es-MX; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; uk; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ja; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ja; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; es-ES; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.16) Gecko/20101130 Firefox/3.5.16 FirePHP/0.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1.16) Gecko/20101130 AskTbMYC/3.9.1.14019 Firefox/3.5.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; it; rv:1.9.1.16) Gecko/20101130 Firefox/3.5.16 GTB7.1 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.1.16) Gecko/20101130 MRA 5.4 (build 02647) Firefox/3.5.16 ( .NET CLR 3.5.30729; .NET4.0C)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.16) Gecko/20101130 Firefox/3.5.16 GTB7.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.16) Gecko/20101130 AskTbPLTV5/3.8.0.12304 Firefox/3.5.16 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.1.16) Gecko/20101130 Firefox/3.5.16 GTB7.1 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.1.16) Gecko/20101130 Firefox/3.5.16 GTB7.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; it; rv:1.9.1.15) Gecko/20101027 Fedora/3.5.15-1.fc12 Firefox/3.5.15",
            "Mozilla/5.0 (X11; U; Linux i686; en-GB; rv:1.9.1.15) Gecko/20101027 Fedora/3.5.15-1.fc12 Firefox/3.5.15",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; ru; rv:1.9.1.13) Gecko/20100914 Firefox/3.5.13",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.12) Gecko/2009070611 Firefox/3.5.12",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.12) Gecko/20100824 MRA 5.7 (build 03755) Firefox/3.5.12",
            "Mozilla/5.0 (X11; U; Linux; en-US; rv:1.9.1.11) Gecko/20100720 Firefox/3.5.11",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1.11) Gecko/20100701 Firefox/3.5.11 ( .NET CLR 3.5.30729; .NET4.0C)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.9.1.11) Gecko/20100701 Firefox/3.5.11 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; hu; rv:1.9.1.11) Gecko/20100701 Firefox/3.5.11",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.1.10) Gecko/20100504 Firefox/3.5.11 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9.1.10) Gecko/20100506 SUSE/3.5.10-0.1.1 Firefox/3.5.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1.10) Gecko/20100504 Firefox/3.5.10 GTB7.0 ( .NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux x86_64; rv:1.9.1.1) Gecko/20090716 Linux Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.3) Gecko/20100524 Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.1) Gecko/20090716 Linux Mint/7 (Gloria) Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.1) Gecko/20090716 Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.1) Gecko/20090714 SUSE/3.5.1-1.1 Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux x86; rv:1.9.1.1) Gecko/20090716 Linux Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux i686; nl; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.2pre) Gecko/20090729 Ubuntu/9.04 (jaunty) Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1 GTB5",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.1) Gecko/20090722 Gentoo Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1.1) Gecko/20090714 SUSE/3.5.1-1.1 Firefox/3.5.1",
            "Mozilla/5.0 (X11; U; DragonFly i386; de; rv:1.9.1) Gecko/20090720 Firefox/3.5.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.1) Gecko/20090718 Firefox/3.5.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; tr; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; sv-SE; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1 GTB5 (.NET CLR 4.0.20506)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1 GTB5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.1.1) Gecko/20090715 Firefox/3.5.1 GTB5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11;U; Linux i686; en-GB; rv:1.9.1) Gecko/20090624 Ubuntu/9.04 (jaunty) Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1) Gecko/20090630 Firefox/3.5 GTB6",
            "Mozilla/5.0 (X11; U; Linux i686; ja; rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (X11; U; Linux i686; it-IT; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.04 (jaunty) Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686; fr; rv:1.9.1) Gecko/20090624 Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686; fr-FR; rv:1.9.1) Gecko/20090624 Ubuntu/9.04 (jaunty) Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1) Gecko/20090701 Ubuntu/9.04 (jaunty) Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686; en-us; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.04 (jaunty) Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1) Gecko/20090624 Ubuntu/8.04 (hardy) Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1) Gecko/20090624 Firefox/3.5",
            "Mozilla/5.0 (X11; U; Linux i686 (x86_64); de; rv:1.9.1) Gecko/20090624 Firefox/3.5",
            "Mozilla/5.0 (X11; U; FreeBSD i386; en-US; rv:1.9.1) Gecko/20090703 Firefox/3.5",
            "Mozilla/5.0 (X11; U; FreeBSD i386; en-US; rv:1.9.0.10) Gecko/20090624 Firefox/3.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; pl; rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES; rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1) Gecko/20090612 Firefox/3.5 (.NET CLR 4.0.20506)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1) Gecko/20090612 Firefox/3.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR 4.0.20506)",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.1) Gecko/20090624 Firefox/3.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; zh-TW; rv:1.9.1) Gecko/20090624 Firefox/3.5 (.NET CLR 3.5.30729)",
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.25 (KHTML, like Gecko) Chrome/12.0.706.0 Safari/534.25",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Ubuntu/10.10 Chromium/12.0.703.0 Chrome/12.0.703.0 Safari/534.24",
            "Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.24 (KHTML, like Gecko) Ubuntu/10.10 Chromium/12.0.702.0 Chrome/12.0.702.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/12.0.702.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/12.0.702.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.699.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.0; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.699.0 Safari/534.24",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_6) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.698.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.697.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.43 Safari/534.24",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.34 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.0; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.34 Safari/534.24",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.3 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.3 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.3 Safari/534.24",
            "Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.14 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.12 Safari/534.24",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_6) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.12 Safari/534.24",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Ubuntu/10.04 Chromium/11.0.696.0 Chrome/11.0.696.0 Safari/534.24",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.0 Safari/534.24",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.694.0 Safari/534.24",
            "Mozilla/5.0 (X11; Linux i686) AppleWebKit/534.23 (KHTML, like Gecko) Chrome/11.0.686.3 Safari/534.23",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.21 (KHTML, like Gecko) Chrome/11.0.682.0 Safari/534.21",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.21 (KHTML, like Gecko) Chrome/11.0.678.0 Safari/534.21",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7_0; en-US) AppleWebKit/534.21 (KHTML, like Gecko) Chrome/11.0.678.0 Safari/534.21",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.20 (KHTML, like Gecko) Chrome/11.0.672.2 Safari/534.20",
            "Mozilla/5.0 (Windows NT) AppleWebKit/534.20 (KHTML, like Gecko) Chrome/11.0.672.2 Safari/534.20",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-US) AppleWebKit/534.20 (KHTML, like Gecko) Chrome/11.0.672.2 Safari/534.20",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.20 (KHTML, like Gecko) Chrome/11.0.669.0 Safari/534.20",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.19 (KHTML, like Gecko) Chrome/11.0.661.0 Safari/534.19",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.18 (KHTML, like Gecko) Chrome/11.0.661.0 Safari/534.18",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-US) AppleWebKit/534.18 (KHTML, like Gecko) Chrome/11.0.660.0 Safari/534.18",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/11.0.655.0 Safari/534.17",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/11.0.655.0 Safari/534.17",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/11.0.654.0 Safari/534.17",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/11.0.652.0 Safari/534.17",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/10.0.649.0 Safari/534.17",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de-DE) AppleWebKit/534.17 (KHTML, like Gecko) Chrome/10.0.649.0 Safari/534.17",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.82 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux armv7l; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16",
            "Mozilla/5.0 (X11; U; FreeBSD x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16",
            "Mozilla/5.0 (X11; U; FreeBSD i386; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.134 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.134 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.134 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.134 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.648.133 Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.648.133 Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_2; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.133 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.648.127 Chrome/10.0.648.127 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.127 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.127 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.127 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.11 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru-RU) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.11 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.11 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.648.0 Chrome/10.0.648.0 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.648.0 Chrome/10.0.648.0 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.0 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.642.0 Chrome/10.0.642.0 Safari/534.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.639.0 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.638.0 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux i686 (x86_64); en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.634.0 Safari/534.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.634.0 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 SUSE/10.0.626.0 (KHTML, like Gecko) Chrome/10.0.626.0 Safari/534.16",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.15 (KHTML, like Gecko) Chrome/10.0.613.0 Safari/534.15",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.15 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.613.0 Chrome/10.0.613.0 Safari/534.15",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.15 (KHTML, like Gecko) Ubuntu/10.04 Chromium/10.0.612.3 Chrome/10.0.612.3 Safari/534.15",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.15 (KHTML, like Gecko) Chrome/10.0.612.1 Safari/534.15",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.15 (KHTML, like Gecko) Ubuntu/10.10 Chromium/10.0.611.0 Chrome/10.0.611.0 Safari/534.15",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/10.0.602.0 Safari/534.14",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/10.0.601.0 Safari/534.14",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/10.0.601.0 Safari/534.14",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/540.0 (KHTML,like Gecko) Chrome/9.1.0.0 Safari/540.0",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/540.0 (KHTML, like Gecko) Ubuntu/10.10 Chrome/9.1.0.0 Safari/540.0",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/9.0.601.0 Safari/534.14",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Ubuntu/10.10 Chromium/9.0.600.0 Chrome/9.0.600.0 Safari/534.14",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/9.0.600.0 Safari/534.14",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.599.0 Safari/534.13",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.84 Safari/534.13",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.44 Safari/534.13",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.19 Safari/534.13",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.15 Safari/534.13",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.15 Safari/534.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.596.0 Safari/534.13",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Ubuntu/10.04 Chromium/9.0.595.0 Chrome/9.0.595.0 Safari/534.13",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Ubuntu/9.10 Chromium/9.0.592.0 Chrome/9.0.592.0 Safari/534.13",
            "Mozilla/5.0 (X11; U; Windows NT 6; en-US) AppleWebKit/534.12 (KHTML, like Gecko) Chrome/9.0.587.0 Safari/534.12",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.12 (KHTML, like Gecko) Chrome/9.0.579.0 Safari/534.12",
            "Mozilla/5.0 (X11; U; Linux i686 (x86_64); en-US) AppleWebKit/534.12 (KHTML, like Gecko) Chrome/9.0.576.0 Safari/534.12",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/540.0 (KHTML, like Gecko) Ubuntu/10.10 Chrome/8.1.0.0 Safari/540.0",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.558.0 Safari/534.10",
            "Mozilla/5.0 (X11; U; CrOS i686 0.9.130; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.344 Safari/534.10",
            "Mozilla/5.0 (X11; U; CrOS i686 0.9.128; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.343 Safari/534.10",
            "Mozilla/5.0 (X11; U; CrOS i686 0.9.128; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.341 Safari/534.10",
            "Mozilla/5.0 (X11; U; CrOS i686 0.9.128; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.339 Safari/534.10",
            "Mozilla/5.0 (X11; U; CrOS i686 0.9.128; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.339",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Ubuntu/10.10 Chromium/8.0.552.237 Chrome/8.0.552.237 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de-DE) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.3 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/533.3",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.215 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.215 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.215 Safari/534.10",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.210 Safari/534.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.200 Safari/534.10",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.551.0 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.548.0 Safari/534.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.544.0 Safari/534.10",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.15) Gecko/20101027 Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.540.0 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.540.0 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de-DE) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.540.0 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.540.0 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.9 (KHTML, like Gecko) Chrome/7.0.531.0 Safari/534.9",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.8 (KHTML, like Gecko) Chrome/7.0.521.0 Safari/534.8",
            "Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.7 (KHTML, like Gecko) Chrome/7.0.517.24 Safari/534.7",
            "Mozilla/5.0 (X11; U; Linux x86_64; fr-FR) AppleWebKit/534.7 (KHTML, like Gecko) Chrome/7.0.514.0 Safari/534.7",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.7 (KHTML, like Gecko) Chrome/7.0.514.0 Safari/534.7",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.7 (KHTML, like Gecko) Chrome/7.0.514.0 Safari/534.7",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.6 (KHTML, like Gecko) Chrome/7.0.500.0 Safari/534.6",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; tr-TR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; fr-FR) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; cs-CZ) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja-JP) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_8; zh-cn) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_8; ja-jp) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; ja-jp) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; zh-cn) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; sv-se) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; ko-kr) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; ja-jp) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; it-it) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; fr-fr) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; es-es) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-gb) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; de-de) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; sv-SE) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ja-JP) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; de-DE) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; hu-HU) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de-DE) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ja-JP) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; it-IT) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/534.16+ (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; fr-ch) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; de-de) AppleWebKit/534.15+ (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; ar) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Android 2.2; Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-HK) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; tr-TR) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; nb-NO) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; fr-FR) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; zh-cn) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_1 like Mac OS X; zh-cn) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8G4 Safari/6533.18.5",
            "Mozilla/5.0 (iPod; U; CPU iPhone OS 4_2_1 like Mac OS X; he-il) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; fr; CPU iPhone OS 4_2_1 like Mac OS X; fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_1 like Mac OS X; zh-tw) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8G4 Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; pl-pl) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8F190 Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; fr-fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8F190 Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3 like Mac OS X; en-gb) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8F190 Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; nb-no) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; it-it) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; fi-fi) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_2_1 like Mac OS X; fi-fi) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/533.17.8 (KHTML, like Gecko) Version/5.0.1 Safari/533.17.8",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; th-th) AppleWebKit/533.17.8 (KHTML, like Gecko) Version/5.0.1 Safari/533.17.8",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-us) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+",
            "Mozilla/5.0 (X11; U; Linux x86_64; en-ca) AppleWebKit/531.2+ (KHTML, like Gecko) Version/5.0 Safari/531.2+",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ja-JP) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja-JP) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_8; ja-jp) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; fr) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; zh-cn) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ru-ru) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ko-kr) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; it-it) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/534.1+ (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-au) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; el-gr) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ca-es) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; zh-tw) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; ja-jp) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; it-it) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; fr-fr) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-en) AppleWebKit/533.16 (KHTML, like Gecko) Version/4.1 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; nl-nl) AppleWebKit/533.16 (KHTML, like Gecko) Version/4.1 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; ja-jp) AppleWebKit/533.16 (KHTML, like Gecko) Version/4.1 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; de-de) AppleWebKit/533.16 (KHTML, like Gecko) Version/4.1 Safari/533.16",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_7; en-us) AppleWebKit/533.4 (KHTML, like Gecko) Version/4.1 Safari/533.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en) AppleWebKit/526.9 (KHTML, like Gecko) Version/4.0dp1 Safari/526.8",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; tr) AppleWebKit/528.4+ (KHTML, like Gecko) Version/4.0dp1 Safari/526.11.2",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; en) AppleWebKit/528.4+ (KHTML, like Gecko) Version/4.0dp1 Safari/526.11.2",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; de) AppleWebKit/528.4+ (KHTML, like Gecko) Version/4.0dp1 Safari/526.11.2",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.1b3pre) Gecko/20081212 Mozilla/5.0 (Windows; U; Windows NT 5.1; en) AppleWebKit/526.9 (KHTML, like Gecko) Version/4.0dp1 Safari/526.8",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_6; en-gb) AppleWebKit/528.10+ (KHTML, like Gecko) Version/4.0dp1 Safari/526.11.2",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_4; en-us) AppleWebKit/528.4+ (KHTML, like Gecko) Version/4.0dp1 Safari/526.11.2",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_4; en-gb) AppleWebKit/528.4+ (KHTML, like Gecko) Version/4.0dp1 Safari/526.11.2",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; es-ES) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-gb) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; cs-CZ) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_8; en-us) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; da-dk) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ja-jp) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/533.4+ (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; de-de) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_2; ja-jp) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; nl-nl) AppleWebKit/531.22.7 (KHTML, like Gecko) Version/4.0.5 Safari/531.22.7",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B5097d Safari/6531.22.7",
            "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B117 Safari/6531.22.7",
            "Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10gin_lib.cc",
            "Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10",
            "Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/123",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; zh-TW) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; de-DE) AppleWebKit/532+ (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_4_11; hu-hu) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/531.21.11 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_2; ru-ru) AppleWebKit/533.2+ (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_2; de-at) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (iPhone; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10",
            "Mozilla/5.0 (iPhone Simulator; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7D11 Safari/531.21.10",
            "Mozilla/5.0 (iPad; U; CPU OS 3_2_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B500 Safari/53",
            "Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; es-es) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B367 Safari/531.21.10",
            "Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; es-es) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B360 Safari/531.21.10",
            "Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.1021.10gin_lib.cc",
            "Mozilla/5.0 (iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-us) AppleWebKit/531.9 (KHTML, like Gecko) Version/4.0.3 Safari/531.9",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_8; en-us) AppleWebKit/532.0+ (KHTML, like Gecko) Version/4.0.3 Safari/531.9.2009",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_8; en-us) AppleWebKit/532.0+ (KHTML, like Gecko) Version/4.0.3 Safari/531.9",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_1; nl-nl) AppleWebKit/532.3+ (KHTML, like Gecko) Version/4.0.3 Safari/531.9",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; fi-fi) AppleWebKit/531.9 (KHTML, like Gecko) Version/4.0.3 Safari/531.9",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-us) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.3 Safari/531.21.10",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/532+ (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; zh-TW) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; pl-PL) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja-JP) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; fr-FR) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; de-DE) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_7; en-us) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/531.2+ (KHTML, like Gecko) Version/4.0.1 Safari/530.18",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.1 Safari/530.18",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ru-RU) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; ja-JP) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; hu-HU) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; he-IL) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; he-IL) AppleWebKit/528+ (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; fr-FR) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; es-es) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; de-DE) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-TW) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; sv-SE) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-PT) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; nb-NO) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; hu-HU) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr-FR) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fi-FI) AppleWebKit/528.16 (KHTML, like Gecko) Version/4.0 Safari/528.16"
        );
        $rand_nub =  rand( 0, count( $user_agent ) - 1 );
        return $user_agent[ $rand_nub ];
    }

    /* For Product Variation */
    public function getProductVariationFromHtml($html, $base_url='') {
        $default_attributes = [];
        $productVariation = $html->find('#twister_feature_div #twisterContainer',0);
        // For single attribute
        if($productVariation) {
            $attributeId = $html->find('#twister_feature_div #twisterContainer form div',0)->getAttribute('id');
            $attributeName = str_replace('variation','',str_replace('_',' ',$attributeId));
            $attributeSlugName = str_replace('variation_','',$attributeId);
            $attributeLabelName = $html->find('#twister_feature_div form div label.a-form-label',0);
            if( $attributeLabelName ) {
                $attributeLabelName = str_replace(':','',trim($attributeLabelName->text()));
            }
            $attributeLabelValue = $html->find('#twister_feature_div form div span.selection',0);
            if( $attributeLabelValue ) {
                $attributeLabelValue = trim($attributeLabelValue->text());
            }

            $variations = $html->find('#twister_feature_div form div ul li');

            if ($variations) {
                $options = $p_variations = [];
                foreach ($variations as $variation) {
                    $name = $variation->find('.twisterImageDiv img',0);
                    $name2 = $variation->find('.a-list-item .twisterTextDiv',0);
                    $name3 = $variation->find('.imgSwatch',0);
                    $url = $variation->getAttribute('data-dp-url');
                    $price = $salePrice = '';
                    if ($name) {
                        $options[] = $name = trim($name->getAttribute('alt'));
                    } elseif ($name2) {
                        $options[] = $name = trim($name2->text());
                    } elseif ($name3) {
                        $options[] = $name = trim($name3->getAttribute('alt'));
                    }
                    $priceString = $variation->find('p.twisterSwatchPrice', 0);
                    if ($priceString) {
                        $price = $this->parsePrice(trim($variation->find('p.twisterSwatchPrice', 0)->text()));
                    }
                    
                    $class_string = $variation->getAttribute('class');
                    $classes = explode(' ', $class_string);
                    if (in_array('selected', $classes) || in_array('swatchSelect', $classes)) {
                        $asin = $variation->getAttribute('data-defaultasin');
                        if (!$asin) {
                            $exp1 = explode("/dp/", $base_url);
                            $exp2 = explode('/', end($exp1));
                            $reverse = array_reverse($exp2);
                            $asin = array_pop($reverse);
                        }
                        $fetchPrice = $this->fetchPriceFromHtml($html);
                        $price = $this->parsePrice($fetchPrice['regular_price']);
                        $salePrice = $this->parsePrice($fetchPrice['amount']);
                        $default_attributes[strtolower($attributeSlugName)] = $name;
                    } else {
                        $url = $variation->getAttribute('data-dp-url');
                        $url = $base_url . '' . $url; 
                        $asin = $variation->getAttribute('data-defaultasin');
                    }
                    $url = $variation->getAttribute('data-dp-url');
                    $p_variations[] = array(
                        'attributes' => array(
                            strtolower($attributeSlugName) => $name,
                        ),
                        'sku' => $asin,
                        'regular_price' => $price,
                        'sale_price' => $salePrice,
                    );
                }
                $attributes_data = array(
                    array('name' => $attributeLabelName, 'slug' => $attributeSlugName, 'options' => $options, 'visible' => 1, 'variation' => 1)
                );
                return [
                    'attributes_data' => $attributes_data,
                    'product_variations' => $p_variations,
                    'default_attributes' => $default_attributes
                ];
            }
        }

        // For multiple attribute => In progress
        $productVariation = $html->find('#twister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister',0);
            if ($productVariation) {
                $fetchPrice = $this->fetchPriceFromHtml($html);
                $p_variations = $attributes_data = [];
                // Check for single value
                $attributes = $html->find('#twister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister .inline-twister-singleton-header');
                if ($attributes) {
                    $selected_attributes = [];
                    foreach ($attributes as $attribute) {
                        $attributeId = $attribute->getAttribute('id');
                        $attributeSlugName = str_replace('inline-twister-singleton-header-', '', $attributeId);
                        $attributeName = str_replace('_', ' ', str_replace('inline-twister-singleton-header-', '', $attributeId));
                        $options = [];
                        $title = $name = $attributeLabelName = '';
                        $titleString = $attribute->find('.inline-twister-dim-title', 0);
                        if ($titleString) {
                            $title = trim($titleString->text());
                            $valString = $titleString->nextSibling();
                            if ($valString) {
                                $options[] = $name = trim($valString->text());
                            }
                        }
                        if ($title) {
                            $attributeLabelName = str_replace(':', '', $title);
                        }
                        $selected_attributes[strtolower($attributeSlugName)] = $name;
                        $default_attributes = $selected_attributes;
                        
                        $attributes_data[] = array('name' => $attributeLabelName, 'slug' => $attributeSlugName, 'options' => $options, 'visible' => 1, 'variation' => 1);
                    }
                    $asin = '';
                    $fetchPrice = $this->fetchPriceFromHtml($html);
                    $p_variations[] = array(
                        'attributes' => $selected_attributes,
                        'sku' => $asin,
                        'regular_price' => $this->parsePrice($fetchPrice['regular_price']),
                        'sale_price' => $this->parsePrice($fetchPrice['amount']),
                    );
                }
                // Return
                return [
                    'attributes_data' => $attributes_data,
                    'product_variations' => $p_variations,
                    'default_attributes' => $default_attributes
                ];
            }
        // Return
        return [
            'attributes_data' => [],
            'product_variations' => [],
            'default_attributes' => [],
        ];
    }

    /* For Multi Level Product Variation */
    public function getProductMultiLevelVariationFromHtml($html, $base_url='') {
        $default_attributes = $attributes_data = [];
        $attributes = $html->find('#twister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister .inline-twister-row');
        if( $attributes ) {
            foreach ($attributes as $key => $attribute) {
                $attributeId = $attribute->getAttribute('id');
                $attributeSlugName = str_replace('inline-twister-row-','',$attributeId);

                $selectedTitleSelector = '#inline-twister-dim-title-' . str_replace('inline-twister-row-','',$attributeId);
                $selectedTitle = $attribute->find($selectedTitleSelector, 0);
                $selectedTitleClassSelector = $attribute->find('.dimension-text', 0);
                if( $selectedTitle ) {
                    $label = $attribute->find($selectedTitleSelector . ' > div > span', 0);
                    $value = $attribute->find($selectedTitleSelector . ' > div > span', 1);
                    if( $label ) {
                        $labelText = $label->text();
                    }
                    $value = $attribute->find('.dimension-text > div > span', 1);
                    if( $value ) {
                        $valueText = $value->text();
                    }
                } else if( $selectedTitleClassSelector ) {
                    $label = $attribute->find('.dimension-text > div > span', 0);
                    if( $label ) {
                        $labelText = $label->text();
                    }
                    $value = $attribute->find('.dimension-text > div > span', 1);
                    if( $value ) {
                        $valueText = $value->text();
                    }
                }

                if( $labelText ) {
                    $attributeLabelName = str_replace(':','',trim($labelText));
                }

                $options = [];
                $variations = $attribute->find('ul li');
                if( $variations ) {
                    foreach ($variations as $key => $variation) {
                        // hidden check
                        $classes = explode(' ', $variation->getAttribute('class'));
                        if( in_array('swatch-prototype', $classes) ) continue;
                        $title = '';
                        $titleString = $variation->find('.swatch-title-text',0);
                        $titleString2 = $variation->find('img.swatch-image',0);
                        if( $titleString ) {
                            $title = $titleString->text();
                        } elseif ($titleString2) {
                            $title = $titleString2->getAttribute('alt');
                        }

                        $options[] = $title;

                        // check if selected
                        $selectedExists = $variation->find('.a-button-selected',0);
                        if( $selectedExists ) {
                            $default_attributes[strtolower($attributeSlugName)] = $title;
                        } else {}
                    }

                    $attributes_data[] = array( 
                        'name' => $attributeLabelName, 
                        'slug' => $attributeSlugName, 
                        'options' => $options, 
                        'visible' => 1, 
                        'variation' => 1 
                    );
                }
            }
        }

        $attributes = $html->find('#softlinesTwister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister .inline-twister-row');
        if( $attributes ) {
            foreach ($attributes as $key => $attribute) {
                $attributeId = $attribute->getAttribute('id');
                $attributeSlugName = str_replace('inline-twister-row-','',$attributeId);

                $selectedTitleSelector = '#inline-twister-dim-title-' . str_replace('inline-twister-row-','',$attributeId);
                $selectedTitle = $attribute->find($selectedTitleSelector, 0);
                $selectedTitleClassSelector = $attribute->find('.dimension-text', 0);
                if( $selectedTitle ) {
                    $label = $attribute->find($selectedTitleSelector . ' > div > span', 0);
                    $value = $attribute->find($selectedTitleSelector . ' > div > span', 1);
                    if( $label ) {
                        $labelText = $label->text();
                    }
                    $value = $attribute->find('.dimension-text > div > span', 1);
                    if( $value ) {
                        $valueText = $value->text();
                    }
                } else if( $selectedTitleClassSelector ) {
                    $label = $attribute->find('.dimension-text > div > span', 0);
                    if( $label ) {
                        $labelText = $label->text();
                    }
                    $value = $attribute->find('.dimension-text > div > span', 1);
                    if( $value ) {
                        $valueText = $value->text();
                    }
                }

                if( $labelText ) {
                    $attributeLabelName = str_replace(':','',trim($labelText));
                }

                $options = [];
                $variations = $attribute->find('ul li');
                if( $variations ) {
                    foreach ($variations as $key => $variation) {
                        // hidden check
                        $classes = explode(' ', $variation->getAttribute('class'));
                        if( in_array('swatch-prototype', $classes) ) continue;
                        $title = '';
                        $titleString = $variation->find('.swatch-title-text',0);
                        $titleString2 = $variation->find('img.swatch-image',0);
                        if( $titleString ) {
                            $title = $titleString->text();
                        } elseif ($titleString2) {
                            $title = $titleString2->getAttribute('alt');
                        }

                        $options[] = $title;

                        // check if selected
                        $selectedExists = $variation->find('.a-button-selected',0);
                        if( $selectedExists ) {
                            $default_attributes[strtolower($attributeSlugName)] = $title;
                        } else {}
                    }

                    $attributes_data[] = array( 
                        'name' => $attributeLabelName, 
                        'slug' => $attributeSlugName, 
                        'options' => $options, 
                        'visible' => 1, 
                        'variation' => 1 
                    );
                }
            }
        }

        return [
            'attributes_data' => $attributes_data,
            'default_attributes' => $default_attributes
        ];
    }

    public function getSkusFromHtml($html, $skus = []) {
        // Attributes that might contain ASINs
        $attributes_to_check = ['data-asin', 'data-defaultasin', 'data-csa-c-item-id', 'data-asin-id', 'data-sku', 'data-product-id'];
        
        // Extract ASINs from common attributes
        foreach ($attributes_to_check as $attr) {
            $elements = $html->find("[$attr]");
            foreach ($elements as $element) {
                $asin = $element->getAttribute($attr);
                if ($asin && strlen($asin) == 10) {
                    $skus[] = $asin;
                }
            }
        }

        // Check for ASINs in data-dp-url attributes
        $elements = $html->find('[data-dp-url]');
        foreach ($elements as $element) {
            $dp_url = $element->getAttribute('data-dp-url');
            if (preg_match('/\/dp\/([A-Z0-9]{10})/', $dp_url, $matches)) {
                $skus[] = $matches[1];
            }
        }

        // Extract SKUs from embedded JavaScript data
        $scripts = $html->find('script');
        foreach ($scripts as $script) {
            $scriptContent = $script->innertext;
            
            // Extract ASINs from asinVariationValues
            if (preg_match('/var\s+asinVariationValues\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $asinVariationValues = json_decode($matches[1], true);
                if ($asinVariationValues) {
                    $skus = array_merge($skus, array_keys($asinVariationValues));
                }
            }
            
            // Extract ASINs from dimensionValuesDisplayData
            if (preg_match('/var\s+dimensionValuesDisplayData\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $dimensionData = json_decode($matches[1], true);
                if ($dimensionData) {
                    foreach ($dimensionData as $dimension) {
                        if (isset($dimension['dimensionValues'])) {
                            foreach ($dimension['dimensionValues'] as $value) {
                                if (isset($value['asin']) && strlen($value['asin']) == 10) {
                                    $skus[] = $value['asin'];
                                }
                            }
                        }
                    }
                }
            }
            
            // Extract ASINs from initial_asins
            if (preg_match('/"initial_asins"\s*:\s*(\[.*?\])/', $scriptContent, $matches)) {
                $initialAsins = json_decode($matches[1], true);
                if ($initialAsins) {
                    $skus = array_merge($skus, $initialAsins);
                }
            }

            // Extract parent ASIN
            if (preg_match('/"parentAsin"\s*:\s*"([A-Z0-9]{10})"/', $scriptContent, $matches)) {
                $skus[] = $matches[1];
            }

            // Extract ASINs from other possible JSON structures
            if (preg_match_all('/"asin"\s*:\s*"([A-Z0-9]{10})"/', $scriptContent, $matches)) {
                $skus = array_merge($skus, $matches[1]);
            }
        }

        // Look for SKUs in twister data
        $twisterData = $html->find('#twister-plus-inline-twister', 0);
        if ($twisterData) {
            $variationItems = $twisterData->find('.a-declarative');
            foreach ($variationItems as $item) {
                $dataAsin = $item->getAttribute('data-asin');
                if ($dataAsin && strlen($dataAsin) == 10) {
                    $skus[] = $dataAsin;
                }
            }
        }

        // Remove duplicates and invalid ASINs
        $skus = array_unique($skus);
        $skus = array_filter($skus, function($asin) {
            return preg_match('/^[A-Z0-9]{10}$/', $asin);
        });

        return array_values($skus);
    }

    public function extractAllProductVariants($html, $url = '')
    {
        $variants = [];
        $dimensionValuesDisplayData = [];
        $asinVariationValues = [];
        $dataToReturn = null;

        // Extract script content
        $scripts = $html->find('script');
        foreach ($scripts as $script) {
            $scriptContent = $script->innertext;

            // Look for dimensionValuesDisplayData
            if (preg_match('/var\s+dimensionValuesDisplayData\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $dimensionValuesDisplayData = json_decode($matches[1], true);
            }

            // Look for asinVariationValues
            if (preg_match('/var\s+asinVariationValues\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $asinVariationValues = json_decode($matches[1], true);
            }

            // Look for dataToReturn
            if (preg_match('/var\s+dataToReturn\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $dataToReturn = json_decode($matches[1], true);
            }

            // If we've found all, break the loop
            if (!empty($dimensionValuesDisplayData) && !empty($asinVariationValues) && $dataToReturn !== null) {
                break;
            }
        }

        // Process the variation data
        if (!empty($dataToReturn) && isset($dataToReturn['dimensionValuesData'])) {
            foreach ($dataToReturn['dimensionValuesData'] as $dimension => $values) {
                foreach ($values as $value) {
                    if (isset($value['asin']) && isset($value['dimensionValue'])) {
                        $variants[$value['asin']][$dimension] = $value['dimensionValue'];
                    }
                }
            }
        } elseif (!empty($dimensionValuesDisplayData) && !empty($asinVariationValues)) {
            foreach ($asinVariationValues as $asin => $variationData) {
                $variant = ['asin' => $asin];
                foreach ($variationData as $dimensionIndex => $valueIndex) {
                    $dimensionKey = array_keys($dimensionValuesDisplayData)[$dimensionIndex];
                    $variant[$dimensionKey] = $dimensionValuesDisplayData[$dimensionKey]['displayValuesJson'][$valueIndex];
                }
                $variants[$asin] = $variant;
            }
        }

        // If we still don't have all variants, try to extract from HTML
        if (empty($variants)) {
            $list_items = $html->find('ul[data-action="a-button-group"] li');
            foreach ($list_items as $item) {
                $asin = $item->getAttribute('data-defaultasin') ?: $item->getAttribute('data-csa-c-item-id');
                if ($asin && strlen($asin) == 10) {
                    $variants[$asin] = [
                        'asin' => $asin,
                        'title' => $item->getAttribute('title')
                    ];
                }
            }
        }

        // Add debug logging
        //error_log("Extracted variants: " . print_r($variants, true));

        return $variants;
    }


####################################################################################
/**
 * Retrieves product attribute data from the given HTML.
 * Returns an array of attributes (each with name, slug, selected value, and, if applicable, options).
 */
public function getProductAttributeFromHtml($html) {
    $attributes_data = array();
    $known_size_slugs = array('size', 'tama-o', 'tamaño', 'talla', 'größe');
    $known_color_slugs = array('color', 'colour', 'farbe');

    // Re-ordered list: legacy variation containers first, then inline twister ones.
    $attribute_containers = array(
        '#variation_fit_type',
        '#variation_color_name',
        '#variation_size_name',
        '#twister-plus-inline-twister-card #twister-plus-inline-twister div[id^="inline-twister-row"]',
        '#twister_feature_div #twisterContainer',
        '#twister-plus-inline-twister-card #twister-plus-inline-twister div[id*="inline-twister-row"]',
        '#softlinesTwister_feature_div #twisterContainer',
        '#twister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister',
        '#softlinesTwister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister',
        '#twister-plus-inline-twister-card #twister-plus-inline-twister',
        'div[id^="inline-twister-expander-content-"]'
    );

    foreach ($attribute_containers as $container) {
        // For inline twister rows and expander content.
        if (strpos($container, 'inline-twister-row') !== false || strpos($container, 'inline-twister-expander-content') !== false) {
            $rows = $html->find($container);
            foreach ($rows as $row) {
                $attribute = $this->extractInlineTwisterAttribute($row);
                if (!empty($attribute)) {
                    // Normalize slug: remove trailing "-name" so that duplicates match.
                    $attribute['slug'] = preg_replace('/-name$/', '', $attribute['slug']);
                    
                    // Standardize common attribute types (size, color)
                    $standardized_slug = $this->standardizeAttributeSlug($attribute['slug'], $known_size_slugs, $known_color_slugs);
                    $attribute['slug'] = $standardized_slug;
                    
                    // Check if we should merge or add this attribute
                    if (!isset($attributes_data[$standardized_slug])) {
                        $attributes_data[$standardized_slug] = $attribute;
                    } else if (isset($attribute['options']) && count($attribute['options']) > 0) {
                        // Keep the attribute with options if one doesn't have them
                        if (!isset($attributes_data[$standardized_slug]['options']) || 
                            count($attributes_data[$standardized_slug]['options']) < count($attribute['options'])) {
                            $attributes_data[$standardized_slug] = $attribute;
                        }
                    }
                }
            }
            continue;
        }

        // For legacy variation containers.
        $productVariation = $html->find($container, 0);
        if ($productVariation) {
            $extracted = $this->extractAttributes($html, $container);
            if (!empty($extracted)) {
                foreach ($extracted as $attribute) {
                    // Clean the name and slug.
                    $attribute['name'] = trim(str_replace(':', '', html_entity_decode($attribute['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                    $attribute['slug'] = str_replace('-58', '', $attribute['slug']);
                    // Normalize slug: remove trailing "-name"
                    $attribute['slug'] = preg_replace('/-name$/', '', $attribute['slug']);
                    
                    // Standardize common attribute types (size, color)
                    $standardized_slug = $this->standardizeAttributeSlug($attribute['slug'], $known_size_slugs, $known_color_slugs);
                    $attribute['slug'] = $standardized_slug;
                    
                    // Only process if it has a selected value
                    if (isset($attribute['selected']) && !empty($attribute['selected'])) {
                        if (!isset($attributes_data[$standardized_slug])) {
                            $attributes_data[$standardized_slug] = $attribute;
                        } else if (isset($attribute['options']) && count($attribute['options']) > 0) {
                            // Keep the attribute with options if one doesn't have them
                            if (!isset($attributes_data[$standardized_slug]['options']) || 
                                count($attributes_data[$standardized_slug]['options']) < count($attribute['options'])) {
                                // Preserve the name from the existing attribute if it's cleaner
                                if (isset($attributes_data[$standardized_slug]['name']) && 
                                    strpos($attributes_data[$standardized_slug]['name'], 'Name') === false &&
                                    strpos($attribute['name'], 'Name') !== false) {
                                    $attribute['name'] = $attributes_data[$standardized_slug]['name'];
                                }
                                $attributes_data[$standardized_slug] = $attribute;
                            }
                        }
                    }
                }
            }
        }
    }

    return array_values($attributes_data);
}

/**
 * Extracts the inline twister attribute from a row.
 * Returns an array with the attribute name, slug, selected value, and all available options.
 */
private function extractInlineTwisterAttribute($row) {
    // Try to get the attribute name from the expected span.
    $nameContainer = $row->find('span.a-color-secondary', 0);
    if ($nameContainer) {
        $attributeName = trim(str_replace(':', '', $nameContainer->plaintext));
    } else {
        // Fallback: if the element's id matches the new pattern, extract attribute name.
        if (isset($row->id) && preg_match('/^inline-twister-expander-content-(.+)$/', $row->id, $matches)) {
            $attributeName = ucwords(str_replace('_', ' ', $matches[1]));
        } else {
            return array();
        }
    }
    $slug = $this->generateAttributeSlug($attributeName);

    // Determine the selected value.
    $selected = '';
    $selectedValueEl = $row->find('span.inline-twister-dim-title-value', 0);
    if ($selectedValueEl) {
        $selected = html_entity_decode(trim($selectedValueEl->plaintext), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (empty($selected)) {
        $selectedButton = $row->find('span.a-button-selected', 0);
        if ($selectedButton) {
            $titleTextSpan = $selectedButton->find('span.swatch-title-text', 0);
            if ($titleTextSpan) {
                $selected = html_entity_decode(trim($titleTextSpan->plaintext), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
    }
    if (empty($selected)) {
        $selectedOption = $row->find('li.swatchSelect', 0);
        if ($selectedOption) {
            $selected = $this->getOptionText($selectedOption);
        }
    }

    // Gather available options.
    $options = array();
    $liElements = $row->find('ul li');
    if ($liElements) {
        foreach ($liElements as $li) {
            $img = $li->find('img', 0);
            $optionText = $img ? trim($img->getAttribute('alt')) : '';
            if (empty($optionText)) {
                $buttonText = $li->find('span.swatch-title-text', 0);
                $optionText = $buttonText ? trim($buttonText->plaintext) : '';
            }
            if (!empty($optionText)) {
                $options[] = $optionText;
            }
            if ($li->getAttribute('data-initiallyselected') === 'true') {
                $selected = $optionText;
            }
        }
    }
    if (empty($options)) {
        $allOptions = $row->find('li.swatch-list-item-text, li.inline-twister-swatch');
        foreach ($allOptions as $option) {
            $titleTextSpan = $option->find('span.swatch-title-text', 0);
            if ($titleTextSpan) {
                $optionText = html_entity_decode(trim($titleTextSpan->plaintext), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (!empty($optionText)) {
                    $options[] = $optionText;
                }
            }
        }
    }
    if (empty($options)) {
        $allButtons = $row->find('span.a-button');
        foreach ($allButtons as $button) {
            $titleTextSpan = $button->find('span.swatch-title-text', 0);
            if ($titleTextSpan) {
                $optionText = html_entity_decode(trim($titleTextSpan->plaintext), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (!empty($optionText)) {
                    $options[] = $optionText;
                }
            }
        }
    }
    if (empty($selected) && !empty($options)) {
        $selected = $options[0];
    }
    if (empty($selected) && empty($options)) {
        return array();
    }

    return array(
        'name'      => $attributeName,
        'slug'      => $slug,
        'selected'  => $selected,
        'options'   => $options,
        'visible'   => 1,
        'variation' => 1
    );
}

/**
 * Extracts attributes for legacy structures.
 * This function handles both swatch-based attributes (Fit Type, Colour) and dropdown-based attributes (Size).
 * It returns an array of attribute arrays.
 */
private function extractAttributes($html, $container) {
    $attributes = array();

    // For dropdown-based attributes (size, color).
    if ($container === '#variation_size_name' || $container === '#variation_color_name') {
        $singletonDiv = $html->find($container, 0);
        if ($singletonDiv) {
            $attribute_name = $this->getAttributeName($singletonDiv);
            $dropdownData = $this->getSingletonOption($html, $container);
            if (!empty($attribute_name) && !empty($dropdownData['options'])) {
                $slug = $this->generateAttributeSlug($attribute_name);
                // If no explicit selection, choose the first available option.
                if (empty($dropdownData['selected'])) {
                    $dropdownData['selected'] = $dropdownData['options'][0];
                }
                $attributes[] = array(
                    'name'      => $attribute_name,
                    'slug'      => $slug,
                    'selected'  => html_entity_decode($dropdownData['selected'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'options'   => $dropdownData['options'],
                    'visible'   => 1,
                    'variation' => 1
                );
            }
        }
        return $attributes;
    }

    // For swatch-based attributes (Fit Type, etc.)
    if (strpos($container, 'variation_') !== false && $container !== '#variation_size_name' && $container !== '#variation_color_name') {
        $variationDiv = $html->find($container, 0);
        if ($variationDiv) {
            // Extract attribute name from the label.
            $label = $variationDiv->find('label.a-form-label', 0);
            $attribute_name = $label ? trim(str_replace(':', '', $label->plaintext)) : '';
            
            // Extract the selected value from the span with class "selection".
            $selectedEl = $variationDiv->find('span.selection', 0);
            $selected = $selectedEl ? trim($selectedEl->plaintext) : '';
            
            // Gather available options from the swatch list.
            $options = array();
            $swatchList = $variationDiv->find('ul.swatches, ul.swatchesRectangle, ul.imageSwatches', 0);
            if ($swatchList) {
                foreach ($swatchList->find('li') as $li) {
                    $p = $li->find('p', 0);
                    if ($p) {
                        $optionText = trim($p->plaintext);
                        if (!empty($optionText)) {
                            $options[] = $optionText;
                        }
                    }
                }
            }
            
            // Only add the attribute if we have a name and at least one value.
            if (!empty($attribute_name) && (!empty($selected) || !empty($options))) {
                $slug = $this->generateAttributeSlug($attribute_name);
                $attributes[] = array(
                    'name'      => $attribute_name,
                    'slug'      => $slug,
                    'selected'  => $selected,
                    'options'   => $options,
                    'visible'   => 1,
                    'variation' => 1
                );
            }
        }
    }

    // For legacy structures that use twisterContainer.
    if (strpos($container, 'twisterContainer') !== false) {
        $variationDivs = $html->find($container . ' div[id^="variation_"]');
        foreach ($variationDivs as $variation) {
            $label = $variation->find('label.a-form-label', 0);
            if ($label) {
                $attribute_name = trim(str_replace(':', '', $label->plaintext));
                // Try to get the selected value.
                $selected = '';
                $dropdown = $variation->find('select option[selected]', 0);
                if ($dropdown) {
                    $selected = trim($dropdown->plaintext);
                } else {
                    $selected = $this->getSelectedOption($variation);
                }
                if (!empty($attribute_name) && !empty($selected)) {
                    $slug = $this->generateAttributeSlug($attribute_name);
                    $attributes[] = array(
                        'name'      => $attribute_name,
                        'slug'      => $slug,
                        'selected'  => html_entity_decode($selected, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'visible'   => 1,
                        'variation' => 1
                    );
                }
            }
        }
    }

    return $attributes;
}

/**
 * Helper: For singleton containers (e.g. #variation_size_name) returns an array with:
 * - 'selected': the default selected value (if any; if none, choose the first available)
 * - 'options': an array of all available options (excluding the "Select" placeholder)
 */
private function getSingletonOption($html, $container) {
    $result = array(
        'selected' => '',
        'options'  => array()
    );
    
    $select = $html->find($container . ' select', 0);
    if ($select) {
        foreach ($select->find('option') as $opt) {
            $opt_text = trim($opt->plaintext);
            // Skip the default "Select" prompt.
            if (strcasecmp($opt_text, 'Select') === 0 || empty($opt_text)) {
                continue;
            }
            // Check if the option is available based on its class.
            $class = $opt->getAttribute('class');
            if ($class && strpos($class, 'dropdownAvailable') !== false) {
                $result['options'][] = $opt_text;
            }
        }
        
        // Capture an explicitly selected option, if present.
        $selectedOption = $html->find($container . ' select option[selected]', 0);
        if ($selectedOption) {
            $selectedText = trim($selectedOption->plaintext);
            if (strcasecmp($selectedText, 'Select') === 0) {
                $selectedText = '';
            }
            if (!empty($selectedText)) {
                $result['selected'] = $selectedText;
            }
        }
        
        // If no valid selection is found, default to the first available option.
        if (empty($result['selected']) && !empty($result['options'])) {
            $result['selected'] = $result['options'][0];
        }
    }
    
    return $result;
}

/**
 * Helper: Extracts the attribute name from a variation container.
 */
private function getAttributeName($variation) {
    $label = $variation->find('label.a-form-label', 0);
    if (!$label) {
        $label = $variation->find('span.selection', 0);
    }
    return $label ? trim(html_entity_decode($label->plaintext, ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
}

/**
 * Helper: Generates a slug from the attribute name.
 */
private function generateAttributeSlug($text) {
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Helper: Standardizes common attribute slugs to prevent duplicates.
 * For example, "size-name", "tama-o", and "talla" should all map to "size".
 */
private function standardizeAttributeSlug($slug, $size_slugs, $color_slugs) {
    // Check if this is a size-related attribute
    foreach ($size_slugs as $size_slug) {
        if (strpos($slug, $size_slug) !== false || levenshtein($slug, $size_slug) <= 2) {
            return 'size';
        }
    }
    
    // Check if this is a color-related attribute
    foreach ($color_slugs as $color_slug) {
        if (strpos($slug, $color_slug) !== false || levenshtein($slug, $color_slug) <= 2) {
            return 'color';
        }
    }
    
    // Remove common suffixes that don't add value
    $slug = preg_replace('/-name$/', '', $slug);
    
    return $slug;
}

/**
 * Helper: Returns the text for an option element (swatch, dropdown, etc.).
 */
private function getOptionText($option) {
    $text = '';
    $img = $option->find('img.imgSwatch', 0);
    if ($img) {
        $text = $img->getAttribute('alt');
    }
    if (!$text) {
        $textDiv = $option->find('div.twisterTextDiv p', 0);
        if ($textDiv) {
            $text = $textDiv->plaintext;
        }
    }
    if (!$text && $option->tag === 'option') {
        $text = $option->plaintext;
    }
    if ($text) {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);
    }
    return $text;
}

/**
 * Helper: Returns a selected option for a variation.
 * Tries to find a list item with class "swatchSelect" or a selected dropdown option.
 */
private function getSelectedOption($variation) {
    $selectedOption = $variation->find('li.swatchSelect', 0) ?? $variation->find('option[selected]', 0);
    return $selectedOption ? $this->getOptionText($selectedOption) : '';
}

/**
 * Helper: Cleans an array of options.
 */
private function cleanOptions($options) {
    return array_values(array_filter($options, function($option) {
        return $option !== 'Select' && !empty(trim($option));
    }));
}

/**
 * Extracts a numeric price from a string.
 */
private function extractNumericPrice($price_string) {
    preg_match('/[\d,.]+/', $price_string, $matches);
    return isset($matches[0]) ? floatval(str_replace(',', '', $matches[0])) : null;
}
####################################################################################







    public function createVariants($attributes) {
        $variants = [];
        $base_price = $attributes['price'] ?? 0;
        unset($attributes['price']);

        $combinations = $this->generateAttributeCombinations($attributes);

        foreach ($combinations as $combination) {
            $variant = [
                'attributes' => $combination,
                'price' => $base_price,
                'sku' => $this->generateSKU($combination),
                'stock_quantity' => 100, // Default stock, adjust as needed
                'stock_status' => 'instock'
            ];
            $variants[] = $variant;
        }

        return $variants;
    }

    private function generateAttributeCombinations($attributes, $current = [], $keys = null, $i = 0) {
        if (is_null($keys)) {
            $keys = array_keys($attributes);
        }

        if ($i >= count($keys)) {
            return [$current];
        }

        $key = $keys[$i];
        $combinations = [];

        foreach ($attributes[$key]['options'] as $option) {
            $new = $current;
            $new[$key] = $option;
            $combinations = array_merge($combinations, $this->generateAttributeCombinations($attributes, $new, $keys, $i + 1));
        }

        return $combinations;
    }

    private function generateSKU($combination) {
        $sku_parts = [];
        foreach ($combination as $attribute => $value) {
            $sku_parts[] = substr(preg_replace('/[^a-zA-Z0-9]/', '', $value), 0, 3);
        }
        return strtoupper(implode('-', $sku_parts));
    }

    public function getProductFirstVariationFromHtml($html, $parentSku, $base_url = '', $all_skus = []) {
        $variation_ids = [];
        $extract_ID = $this->getSkuFromUrl($base_url);

        // Add specific selector for digital storage capacity variants
        $storage_selectors = [
            '#inline-twister-row-digital_storage_capacity li[data-asin]',
            '.inline-twister-swatch[data-asin]',
            '.swatch-list-item-text[data-asin]'
        ];

        foreach ($storage_selectors as $selector) {
            $storage_variants = $html->find($selector);
            foreach ($storage_variants as $variant) {
                $asin = $variant->getAttribute('data-asin');
                if ($asin && strlen($asin) == 10 && in_array($asin, $all_skus) && $asin !== $parentSku) {
                    $variation_ids[] = $asin;
                }
            }
        }

        // Original variation selectors
        $variation_selectors = [
            '#variation_size_name ul li', 
            '#variation_color_name ul li',
            '#twister_feature_div #twisterContainer form div ul li',
            '#twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister .inline-twister-row ul li',
            '#softlinesTwister_feature_div #twisterContainer form div ul li',
            '.shelf-item', '.a-carousel-card', '.twisterSlot',
            'select[name^="dropdown_selected_"] option',
            'input[type="hidden"][name="ASIN"]', 
            'input[type="hidden"][name="asin"]'
        ];

        foreach ($variation_selectors as $selector) {
            $variations = $html->find($selector);
            foreach ($variations as $variation) {
                $attributes_to_check = ['data-defaultasin', 'data-asin', 'data-csa-c-item-id', 'data-asin-id', 'value'];
                foreach ($attributes_to_check as $attr) {
                    $sid = trim($variation->getAttribute($attr));
                    if ($sid && strlen($sid) == 10 && in_array($sid, $all_skus) && $sid !== $parentSku) {
                        $variation_ids[] = $sid;
                        break;
                    }
                }
                // Check data-dp-url attribute
                $dp_url = $variation->getAttribute('data-dp-url');
                if ($dp_url && preg_match('/\/dp\/([A-Z0-9]{10})/', $dp_url, $matches)) {
                    $sid = $matches[1];
                    if (in_array($sid, $all_skus) && $sid !== $parentSku) {
                        $variation_ids[] = $sid;
                    }
                }
            }
        }

        // Process JavaScript data for variations
        $scripts = $html->find('script');
        foreach ($scripts as $script) {
            $scriptContent = $script->innertext;
            
            // Extract ASINs from asinVariationValues
            if (preg_match('/var\s+asinVariationValues\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $asinVariationValues = json_decode($matches[1], true);
                if ($asinVariationValues) {
                    foreach (array_keys($asinVariationValues) as $asin) {
                        if (in_array($asin, $all_skus) && $asin !== $parentSku) {
                            $variation_ids[] = $asin;
                        }
                    }
                }
            }
            
            // Extract ASINs from dimensionValuesDisplayData
            if (preg_match('/var\s+dimensionValuesDisplayData\s*=\s*({.*?});/s', $scriptContent, $matches)) {
                $dimensionData = json_decode($matches[1], true);
                if ($dimensionData) {
                    foreach ($dimensionData as $dimension) {
                        if (isset($dimension['dimensionValues'])) {
                            foreach ($dimension['dimensionValues'] as $value) {
                                if (isset($value['asin']) && in_array($value['asin'], $all_skus) && $value['asin'] !== $parentSku) {
                                    $variation_ids[] = $value['asin'];
                                }
                            }
                        }
                    }
                }
            }

            // Extract ASINs from other possible JSON structures
            if (preg_match_all('/"asin"\s*:\s*"([A-Z0-9]{10})"/', $scriptContent, $matches)) {
                foreach ($matches[1] as $asin) {
                    if (in_array($asin, $all_skus) && $asin !== $parentSku) {
                        $variation_ids[] = $asin;
                    }
                }
            }
        }

        // Remove duplicates and sort
        $variation_ids = array_unique($variation_ids);
        sort($variation_ids);

        return array_values($variation_ids);
    }

    //wf
    public function getSkuFromUrl($url) {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null) {
            return null; // Return null if URL is malformed
        }
        $segments = explode("/dp/", $path);
        if (count($segments) < 2) {
            return null; // Return null if URL does not contain "/dp/"
        }
        $asinSegment = end($segments);
        $asinParts = explode('/', $asinSegment);
        
        return $asinParts[0] ?? null; // Return the ASIN or null if not found
    }

    public function getParentSkuFromHtml($html) {

        if (!$html || !is_object($html)) {
            error_log("getParentSkuFromHtml: Invalid HTML input");
            return null;
        }
        
        // Try to find parentAsin in multiple possible locations
        $searchElements = ['#centerCol', '#dp-container', 'body'];
        
        foreach ($searchElements as $elementSelector) {
            $element = $html->find($elementSelector, 0);
            if ($element) {
                $regex = '/"parentAsin"\s*:\s*"([A-Z0-9]{10})"/i';
                if (preg_match($regex, $element->outertext, $matches)) {
                    $asin = trim($matches[1]);
                    if (!empty($asin)) {
                        return $asin;
                    }
                }
                
                // If not found with first regex, try an alternative
                $altRegex = "/\'parentAsin\'\s*:\s*\'([A-Z0-9]{10})\'/i";
                if (preg_match($altRegex, $element->outertext, $matches)) {
                    $asin = trim($matches[1]);
                    if (!empty($asin)) {
                        return $asin;
                    }
                }
            }
        }
        
        // If we've searched all elements and haven't found a match, try the entire HTML
        $regex = '/"parentAsin"\s*:\s*"([A-Z0-9]{10})"/i';
        if (preg_match($regex, $html->outertext, $matches)) {
            $asin = trim($matches[1]);
            if (!empty($asin)) {
                return $asin;
            }
        }
        
        error_log("getParentSkuFromHtml: No parentAsin found in HTML"); exit;
        return null;
    }

    private function mapAmazonAttributeToWooCommerce($amazon_attribute) {
        $map = [
            'size' => 'pa_size',
            'colour' => 'pa_colour',
            'color' => 'pa_colour',
            'style' => 'pa_style',
            'material' => 'pa_material',
            'fit_type' => 'pa_fit_type',
            'fit_type_options' => 'pa_fit_type',
        ];

        $amazon_attribute = strtolower($amazon_attribute);
        return isset($map[$amazon_attribute]) ? $map[$amazon_attribute] : 'pa_' . $amazon_attribute;
    }

    private function sanitizeAttributeValue($value) {
        if (is_array($value)) {
            return implode('|', array_map(function($v) {
                return $this->sanitizeAttributeValue($v);
            }, $value));
        }
        
        $value = html_entity_decode($value);
        $value = strtolower(str_replace(' ', '-', $value));
        $value = preg_replace('/[^a-z0-9-]/', '', $value);
        return $value;
    }

    public function getAttributeCount($html) {
        $attributes = $html->find('#twister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister .inline-twister-row');
        if( $attributes ) {
            return count($attributes);
        }

        $attributes = $html->find('#softlinesTwister_feature_div #twister-plus-inline-twister-container #twister-plus-inline-twister-card #twister-plus-inline-twister .inline-twister-row');
        if( $attributes ) {
            return count($attributes);
        }
        return FALSE;
    }

    public function importMultiLevelVariation($product_id, $product_meta_data) {
        $product = wc_get_product($product_id);

        // The attribute data
        $attributes_data = $product_meta_data['attributes_data'];
        wc_update_product_attributes( $product_id, $attributes_data );

        $variations_default_attributes = [];
        foreach ($product_meta_data['default_attributes'] as $attribute => $value) {
            $taxonomy = 'pa_'.$attribute;
            if( $term = get_term_by( 'name', $value, $taxonomy ) ) {
                $variations_default_attributes[$taxonomy] = $term->slug;
            }
        }

        // Save the variation default attributes
        if( !empty($variations_default_attributes) ) {
            update_post_meta( $product_id, '_default_attributes', $variations_default_attributes );
        }
    }

    public function get_parsed_url($url, $part = 'PHP_URL_FULL') {
        $parse = parse_url($url);
        
        switch ($part) {
            case 'PHP_URL_FULL':
                $formatted = $parse['scheme'] . '://' . $parse['host'];
                if (isset($parse['path'])) {
                    $formatted .= $parse['path'];
                }
                if (isset($parse['query'])) {
                    $formatted .= '?' . $parse['query'];
                }
                break;
            case 'PHP_URL_BASE':
                $formatted = $parse['scheme'] . '://' . $parse['host'];
                break;
            default:
                $formatted = '';
        }
        
        // Check for empty string then return false
        if ($formatted == '') return FALSE;
        return $formatted;
    }

    public function fetchReviewFromHtml($html) {
		$reviewData = [];
		$reviewList = $html->find('#cm_cr-review_list [data-hook="review"]');

		foreach ($reviewList as $review) {
			$extract = explode('ASIN=', $review->find('[data-hook="review-title"]',0)->getAttribute('href'));

            // Get ratings
			$rating = 5;
			$star_classes = ['a-star-1','a-star-2','a-star-3','a-star-4','a-star-5'];
			$review_star_rating = $review->find('i.review-rating',0)->getAttribute('class');
			preg_match('/a-star-\d/', $review_star_rating, $match);
			$reviews = isset($match[0]) ? $match[0] : '';
			if( false !== $key = array_search($reviews, $star_classes) ) { $rating = $key + 1; }
			
			$reviewData[] = [
				"id" => $review->getAttribute('id'),
				"asin" => end($extract),
				"user" => $review->find('.a-profile-name',0)->text(),
				"title" => $review->find('[data-hook="review-title"]',0)->text(),
				"content" => $review->find('[data-hook="review-body"]',0)->text(),
				"rating" => $rating
			];
		}

        //echo '<pre>'; dd( $reviewData ); echo '</pre>'; exit;
		return $reviewData;
	}
    
    //search without-api
    function fetchSearchPriceFromHtml($html) {
        $amount = $regular_price = $sale_price = 0;
        $currency = '';
        
        $price = $html->find('.a-offscreen');
        $priceWhole = $html->find('.a-price-whole');
        $priceFraction = $html->find('.a-price-fraction');
        $aSizeBaseColorPrice = $html->find('.a-size-base.a-color-price.a-color-price');
        $currencySymbol = $html->find('.a-price-symbol');

        $delPrice = $html->find('.a-text-price .a-offscreen', 0);

        // Find sale price
        if (isset($price[0])) {
            $priceText = $price[0]->innertext;
            $currency = preg_replace('/[0-9.,\s]/', '', $priceText);
            $sale_price = preg_replace('/[^0-9.]/', '', $priceText);
        }

        // Find regular price (usually crossed out)
        if ($delPrice) {
            $delPriceText = $delPrice->innertext;
            $currency = preg_replace('/[0-9.,\s]/', '', $delPriceText);
            $regular_price = preg_replace('/[^0-9.]/', '', $delPriceText);
        }

        // If no sale price found, use the regular price
        if ($sale_price == 0 && $regular_price != 0) {
            $sale_price = $regular_price;
        }

        // If no regular price found, use the sale price
        if ($regular_price == 0 && $sale_price != 0) {
            $regular_price = $sale_price;
        }

        $sale_price = $this->parsePrice($sale_price);
        $regular_price = $this->parsePrice($regular_price);
        
        return [
            'sale_price' => $sale_price,
            'regular_price' => $regular_price,
            'currency' => $currency,
            'formatted_sale_price' => $currency . $sale_price,
            'formatted_regular_price' => $currency . $regular_price,
        ];
    }

    function get_search_results($data) {
        $base_url = no_api_active_country_url();
        $ams_keyword = $data['k'] ?? '';
        $ams_amazon_cat = $data['i'] ?? '';
        $ams_amazon_page = $data['page'] ?? 1;
        $results_limit = get_option('ams_results_limit', '50');
        $results_limit = $results_limit ? intval($results_limit) : 50;
        
        $lists = array();
        $load_more = false;
        $max_pages = 10;
        $total_results = 0;  // Added counter for total results

        for ($current_page = $ams_amazon_page; $current_page <= $max_pages; $current_page++) {
            $url = $base_url . '/s?' . http_build_query([
                'k' => $ams_keyword,
                'i' => $ams_amazon_cat,
                'page' => $current_page
            ]);
            $response_body = $this->getContentUsingCurl($url);

            if (empty($response_body)) {
                break;
            }
            if (!class_exists('simple_html_dom')) {
                require_once AMS_PLUGIN_PATH . '/includes/Admin/lib/simplehtmldom/simple_html_dom.php';
            }
            $html = new \simple_html_dom();
            $html->load($response_body);

            $search_result = $html->find('.s-result-list', 0);
            if (!$search_result) {
                $content = executeScrapingService($url);
                if ($content === false) {
                    break;
                }
                $html->load($content);
                $search_result = $html->find('.s-result-list', 0);
                if (!$search_result) {
                    break;
                }
            }
            
            $search_items = $search_result->find('.s-result-item');
            $total_results += count($search_items); // Accumulate total results

            foreach ($search_items as $search_item) {
                $data_asin = $search_item->getAttribute('data-asin') ?? '';
                
                if ($data_asin) {
                    $title_element = $search_item->find('h2', 0);
                    if (!$title_element) {
                        continue;
                    }
                    
                    $anchor = $search_item->find('.a-link-normal', 0);
                    $detailPageURL = $anchor ? $base_url . $anchor->getAttribute('href') : '';
                    if (strpos($detailPageURL, "/dp/") === false) {
                        continue;
                    }

                    $price = $this->fetchPriceFromHtml($search_item);
                    $regular_price = isset($price['regular_price']) ? $price['regular_price'] : 0;
                    $sale_price = isset($price['sale_price']) ? $price['sale_price'] : 0;
                    logImportVerification('Regular price: ', $regular_price);
                    logImportVerification('Sale price: ', $sale_price);

                    // Currency
                    $currency = $this->fetchCurrencyFromHtml($search_item);
                    logImportVerification('Currency: ', $currency);
                    
                    $review_stars = $search_item->find('.a-icon-star-small', 0);
                    $review_count = $search_item->find('.a-size-base', 0);
                    
                    $rating = 0;
                    $review_text = '';
                    $review_count_text = '';
                    
                    if ($review_stars) {
                        $rating_text = $review_stars->find('.a-icon-alt', 0)->innertext ?? '';
                        $rating = $this->extract_rating_from_text($rating_text);
                        $review_text = $rating_text;
                    }
                    
                    if ($review_count) {
                        $review_count_text = $review_count->plaintext;
                    }
                    
                    $product_data = [
                        'ASIN'          => $data_asin,
                        'Title'         => $title_element->plaintext,
                        'Price'         => $currency . ($sale_price ?: 0),
                        'SavingBasis'   => $currency . ($regular_price ?: 0),
                        'ImageUrl'      => $search_item->find('img', 0)->getAttribute('src') ?? '',
                        'DetailPageURL' => $detailPageURL,
                        'Rating'        => $rating,
                        'ReviewText'    => $review_text,
                        'ReviewCount'   => $review_count_text,
                        'Debug'         => [
                            'RawReviewStars' => $review_stars ? $review_stars->outertext : 'Not found',
                            'RawReviewCount' => $review_count ? $review_count->outertext : 'Not found',
                        ]
                    ];
                    
                    $lists[] = $product_data;
                }
                
                if (count($lists) >= $results_limit) {
                    $load_more = true;
                    break 2;
                }
            }
        }

        return [
            'data' => array_slice($lists, 0, $results_limit),
            'load_more' => $load_more,
            'total_results' => $total_results  // Added to return value
        ];
    }
    //search without-api


    function extract_rating_from_text($text) {
        if (preg_match('/(\d+(\.\d+)?) out of 5 stars/', $text, $matches)) {
            return floatval($matches[1]);
        }
        return 0;
    }

    public function getContentUsingCurl($url, $user_agent = false) {
        $max_attempts = 3;
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            $attempt++;
            //logImportVerification("Attempt $attempt of $max_attempts", null);
            sleep(rand(2, 3));
            
            $rand_user_agent = $this->user_agent();
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 120,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_USERAGENT => $user_agent ? $user_agent : $rand_user_agent,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => ""
            );
            
            $ch = curl_init($url);
            curl_setopt_array($ch, $options);
            $contents = curl_exec($ch);
            $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                $contents = '';
            } else {
                curl_close($ch);
            }
            
            $use_scraping_service = 
                $response_code != 200 || 
                !is_string($contents) || 
                strlen($contents) == 0 || 
                stripos($contents, 'captcha') !== false ||
                stripos($contents, 'Page Not Found') !== false ||
                stripos($contents, 'Dogs of Amazon') !== false ||
                !preg_match('/<title>(.*?)<\/title>/i', $contents) ||
                empty(trim(preg_replace('/<title>(.*?)<\/title>/i', '$1', $contents)));
            
            if (!$use_scraping_service) {
                return $contents;
            }
            
            // Use scraping service if any of the above conditions are true
            logImportVerification('Using scraping service', null);
            $scraping_response = executeScrapingService($url);
            //echo '<pre>'; dd( $scraping_response ); echo '</pre>';
            
            if ($scraping_response === false) {
                logImportVerification('Scraping service failed', null);
                if ($attempt == $max_attempts) {
                    return false;
                }
                continue; // Try again
            }
            
            $contents = is_array($scraping_response) ? $scraping_response['data'] : $scraping_response;
            
            if (is_string($contents) && strlen($contents)) {
                logImportVerification('Scraping service used successfully', null);
                return $contents;
            }
            
            logImportVerification('Failed to retrieve content even with scraping service', null);
            
            if ($attempt == $max_attempts) {
                return false;
            }
            // If we reach here, we'll try again
        }
        
        return false;
    }

    public function getRandomUserAgent() {
        $userAgents=array(
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.167 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.99 Safari/533.4",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.75 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36",
        "Mozilla/5.0 (Linux; Android 9; SM-G960F Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/74.0.3729.157 Mobile Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36",
        "BrightSign/8.0.69 (XT1143)Mozilla/5.0 (X11; Linux armv7l) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.11.2 Chrome/65.0.3325.230 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36 MVisionPlayer/1.0.0.0",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/5.9.4 Chrome/56.0.2924.122 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.117 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
        "Mozilla/5.0 (Linux; Android 6.0.1; SM-G532G Build/MMB29T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.83 Mobile Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
        "Mozilla/5.0 (Linux; Android 9; SM-G950F Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/74.0.3729.157 Mobile Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107 Safari/537.36",
        "Mozilla/5.0 (en-us) AppleWebKit/534.14 (KHTML, like Gecko; Google Wireless Transcoder) Chrome/9.0.597 Safari/534.14"
        );
        
        $random = rand(0,count($userAgents)-1);        
        return $userAgents[$random];
    }
}