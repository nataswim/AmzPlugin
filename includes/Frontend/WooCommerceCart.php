<?php
namespace Amazon\Affiliate\Frontend;
/**
 * Class WooCommerceCart
 *
 * @package Amazon\Affiliate\Frontend
 */
class WooCommerceCart {

    public function __construct() {
        $this->setup_hooks();
    }

    private function setup_hooks() {
        $use_custom_button = get_option('ams_use_custom_button', '0') === '1';
        $theme_hook = get_option('ams_theme_hook', 'woocommerce_after_shop_loop_item');
        
        // error_log('AMS Debug - Theme Hook: ' . $theme_hook);
        // error_log('AMS Debug - Use Custom Button: ' . ($use_custom_button ? 'true' : 'false'));
        
        add_action('woocommerce_product_meta_start', [$this, 'buy_now_button_actions']);
        add_action('wp_footer', [$this, 'buy_now_button_actions'], 99);
        
        if ($use_custom_button && !empty($theme_hook)) {
            add_action($theme_hook, function() use ($theme_hook) {
                global $product;
                if (!$product) {
                    //error_log('AMS Warning - Product object not found');
                    return;
                }
                remove_action($theme_hook, 'woocommerce_template_loop_add_to_cart', 10);
                
                $button = $this->buy_now_button_actions_category(
                    '<a href="' . $product->add_to_cart_url() . '" class="button add_to_cart_button">Add to Cart</a>',
                    $product
                );
                //error_log('AMS Debug - Button generated for product ID: ' . $product->get_id());
                echo $button;
            }, 10);
            //error_log('AMS Debug - Dynamic hook action added: ' . $theme_hook);
        }
        
        add_filter('woocommerce_loop_add_to_cart_link', [$this, 'buy_now_button_actions_category'], 10, 2);
        add_action('woocommerce_before_single_product', [$this, 'visitor_record']);
        add_action('woocommerce_checkout_init', [$this, 'woocommerce_external_checkout'], 10);
        add_filter('woocommerce_product_additional_information', [$this, 'ams_product_additional_information'], 10, 2);
        add_action('admin_post_cart_redirected_count', 'my_handle_form_submit');
        add_action('admin_post_nopriv_cart_redirected_count', 'my_handle_form_submit');
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('cart_custom_js', AMS_PLUGIN_URL . 'assets/js/components/custom/cart.js', [], null, true);
    }

    // This function is for product information of front side
    function ams_product_additional_information( $product ) {
        if( $product->is_type('variable') ) {
            echo '<div id="ams-additional-information"></div>';
        } else {
            $ams_additional_information = get_post_meta( $product->get_id(), '_ams_additional_information', true );
            echo '<div id="ams-additional-information">'. $ams_additional_information .'</div>';
        }
    }

    // This function is for option 1+2+3+4 - single page
    public function buy_now_button_actions() {
        global $product;

        // Ensure $product is valid
        if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
            return;
        }

        $product_id = $product->get_id();
        if (!$product_id || !get_post_meta($product_id, '_ams_product_url', true)) {
            return;
        }

        $btn_text = get_option('ams_buy_now_label', 'Buy Now');
        $ams_associate_tag = get_option('ams_associate_tag');
        $asin_id = get_post_meta($product_id, '_wca_amazon_affiliate_asin', true);
        $ams_amazon_country = get_option('ams_amazon_country', 'com');
        $enable_no_follow = get_option('ams_enable_no_follow_link');
        $buy_action = get_option('ams_buy_action_btn');
        $ams_product_url = get_post_meta($product_id, '_ams_product_url', true);

        if (strtolower($ams_amazon_country) === 'mx') {
            $ams_amazon_country = 'com.mx';
        }

        add_filter('woocommerce_product_single_add_to_cart_text', function($text) use ($btn_text, $buy_action) {
            return ($buy_action === 'multi_cart' || $buy_action === 'dropship') ? __('Add to Cart', 'woocommerce') : (!empty($btn_text) ? $btn_text : __('Buy Now', 'woocommerce'));
        });

