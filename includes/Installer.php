<?php
namespace Amazon\Affiliate;
/**
 * Plugin Installer handler
 *
 * @package Amazon\Affiliate
 */

class Installer {

    /**
     * Installer constructor.
     */
    public function __construct() {

    }

    /**
     * Initializes class
     *
     * @return void
     */

    public function run() {
        $this->add_version();
        $this->add_general();
    }

    /**
     * Store plugin version
     *
     * @return void
     */

    public function add_version() {
        $installed = get_option('ams_amazon_installed');
        if (!$installed) {
            update_option('ams_amazon_installed', time());
        }
        update_option('ams_wc_version', AMS_PLUGIN_VERSION);
    }

    /**
     * Create general setting
     *
     * @return void
     */

    public function add_general() {
        // Utility function to set defaults only if not already set
        $set_default = function($option_name, $default_value) {
            if (get_option($option_name) === false) {
                update_option($option_name, $default_value);
            }
        };

        // ScraperAPI options
        $set_default('ams_scraper_api_is_active', 1);
        $set_default('ams_scraper_api_on_update', 1);

        // ScrapingAnt options
        $set_default('ams_scrapingant_is_active', 1);
        $set_default('ams_scrapingant_on_update', 1);

        // General settings
        $set_default('ams_product_per_page', 10);
        $set_default('ams_enable_no_follow_link', 'nofollow');
        $set_default('ams_remove_unavailable_products', 'No');
        $set_default('ams_fast_product_importer', 'No');
        $set_default('ams_product_import_status', 'publish');
        $set_default('ams_buy_action_btn', 'redirect');
        $set_default('ams_product_thumbnail_size', 'hd');
        $set_default('ams_checkout_mass_redirected', 'You will be redirected to complete your checkout!');
        $set_default('ams_checkout_redirected_seconds', 3);
        $set_default('ams_percentage_profit', 10);
        $set_default('ams_buy_now_label', 'Buy On Amazon');
        $set_default('single_import_review_limit', '10');
        $set_default('multiple_import_review_limit', '10');
        $set_default('ams_variation_image_limit', '5');
        $set_default('ams_variation_limit', '5');
        $set_default('ams_category_min_depth', '1');
        $set_default('ams_category_max_depth', '3');

        // Custom Theme Hook settings
        $set_default('ams_use_custom_button', 0);

        // Reviewer settings
        $set_default('enable_reviewer_image', 1);
        $set_default('enable_review_title', 1);
        $set_default('enable_page_speed_test', 0);

        // Default for products search count
        $set_default('wca_products_search_count', 0);

        // Active Scraping Service
        $set_default('ams_active_scraping_service', 'scraperapi');

        // Cleanup options – default is disabled
        $set_default('enable_clean_completed_actions', '0');
        $set_default('enable_clean_action_logs', '0');
    }
}