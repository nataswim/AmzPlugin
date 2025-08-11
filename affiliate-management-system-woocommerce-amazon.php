<?php
/**
 * Plugin Name:       AMS - WooCommerce Amazon
 * Plugin URI:        https://affiliatepro.org/
 * Description:       Transform your WooCommerce store into a powerful Amazon affiliate platform...
 * Version:           10.1.6
 * Requires at least: 5.6
 * Tested up to:      <?php echo $wp_version; // Current WordPress version ?>
 * Requires PHP:      7.4
 * Author:            AffiliateProSaaS
 * Author URI:        https://affiliatepro.org/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ams-wc-amazon
 * Domain Path:       /languages/
 * WC requires at least: 5.0
 * Update URI:        https://affiliatepro.org/woocommerce-amazon-logs/
 * Network:           true
 * Last Updated:      2025-04-15
 * Downloads:         1000000
 * Tested up to:      6.7.2
 * Slug:              ams-wc-amazon
 * @package           AMS_WC_Amazon
 * @author            AffiliateProSaaS
 * @copyright         2025 AffiliateProSaaS
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

// Check if WooCommerce is active.
if (!function_exists('is_plugin_active')) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}


if (!is_plugin_active('woocommerce/woocommerce.php') ) {
    add_action('admin_notices', 'ams_woocommerce_missing');
}

if (!class_exists('AmsWcAmazon')) {
    /**
     * The main plugin class
     */

    final class AmsWcAmazon {

        /**
         * AmsWcAmazon constructor.
         */

        private function __construct() {
            $this->define_constants();
            register_activation_hook(__FILE__, array($this, 'activate'));
            register_deactivation_hook(__FILE__, array($this, 'deactivate'));
            add_action('plugins_loaded', array($this, 'init_plugin'));
            add_action('plugins_loaded', array($this, 'plugins_loaded_text_domain'));
            add_action('admin_post_nopriv_cart_redirected_count', array($this, 'cart_redirected_count'));
            add_action('admin_post_cart_redirected_count', array($this, 'cart_redirected_count'));
            add_filter('cron_schedules', array($this, 'every_day_cron'));
        }

        /**
         * Initializes a single instance
         */

        public static function init() {
            static $instance = false;
            if (!$instance) {
                $instance = new self();
            }
            return $instance;
        }

        /**
         * Plugin text domain loaded
         */

        public function plugins_loaded_text_domain() {
            load_plugin_textdomain( 'ams-wc-amazon', false, AMS_PLUGIN_PATH . 'languages/' );
        }

        /**
         * Define plugin path and url constants
         */

        public function define_constants() {
            if (!defined('AMS_PLUGIN_FILE')) {
                define('AMS_PLUGIN_FILE', __FILE__);
            }
            if (!defined('AMS_PLUGIN_PATH')) {
                define('AMS_PLUGIN_PATH', plugin_dir_path(__FILE__));
            }
            if (!defined('AMS_PLUGIN_URL')) {
                define('AMS_PLUGIN_URL', plugin_dir_url(__FILE__));
            }
            if (!defined('AMS_PLUGIN_VERSION')) {
                $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
                define('AMS_PLUGIN_VERSION', $plugin_data['Version']);
            }
            if (!defined('AMS_PLUGIN_NAME')) {
                define('AMS_PLUGIN_NAME', 'AMS - WooCommerce Amazon');
            }
            if (!defined('AMS_BRAND_NO_LOGO')) {
                define('AMS_BRAND_NO_LOGO', AMS_PLUGIN_URL . 'assets/img/brand/no-logo.png');
            }
        }


        /**
         *  Init plugin
         */

        public function init_plugin() {
            new \Amazon\Affiliate\Assets();

            // Always initialize admin and front-end classes
            if (is_admin()) {
                new \Amazon\Affiliate\Admin();
                add_action('admin_footer-plugins.php', 'ams_deactivation_popup_script');
                add_action('woocommerce_product_options_inventory_product_data', 'display_gtin_in_inventory_tab');
                add_filter('plugins_api', 'ams_plugin_information_content', 20, 3);
                add_action('admin_head', 'ams_admin_styles');
            } else {
                new \Amazon\Affiliate\Frontend();
                add_action('init', 'show_page_load_time');
                add_shortcode('ams_display_products', 'ams_display_products_grid_shortcode');
                add_filter('the_content', 'append_custom_notification_to_content');
                add_action('woocommerce_single_product_summary', 'display_last_updated_date', 25);
                add_action('woocommerce_review_before_comment_text', 'custom_review_title', 10);
                add_filter('get_avatar_url', 'custom_reviewer_image_url', 10, 3);
                add_filter('get_avatar_data', 'custom_reviewer_image_data', 10, 2);
                add_action('wp_enqueue_scripts', 'ams_enqueue_image_fit_css');
            }

            //Code for variants gallery custom theme
            // Run the script on init
            add_action('init', 'update_custom_variation_images');
            // Add a filter to handle both attachment IDs and URLs when displaying images
            add_filter('wp_get_attachment_image_src', 'handle_url_based_attachment_images', 10, 4);
            //Code for variants gallery custom theme
            

            // Brands
            add_action('wp_ajax_ams_track_brand_click', 'ams_track_brand_click');
            add_action('wp_ajax_nopriv_ams_track_brand_click', 'ams_track_brand_click');
            add_action('wp_footer', 'ams_add_brand_tracking');
            add_action('init', 'ams_register_product_brand_taxonomy');
            add_action('product_brand_edit_form_fields', 'ams_add_brand_logo_field');
            add_action('product_brand_add_form_fields', 'ams_add_brand_logo_field');
            add_action('edited_product_brand', 'ams_save_brand_logo');
            add_action('create_product_brand', 'ams_save_brand_logo');
            add_action('admin_init', 'ams_register_brand_settings');
            add_action('ams_brand_settings_page', 'ams_show_brand_statistics');
            add_action('wp_ajax_ams_reset_stats', 'ams_reset_stats_handler');
            add_action('wp_head', 'ams_inject_custom_css');
            add_action('admin_menu', 'ams_add_brand_admin_menu');
            add_action('woocommerce_single_product_summary', 'ams_display_product_brand', 25);
            add_shortcode('brand_filter', 'ams_brand_filter_shortcode');
            add_action('woocommerce_before_shop_loop', 'ams_display_brand_filter', 20);
            // Brands

            add_action('admin_enqueue_scripts', 'ams_enqueue_admin_scripts');

            //Backend code for product meta box - admin side
            add_action('add_meta_boxes', 'ams_modify_product_image_meta_boxes', 40);

            // Add URL-based images to the product gallery
            add_action('woocommerce_product_write_panel_tabs', 'ams_add_url_images_to_product_gallery');

            // Handle deletion of URL-based images
            add_action('wp_ajax_woocommerce_remove_images', 'ams_handle_url_image_deletion', 5);

            // Add custom CSS to ensure proper display
            add_action('admin_head', 'ams_add_product_image_styles');

            //Backend code for product meta box - admin side

            add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ams_add_action_links');
            add_filter('plugin_row_meta', 'ams_plugin_row_meta', 10, 4);

            //debug code
            //add_action('woocommerce_after_single_product', 'display_variation_image_debug_info');
            //add_action('woocommerce_after_single_product', 'display_simple_product_debug_info');
            //debug code

            // Initialize shared global variable
            $GLOBALS['amswoofiu'] = $this->amswoofiu();
        }

        /**
         * Init Fiu - Featured Image URL
         */

        function amswoofiu() {
            return \Amazon\Affiliate\Fiu\Fiu::instance();
        }

        /**
         * It's count cart amazon redirect
         */

        public function cart_redirected_count() {
            if (isset($_GET['url'])) {
                $post_id = sanitize_text_field($_GET['id']);
                $url = '';
                
                if ('redirect' === get_option('ams_buy_action_btn')) {
                    $count_key = 'ams_product_direct_redirected';
                    $count = get_post_meta($post_id, $count_key, true);
                    $count = is_numeric($count) ? (int)$count : 0; // Ensure $count is an integer
                    $count++;
                    update_post_meta($post_id, $count_key, $count);
                    $url = $_GET['url'];
                } elseif ('cart_page' === get_option('ams_buy_action_btn')) {
                    $count_key = 'ams_product_added_to_cart';
                    $count = get_post_meta($post_id, $count_key, true);
                    $count = is_numeric($count) ? (int)$count : 0; // Ensure $count is an integer
                    $count++;
                    update_post_meta($post_id, $count_key, $count);
                    $url = urldecode_deep(sanitize_text_field($_GET['url']));
                }
                
                wp_redirect(esc_url_raw($url));
                exit();
            }
        }

        /**
         * Do Stuff Plugin activation
         */

        public function activate() {
        	flush_rewrite_rules();
            $installer = new \Amazon\Affiliate\Installer();
            $installer->run();
        }

        /**
         * Every day cron interval register
         */

        // Define the schedule
        public function every_day_cron($schedules) {
            $schedules['ams_every_day'] = array(
                'interval' => 86400,
                'display'  => esc_html__('Once Daily', 'ams-wc-amazon'),
            );
            return $schedules;
        }

        /**
         * Plugin deactivate
         */

        public function deactivate() {
            flush_rewrite_rules();
            wp_clear_scheduled_hook( 'ams_product_availability' );
        }
    }
}

// Additional functions

function ams_check_version() {
    $url = add_query_arg(
        array(
            'slug' => 'ams-wc-amazon',
            'client_version' => AMS_PLUGIN_VERSION
        ),
        'https://buy.affiliatepro.org/version/check'
    );
    
    $response = wp_remote_get($url);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $update_data = json_decode(wp_remote_retrieve_body($response));
        if (isset($update_data->version) && !empty($update_data->version)) {
            return $update_data->version;
        }
    }
    
    return false;
}

/**
 * Initializes the main plugin
 *
 * @return \AmsWcAmazon
 */

function ams_wc_amazon() {
    return AmsWcAmazon::init();
}

/**
 * Rick off the plugin
 */
ams_wc_amazon();