<?php include "common-header.php"; ?>

<div class="container-fluid py-3">
    <div class="row">
        <div class="col-12">
            <!-- Notification Area -->
            <div id="notification-area">
                <?php 
                if(empty(get_option('ams_access_key_id')) || empty(get_option('ams_secret_access_key')) || empty(get_option('ams_associate_tag'))) { 
                ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong><?php esc_html_e('Note:', 'ams-wc-amazon'); ?></strong>
                        <?php esc_html_e('Your product import/update process will be active once you save your Amazon Affiliate API key details.', 'ams-wc-amazon'); ?>
                        <a href="<?php echo admin_url('admin.php?page=wc-product-setting-page&action=affiliates&tab=pills-az-settings-tab')?>" class="alert-link"><?php esc_html_e('Go To Settings', 'ams-wc-amazon'); ?></a>
                    </div>
                <?php 
                }
                ?>
                <div id="validation-error-message" class="alert alert-warning d-none"></div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form id="wca-product-search" method="POST">
                        <!-- Search Options -->
                        <div class="row mb-4">
                            <div class="col-lg-8 offset-lg-2">
                                <div class="btn-group w-100" role="group" aria-label="<?php esc_attr_e('Search options', 'ams-wc-amazon'); ?>">
                                    <input type="radio" class="btn-check" name="wca_search_by" id="keyword" value="keyword">
                                    <label class="btn btn-outline-primary" for="keyword"><?php esc_html_e('Keyword Search', 'ams-wc-amazon'); ?></label>

                                    <input type="radio" class="btn-check" name="wca_search_by" id="asin" value="asin">
                                    <label class="btn btn-outline-primary" for="asin"><?php esc_html_e('ASIN Numbers', 'ams-wc-amazon'); ?></label>

                                    <input type="radio" class="btn-check" name="wca_search_by" id="csv" value="csv">
                                    <label class="btn btn-outline-primary" for="csv"><?php esc_html_e('Import from CSV', 'ams-wc-amazon'); ?></label>
                                </div>
                            </div>
                        </div>

                        <!-- Search Content -->
                        <div id="search-content">
                            <div id="keyword-content" class="search-box">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <select name="ams_amazon_cat" id="ams_amazon_cat" class="form-select">
                                            <option value=""><?php esc_html_e('Select Category', 'ams-wc-amazon'); ?></option>
                                            <?php
                                            foreach ($this->get_amazon_cat() as $key => $value) {
                                                echo '<option value="' . esc_attr($key) . '">' . esc_html($value) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input name="keyword" class="form-control" placeholder="<?php esc_attr_e('Type search keyword', 'ams-wc-amazon'); ?>" type="text">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="sort_by" id="sort_by" class="form-select">
                                            <option value="Relevance"><?php esc_html_e('Relevance', 'ams-wc-amazon'); ?></option>
                                            <option value="AvgCustomerReviews"><?php esc_html_e('Avg Customer Reviews', 'ams-wc-amazon'); ?></option>
                                            <option value="Featured"><?php esc_html_e('Featured', 'ams-wc-amazon'); ?></option>
                                            <option value="NewestArrivals"><?php esc_html_e('Newest Arrivals', 'ams-wc-amazon'); ?></option>
                                            <option value="Price:HighToLow"><?php esc_html_e('Price: High to Low', 'ams-wc-amazon'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="asin-content" class="search-box" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
                                        <input name="asin_id" type="text" class="form-control" placeholder="<?php esc_attr_e('Enter ASIN numbers (e.g., B0813RK, B08G8BT)', 'ams-wc-amazon'); ?>" />
                                    </div>
                                </div>
                            </div>
                            <div id="csv-content" class="search-box" style="display: none;">
                                <div class="row">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="input-group mb-3">
                                            <input type="file" class="form-control" id="csv" name="csv">
                                            <label class="input-group-text" for="csv"><?php esc_html_e('Upload CSV', 'ams-wc-amazon'); ?></label>
                                        </div>
                                        <div class="text-center">
                                            <a href="<?php echo AMS_PLUGIN_URL; ?>templates/sample-data/sample-data.csv" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-download me-1"></i> <?php esc_html_e('Download Sample File', 'ams-wc-amazon'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <input type="hidden" name="action" id="action" value="search_products">
                            <?php wp_nonce_field('wca_search_product'); ?>
                            <button type="submit" id="submit-button" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> <?php esc_html_e('Search for Products', 'ams-wc-amazon'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="product_cards">
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

<script>
jQuery(document).ready(function($) {
    const storageKey = 'wcaProductSearch';

    function saveState(tab, content) {
        localStorage.setItem(storageKey, JSON.stringify({ tab, content }));
    }

    function loadState() {
        const state = JSON.parse(localStorage.getItem(storageKey)) || { tab: 'keyword', content: {} };
        setActiveTab(state.tab);
        if (state.content) {
            Object.keys(state.content).forEach(key => {
                $(`[name="${key}"]`).val(state.content[key]);
            });
        }
    }

    function setActiveTab(tab) {
        $(`#${tab}`).prop('checked', true);
        $('.search-box').hide();
        $(`#${tab}-content`).show();
        updateSubmitButton(tab);
    }

    function updateSubmitButton(tab) {
        const isCSV = tab === 'csv';
        $('#submit-button').html(`<i class="bi bi-${isCSV ? 'upload' : 'search'} me-1"></i> ${isCSV ? 'Import' : 'Search for'} Products`);
        $('#csv').attr('required', isCSV);
        $('#action').val(isCSV ? 'import_products' : 'search_products');
        $('#wca-product-search').attr('enctype', isCSV ? 'multipart/form-data' : null)
            .toggleClass('wca-product-import', isCSV)
            .toggleClass('wca-product-search', !isCSV);
    }

    function getTabContent(tab) {
        switch(tab) {
            case 'keyword':
                return {
                    ams_amazon_cat: $('#ams_amazon_cat').val(),
                    keyword: $('[name="keyword"]').val(),
                    sort_by: $('#sort_by').val()
                };
            case 'asin':
                return { asin_id: $('[name="asin_id"]').val() };
            default:
                return {};
        }
    }

    // Initial load of state
    loadState();

    // Event listeners
    $('input[name="wca_search_by"]').change(function() {
        const tab = $(this).val();
        setActiveTab(tab);
        saveState(tab, getTabContent(tab));
    });

    $('#wca-product-search').submit(function() {
        const tab = $('input[name="wca_search_by"]:checked').val();
        saveState(tab, getTabContent(tab));
    });

    $('#ams_amazon_cat, [name="keyword"], #sort_by, [name="asin_id"]').change(function() {
        const tab = $('input[name="wca_search_by"]:checked').val();
        saveState(tab, getTabContent(tab));
    });
});
</script>