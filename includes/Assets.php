<?php

namespace Amazon\Affiliate;

/**
 * Plugin Assets handler class
 *
 * @package Amazon\Affiliate
 */
class Assets {

    /**
     * Assets constructor.
     */
    function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'backend_register_assets'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'load_form_control'));
    }

    public function load_form_control() {
        wp_enqueue_script(
            'ams-form-control', 
            AMS_PLUGIN_URL . 'assets/js/components/custom/form-control.js', 
            array('jquery'), 
            AMS_PLUGIN_VERSION, 
            true
        );
        
        wp_localize_script('ams-form-control', 'amsFormControl', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce_ams_de_activated' => wp_create_nonce('ams_de_activated')
        ));
    }

    public function backend_register_assets($screen) {
        // Dequeue any conflicting styles or scripts
        wp_dequeue_style('inext-wpc-admin-style');
        wp_dequeue_script('inext-wpc-bootstrap-script');

        // Load jQuery
        wp_enqueue_script('jquery');

        // Load custom backend CSS and JS specifically for your plugin
        wp_register_style('ams-amazon-backend', AMS_PLUGIN_URL . 'assets/css/backend.css', array(), AMS_PLUGIN_VERSION);
        wp_enqueue_style('ams-amazon-backend');

        wp_enqueue_script('ams-amazon-backend', AMS_PLUGIN_URL . 'assets/js/backend.js', array('jquery'), AMS_PLUGIN_VERSION, true);

        // Determine if we are on the main dashboard page
        $ams_dashboard = (strtolower($screen) === 'toplevel_page_wc-amazon-affiliate');

        // Localize script with dynamic data
        wp_localize_script('ams-amazon-backend', 'amsbackend', array(
            'ajax_url'                      => admin_url('admin-ajax.php'),
            'check_nonce'                   => wp_create_nonce('ams_product_import'),
            'ams_test_api'                  => wp_create_nonce('ams_test_api'),
            'nonce_ams_dashboard_info'      => wp_create_nonce('ams_dashboard_info'),
            'nonce_ams_de_activated'        => wp_create_nonce('ams_de_activated'),
            'nonce_ams_without_api'         => wp_create_nonce('ams_without_api'),
            'nonce_product_update_request'  => wp_create_nonce('product_update_request'),
            'ams_product_per_page'          => get_option('item_page'),
            'ams_dashboard'                 => $ams_dashboard,
            'ams_assets'                    => AMS_PLUGIN_URL . 'assets/',
            'ams_t_import'                  => esc_html('Importing', 'ams-wc-amazon'),
            'ams_t_loading'                 => esc_html('Loading...', 'ams-wc-amazon'),
            'ams_t_testing_api'             => esc_html('Testing API... Please wait while we process your request.', 'ams-wc-amazon'),
            'ams_t_deactivated'             => esc_html('Deactivated', 'ams-wc-amazon'),
            'ams_mass_product_importing'    => esc_html__('Product import has started.', 'ams-wc-amazon'),
            'nonce_ams_import_product_url'  => wp_create_nonce('ams_import_product_url'),
            'ams_product_availability'      => wp_create_nonce('ams_product_availability'),
        ));
    }


    public function frontend_register_assets() {
        wp_enqueue_script('jquery');
        
        // Enqueue existing frontend CSS
        wp_register_style('ams-amazon-frontend', AMS_PLUGIN_URL . 'assets/css/frontend.css', false, AMS_PLUGIN_VERSION);
        wp_enqueue_style('ams-amazon-frontend');

        // Enqueue the brand filter CSS
        wp_register_style('ams-amazon-brand-filter', AMS_PLUGIN_URL . 'assets/css/brand-filter.css', false, AMS_PLUGIN_VERSION);
        wp_enqueue_style('ams-amazon-brand-filter');

        // Enqueue media uploader script
        wp_enqueue_media();
        
        // Enqueue the custom JS for the media uploader
        wp_register_script('brand-logo-upload', AMS_PLUGIN_URL . 'assets/js/brand-logo-upload.js', array('jquery'), AMS_PLUGIN_VERSION, true);
        wp_enqueue_script('brand-logo-upload');
    }
}