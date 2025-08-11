<?php
namespace Amazon\Affiliate;

/**
 * Backend handler Class Admin
 *
 * @package Amazon\Affiliate
 */
class Admin {
    public function __construct() {
        $setting = new Admin\Setting();
        $Productsearch = new Admin\Productsearch();
        $ImportProducts = new Admin\ImportProducts();
        $dashboard = new Admin\Dashboard();
        $license_activation = new Admin\LicenseActivation();
        $products_search_without_api = new Admin\ProductsSearchWithoutApi();
        $product_import_by_url = new Admin\ProductImportByUrl();
        $product_review_import = new Admin\ProductReviewImport();
        $logs_page = new Admin\LogsPage();

        new Admin\Menu($setting, $Productsearch, $dashboard, $products_search_without_api, $product_import_by_url, $product_review_import, $logs_page);
        $this->dispatch_actions($setting, $Productsearch, $ImportProducts, $dashboard, $license_activation, $products_search_without_api, $product_import_by_url, $product_review_import, $logs_page);
    }

    public function dispatch_actions($setting, $Productsearch, $ImportProducts, $dashboard, $license_activation, $products_search_without_api, $product_import_by_url, $product_review_import, $logs_page) {
        add_action('admin_post_ams_wc_amazon_general_setting', array($setting, 'general_amazon_setting_handler'));
        add_action('admin_post_ams-wc-general-setting', array($setting, 'general_setting'));
        add_action('admin_post_ams_product_cron', array($setting, 'general_ams_product_cron'));
        add_action('admin_post_ams_wc_product_cat_setting', array($setting, 'product_cat_country_setting'));
        add_action('wp_ajax_import_products', array($Productsearch, 'import_products'));
        add_action('wp_ajax_wca_import_process', array($Productsearch, 'wca_import_process'));
        add_action('wp_ajax_search_products', array($Productsearch, 'search_products'));
        add_action('wp_ajax_ams_product_import', array($ImportProducts, 'product_import'));
        add_action('wp_ajax_product_update_request', array($ImportProducts, 'product_update_request'));
        add_action('wp_ajax_ams_dashboard_info', array($dashboard, 'dashboard_info'));
        add_action('wp_ajax_ams_license_activation', array($license_activation, 'license_activation'));
        add_action('wp_ajax_ams_license_deactivated', array($license_activation, 'deactivated_license_plugin'));
        add_action('wp_ajax_ams_test_api', array($setting, 'test_api'));
        add_action('wp_ajax_search_products_without_api', array($products_search_without_api, 'get_product_list'));
        add_action('wp_ajax_product_import_without_api', array($products_search_without_api, 'product_import_without_api'));
        add_action('wp_ajax_ams_product_import_by_url', array($product_import_by_url, 'product_import_by_url'));
        add_action('wp_ajax_ams_product_review_import', array($product_review_import, 'product_review_import'));
    }
}