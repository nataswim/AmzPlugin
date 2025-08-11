<?php include "common-header.php";

$results_limit = get_option('ams_results_limit', '50');

if ( class_exists( 'WooCommerce' ) ) {
    $currency_symbol = get_woocommerce_currency_symbol();
} else {
    $currency_symbol = '$';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="" class="wca-product-without-api-search" method="POST">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="ams_amazon_cat" id="ams_amazon_cat" class="form-select" aria-label="<?php esc_attr_e('Select category', 'ams-wc-amazon'); ?>">
                                    <option selected disabled><?php esc_html_e('All Categories', 'ams-wc-amazon'); ?></option>
                                    <?php foreach ( $this->get_amazon_cat() as $key => $value ) { ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input name="keyword" id="keyword" class="form-control" placeholder="<?php echo esc_attr__( 'Search Amazon', 'ams-wc-amazon' ); ?>" type="text">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="star_rating" id="star_rating" class="form-select" aria-label="<?php esc_attr_e('Select customer review rating', 'ams-wc-amazon'); ?>">
                                    <option value="" selected><?php esc_html_e('Any Rating', 'ams-wc-amazon'); ?></option>
                                    <option value="4"><?php esc_html_e('4 Stars & Up', 'ams-wc-amazon'); ?></option>
                                    <option value="3"><?php esc_html_e('3 Stars & Up', 'ams-wc-amazon'); ?></option>
                                    <option value="2"><?php esc_html_e('2 Stars & Up', 'ams-wc-amazon'); ?></option>
                                    <option value="1"><?php esc_html_e('1 Star & Up', 'ams-wc-amazon'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><?php echo esc_html($currency_symbol); ?></span>
                                    <input name="min_price" id="min_price" class="form-control" type="number" min="0" step="1" placeholder="<?php esc_attr_e('Min', 'ams-wc-amazon'); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><?php echo esc_html($currency_symbol); ?></span>
                                    <input name="max_price" id="max_price" class="form-control" type="number" min="0" step="1" placeholder="<?php esc_attr_e('Max', 'ams-wc-amazon'); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input name="title_search" id="title_search" class="form-control" placeholder="<?php echo esc_attr__( 'Filter by title (works in real-time)', 'ams-wc-amazon' ); ?>" type="text">
                            </div>
                        </div>
                        <input type="hidden" name="action" value="<?php echo esc_attr('search_products'); ?>">
                        <?php wp_nonce_field( 'wca_search_product' ); ?>
                    </form>
                    
                    <?php if ($results_limit) : ?>
                    <div class="alert alert-info d-flex align-items-center mt-3 mb-0 py-2" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 me-2"></i>
                        <div>
                            <?php printf(esc_html__('Your search will return up to %s results.', 'ams-wc-amazon'), $results_limit); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div id="validation-error-message" class="alert alert-danger text-center d-none mt-3 mb-0" role="alert"></div>
                </div>
            </div>
            
            <div class="product_cards container-fluid">
                <div class="row wca-amazon-product g-3">
                    <!-- Product cards will be dynamically inserted here -->
                </div>
                <div class="text-center mt-4">
                    <div class="wca-loading-icon mt-3" style="display:none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?php esc_html_e('Loading...', 'ams-wc-amazon'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "common-footer.php"; ?>