        add_action('wp_footer', function() use ($btn_text, $buy_action, $ams_product_url, $ams_associate_tag, $product_id) {
            if (!empty($btn_text)) {
                echo '<script type="text/javascript">
                        jQuery(document).ready(function($) {
                            var btn = $("button.single_add_to_cart_button");
                            if (btn.length) {
                                btn.text("' . esc_js($btn_text) . '");

                                if ("' . esc_js($buy_action) . '" === "redirect") {
                                    var redirect_url = "' . esc_js($ams_product_url . '?tag=' . $ams_associate_tag) . '";
                                    btn.off("click").on("click", function(e) {
                                        e.preventDefault();
                                        window.open(redirect_url, "_blank");
                                    });
                                }
                            }
                        });
                      </script>';
            }
        }, 999);
    }

    // Helper function to generate Amazon cart URL for Option 2
    private function get_amazon_cart_url($product) {
        $ams_associate_tag = get_option('ams_associate_tag');
        $asin_id = get_post_meta($product->get_id(), '_wca_amazon_affiliate_asin', true);
        $ams_amazon_country = get_option('ams_amazon_country', 'com');

        if ("mx" == strtolower($ams_amazon_country)) {
            $ams_amazon_country = 'com.mx';
        }

        $amazon_cart_url = 'https://www.amazon.' . $ams_amazon_country . '/gp/aws/cart/add.html';
        $args = [
            'AssociateTag' => $ams_associate_tag,
            'ASIN.1'       => $asin_id,
            'Quantity.1'   => 1
        ];

        return $amazon_cart_url . '?' . http_build_query($args);
    }

    // This is the redirect process code - option 3
    public function woocommerce_external_checkout() {
        // Check if we're on the checkout page
        if (!is_page('checkout') && !is_checkout()) {
            return;
        }
        // Check if dropship option is enabled
        if (strtolower(get_option('ams_buy_action_btn')) === strtolower('dropship')) {
            return;
        }
        $ams_access_key_id = get_option('ams_access_key_id');
        $ams_associate_tag = get_option('ams_associate_tag');
        $ams_amazon_country = get_option('ams_amazon_country');
        $url = 'https://www.amazon.' . $ams_amazon_country . '/gp/aws/cart/add.html?';
        
        $arg = array(
            'AWSAccessKeyId' => $ams_access_key_id,
            'AssociateTag'   => $ams_associate_tag,
        );
        $count = 1;
        $stay_to_checkout = false;
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $product = wc_get_product($product_id);
            if ($cart_item['variation_id'] > 0 && in_array($cart_item['variation_id'], $product->get_children())) {
                $asin_id = get_post_meta($cart_item['variation_id'], '_sku', true);
            } else {
                $asin_id = $product->get_sku();
            }
            $arg['ASIN.' . $count] = $asin_id;
            $arg['Quantity.' . $count] = $quantity;
            $this->total_count_products_add_to_cart($product_id);
            if (!get_post_meta($product_id, '_ams_product_url', true)) {
                $stay_to_checkout = true;
            } else {
                WC()->cart->remove_cart_item($cart_item_key);
            }
            $count++;
        }
        if ($stay_to_checkout) {
            return;
        }
        $arg = http_build_query($arg);
        $add_to_cart = $url . $arg;
        // Check if redirection delay is enabled
        if (get_option('ams_checkout_redirected_seconds', true)) {
            // Use our custom redirect page
            $redirect_url = add_query_arg(
                array(
                    'ams_redirect' => '1',
                    'ams_redirect_uri' => urlencode($add_to_cart)
                ),
                home_url()
            );
            wp_safe_redirect($redirect_url);
        } else {
            // Direct redirect to Amazon
            wp_redirect(esc_url_raw($add_to_cart));
        }
        exit();
    }

    public function buy_now_button_actions_category($button, $product) {
        //error_log('buy_now_button_actions_category triggered.');
        // Retrieve settings and metadata
        $product_id         = $product->get_id();
        $btn_text           = get_option('ams_buy_now_label', 'Buy Now');
        $ams_associate_tag  = get_option('ams_associate_tag');
        $ams_amazon_country = get_option('ams_amazon_country', 'com');
        $enable_no_follow   = get_option('ams_enable_no_follow_link') ? 'nofollow' : '';
        $buy_action         = get_option('ams_buy_action_btn');
        $ams_product_url    = get_post_meta($product_id, '_ams_product_url', true);

        // Adjust Amazon country for 'mx'
        if ("mx" === strtolower($ams_amazon_country)) {
            $ams_amazon_country = 'com.mx';
        }

        // Handle each option for `buy_action`
        if ($buy_action === 'redirect' && !empty($ams_product_url)) {
            // Option 1: Direct Amazon Redirect
            $query = parse_url($ams_product_url, PHP_URL_QUERY);
            $product_associate_tag = (!empty($query) ? '&' : '?') . 'tag=' . $ams_associate_tag;
            $redirect_url = $ams_product_url . $product_associate_tag;

            return sprintf(
                '<a href="%s" rel="%s" class="%s" target="_blank">%s</a>',
                esc_url($redirect_url),
                esc_attr($enable_no_follow),
                esc_attr($this->get_button_classes($button)), // Use default theme classes
                esc_html($btn_text)
            );
        } elseif ($buy_action === 'cart_page') {
            // Option 2: Amazon Cart Page Redirect
            $final_url = $this->get_amazon_cart_url($product);

            return sprintf(
                '<a href="#" rel="%s" class="%s" id="redirect_amazon_cart_%s">%s</a>
                 <script type="text/javascript">
                     jQuery(document).ready(function($) {
                         $("#redirect_amazon_cart_' . esc_js($product_id) . '").on("click", function(e) {
                             e.preventDefault();
                             window.location.href = "' . esc_js($final_url) . '";
                         });
                     });
                 </script>',
                esc_attr($enable_no_follow),
                esc_attr($this->get_button_classes($button)), // Use default theme classes
                esc_attr($product_id),
                esc_html($btn_text)
            );
        } elseif ($buy_action === 'multi_cart' || $buy_action === 'dropship') {
            // Option 3 or 4: Multi-Cart or Dropship
            return $button; // Return the default WooCommerce button
        }

        // Default fallback
        return $button;
    }

    private function get_button_classes($button) {
        if (preg_match('/class="([^"]+)"/', $button, $matches)) {
            return $matches[1];
        }
        return 'button'; // Fallback class
    }

    ////// Admin side functions //////
    public function total_count_products_add_to_cart( $product_id ) {
        $count_key     = 'ams_product_added_to_cart';
        $product_count = get_post_meta( $product_id, $count_key, true );
        $product_count++;

        update_post_meta( $product_id, $count_key, $product_count );
    }

    public function visitor_record() {
        global $product;

        // Check if $product is set and is a valid WC_Product object
        if (!isset($product) || !is_a($product, 'WC_Product')) {
            return; // Exit early if not on a product page
        }

        // Safely retrieve the product ID
        $post_id = $product->get_id();

        // Check if $post_id is valid
        if (!$post_id) {
            return; // Exit early if no valid product ID is found
        }

        // Define the meta key and safely get the current count
        $count_key = 'ams_product_views_count';
        $count = get_post_meta($post_id, $count_key, true);

        // Ensure $count is numeric before incrementing
        if (!is_numeric($count)) {
            $count = 0; // Initialize count if meta doesn't exist or is invalid
        }

        // Increment and update the post meta
        $count++;
        update_post_meta($post_id, $count_key, $count);
    }
    ////// Admin side functions //////
}