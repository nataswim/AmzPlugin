<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Trigger the license deactivation AJAX call
$response = wp_remote_post(admin_url('admin-ajax.php'), array(
    'body' => array(
        'action' => 'ams_license_deactivated',
        'nonce' => wp_create_nonce('ams-deactivate-license')
    )
));

// Error handling
if (is_wp_error($response)) {
    error_log(sprintf(__('AMS Plugin uninstall error: %s', 'ams-wc-amazon'), $response->get_error_message()));
} else {
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body, true);
    
    if (!$result || !isset($result['success']) || $result['success'] !== true) {
        error_log(__('AMS Plugin license deactivation failed during uninstall', 'ams-wc-amazon'));
    }
}

// Additional cleanup tasks can be added here