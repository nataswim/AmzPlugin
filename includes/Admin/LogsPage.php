<?php
namespace Amazon\Affiliate\Admin;

class LogsPage {
    private $log_file;

    public function __construct() {
        $this->log_file = plugin_dir_path(dirname(__FILE__)) . 'import_verification.log';
    }

    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Handle clear logs action
        if (isset($_POST['action']) && $_POST['action'] == 'clear_logs') {
            $this->handle_clear_logs_request();
        }

        // Handle export logs action
        if (isset($_POST['action']) && $_POST['action'] == 'export_logs') {
            $this->export_logs();
        }

        $log_contents = $this->get_log_contents();
        include dirname(__FILE__) . '/views/view-logs.php';
    }

    public function get_log_contents() {
        if (!file_exists($this->log_file)) {
            return '';
        }
        
        return file_get_contents($this->log_file);
    }

    public function clear_logs() {
        if (file_exists($this->log_file)) {
            $result = file_put_contents($this->log_file, '');
            if ($result !== false) {
                return true;
            }
        }
        return false;
    }

    private function handle_clear_logs_request() {
        if (!isset($_POST['clear_logs_nonce']) || !wp_verify_nonce($_POST['clear_logs_nonce'], 'clear_logs_nonce')) {
            wp_die(__('Security check failed.'));
        }
        $result = $this->clear_logs();
        if ($result) {
            $this->logs_cleared_notice();
        } else {
            $this->logs_clear_failed_notice();
        }
        // Redirect to prevent form resubmission
        wp_safe_redirect(add_query_arg('page', 'view-logs', admin_url('admin.php')));
        exit;
    }

    public function logs_cleared_notice() {
        add_settings_error('ams_logs', 'logs_cleared', __('Logs have been cleared successfully.', 'ams-wc-amazon'), 'success');
    }

    public function logs_clear_failed_notice() {
        add_settings_error('ams_logs', 'logs_clear_failed', __('Failed to clear logs. Please check file permissions.', 'ams-wc-amazon'), 'error');
    }

    public function logs_file_not_found_notice() {
        add_settings_error('ams_logs', 'logs_file_not_found', __('Log file not found. It may have been already deleted.', 'ams-wc-amazon'), 'warning');
    }

    private function export_logs() {
        if (!isset($_POST['export_logs_nonce']) || !wp_verify_nonce($_POST['export_logs_nonce'], 'export_logs_nonce')) {
            wp_die(__('Security check failed.'));
        }

        if (!file_exists($this->log_file)) {
            wp_die(__('Log file not found.', 'ams-wc-amazon'));
        }

        $log_contents = file_get_contents($this->log_file);

        if ($log_contents === false) {
            wp_die(__('Failed to read log file.', 'ams-wc-amazon'));
        }

        $filename = 'import_verification_logs_' . date('Y-m-d_H-i-s') . '.txt';

        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for file download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($log_contents));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output file contents
        echo $log_contents;
        exit;
    }

    public function paginate_logs($log_contents, $page, $per_page) {
        $lines = explode("\n", $log_contents);
        $offset = ($page - 1) * $per_page;
        return implode("\n", array_slice($lines, $offset, $per_page));
    }

    public function filter_logs($log_contents, $filter) {
        if (empty($filter)) return $log_contents;
        $lines = explode("\n", $log_contents);
        $filtered = array_filter($lines, function($line) use ($filter) {
            return stripos($line, $filter) !== false;
        });
        return implode("\n", $filtered);
    }
}