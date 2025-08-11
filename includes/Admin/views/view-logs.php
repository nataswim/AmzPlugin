<?php
$log_contents = isset($log_contents) ? $log_contents : '';
$current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$logs_per_page = 100; // Number of log entries per page
$total_pages = ceil(substr_count($log_contents, "\n") / $logs_per_page);
$filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : '';
$filtered_logs = $this->filter_logs($log_contents, $filter);
$paginated_logs = $this->paginate_logs($filtered_logs, $current_page, $logs_per_page);
?>

<?php include "common-header.php"; ?>


<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Import Verification Logs', 'ams-wc-amazon'); ?></h1>
    <div class="card mt-4">
        <div class="card-body">
            <form method="get" action="" class="row g-3 align-items-center">
                <input type="hidden" name="page" value="view-logs">
                <div class="col-auto">
                    <input type="text" name="filter" class="form-control" value="<?php echo esc_attr($filter); ?>" placeholder="<?php esc_attr_e('Filter logs...', 'ams-wc-amazon'); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><?php esc_html_e('Apply Filter', 'ams-wc-amazon'); ?></button>
                </div>
            </form>
            
            <?php if (!empty($log_contents)) : ?>
                <div class="mt-3">
                    <form method="post" action="" class="d-inline-block">
                        <?php wp_nonce_field('clear_logs_nonce', 'clear_logs_nonce'); ?>
                        <input type="hidden" name="action" value="clear_logs">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs?', 'ams-wc-amazon'); ?>');">
                            <?php esc_html_e('Clear Logs', 'ams-wc-amazon'); ?>
                        </button>
                    </form>
                    <form method="post" action="" class="d-inline-block ms-2">
                        <?php wp_nonce_field('export_logs_nonce', 'export_logs_nonce'); ?>
                        <input type="hidden" name="action" value="export_logs">
                        <button type="submit" class="btn btn-success">
                            <?php esc_html_e('Export Logs', 'ams-wc-amazon'); ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <?php if (empty($paginated_logs)) : ?>
                <p class="text-muted"><?php esc_html_e('No logs found.', 'ams-wc-amazon'); ?></p>
            <?php else : ?>
                <pre class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;"><?php echo esc_html($paginated_logs); ?></pre>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($total_pages > 1) : ?>
        <nav aria-label="Log navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                $pagination_args = array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'total' => $total_pages,
                    'current' => $current_page,
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'prev_next' => true,
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'type' => 'array',
                );
                $page_links = paginate_links($pagination_args);
                
                if (is_array($page_links)) {
                    foreach ($page_links as $link) {
                        if (strpos($link, 'current') !== false) {
                            echo '<li class="page-item active"><span class="page-link">' . $link . '</span></li>';
                        } else {
                            echo '<li class="page-item">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                        }
                    }
                }
                ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>