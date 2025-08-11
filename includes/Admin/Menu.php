<?php
namespace Amazon\Affiliate\Admin;

/**
 * Plugin admin menu handler Class
 *
 * @package Amazon\Affiliate\Admin
 */
class Menu {
    private $setting;
    private $dashboard;
    private $Productsearch;
    private $product_review_import;
    private $product_import_by_url;
    private $products_search_without_api;
    private $logs_page; // New property for logs page

    /**
     * Menu constructor.
     *
     * @param $setting
     * @param $Productsearch
     * @param $dashboard
     * @param $products_search_without_api
     * @param $product_import_by_url
     * @param $product_review_import
     * @param $logs_page
     */
    function __construct($setting, $Productsearch, $dashboard, $products_search_without_api, $product_import_by_url, $product_review_import, $logs_page) {
        $this->setting = $setting;
        $this->dashboard = $dashboard;
        $this->Productsearch = $Productsearch;
        $this->products_search_without_api = $products_search_without_api;
        $this->product_import_by_url = $product_import_by_url;
        $this->product_review_import = $product_review_import;
        $this->logs_page = $logs_page; // Initialize logs page

        add_action('admin_menu', array($this, 'admin_menu'));
        add_filter('plugin_action_links_affiliate-management-system-woocommerce-amazon/affiliate-management-system-woocommerce-amazon.php', array($this, 'plugin_setting_link'));
    }

    /**
     * Admin Menu register
     */
    public function admin_menu() {
        $capability = 'manage_options';
        $title = esc_html__('Ams Amazon', 'ams-wc-amazon');
        $setting_page_title = esc_html__('Setting', 'ams-wc-amazon');
        $parent_slug = 'wc-amazon-affiliate';
        add_menu_page($title, $title, $capability, $parent_slug, array($this->dashboard, 'dashboard_page'), 'dashicons-amazon');
        $parent_slug = 'wc-amazon-affiliate-slug';

        add_submenu_page($parent_slug, esc_html__('Dashboard', 'ams-wc-amazon'), esc_html__('Dashboard', 'ams-wc-amazon'), $capability, $parent_slug, array($this->dashboard, 'dashboard_page'));
        add_submenu_page($parent_slug, esc_html__('Amazon API Import', 'ams-wc-amazon'), esc_html__('Amazon API Import', 'ams-wc-amazon'), $capability, 'wc-product-search', array($this->Productsearch, 'product_page'));
        add_submenu_page($parent_slug, esc_html__('Amazon Product Import - Without API - By Search [All Countries]', 'ams-wc-amazon'), esc_html__('Amazon Product Import - Without API - By Search [All Countries]', 'ams-wc-amazon'), $capability, 'products-search-without-api', array($this->products_search_without_api, 'products_page'));
        add_submenu_page($parent_slug, esc_html__('Without API - By URL [All Countries]', 'ams-wc-amazon'), esc_html__('Without API - By URL [All Countries]', 'ams-wc-amazon'), $capability, 'product-import-by-url', array($this->product_import_by_url, 'product_import_page'));
        add_submenu_page($parent_slug, esc_html__('Products Review', 'ams-wc-amazon'), esc_html__('Products Review', 'ams-wc-amazon'), $capability, 'product-review-import', array($this->product_review_import, 'product_review_page'));
        add_submenu_page($parent_slug, $setting_page_title, $setting_page_title, $capability, 'wc-product-setting-page', array($this->setting, 'setting_page'));
        
        // Add new submenu for logs
        add_submenu_page($parent_slug, esc_html__('View Logs', 'ams-wc-amazon'), esc_html__('View Logs', 'ams-wc-amazon'), $capability, 'view-logs', array($this->logs_page, 'render_logs_page'));
    }

    /**
     * Plugin setting page link
     *
     * @param $link
     * @return mixed
     */
    public function plugin_setting_link($link) {
        $new_link = sprintf("<a href='%s'>%s</a>", "admin.php?page=wc-product-setting-page", esc_html__("Setting", "woo-address-auto-complete"));
        $link[] = $new_link;

        return $link;
    }
}