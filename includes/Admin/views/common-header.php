<?php
$navbar_data = ams_fetch_navbar_data();
$breadcrumbs = ams_generate_breadcrumbs();

$regions = ams_get_amazon_regions();
$ams_amazon_country = get_option('ams_amazon_country');
$ams_default_category = get_option('ams_default_category');
$store_currency = get_woocommerce_currency();
$currency_code_options = get_woocommerce_currencies();

// Get the correct display values
$regions_placeholder = isset($regions[$ams_amazon_country]['RegionName']) ? $regions[$ams_amazon_country]['RegionName'] : $ams_amazon_country;
$category_placeholder = $ams_default_category === '_auto_import_amazon' ? 'Auto Import From Amazon' : $ams_default_category;
$currency_placeholder = isset($currency_code_options[$store_currency]) ? $currency_code_options[$store_currency] : $store_currency;

//License section
$ams_activated_status = get_option('ams_activated_status');
$wca_license_class  = $ams_activated_status == 'success' ? 'text-success' : 'text-warning';
$wca_license_status = $ams_activated_status == 'success' ? esc_html__('Activated', 'ams-wc-amazon') : esc_html__('Not Activated', 'ams-wc-amazon');

// formatted HTML (for display)
$wca_license = sprintf('<span class="%s">%s</span>', $wca_license_class, $wca_license_status);
//License section

?>
<input type="hidden" id="wca_license_status" value="<?php echo esc_attr($wca_license_status); ?>">

<!-- Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">

<!-- Icons -->
<link rel="stylesheet" href="<?= AMS_PLUGIN_URL ?>assets/vendor/nucleo/css/nucleo.css" type="text/css">
<link rel="stylesheet" href="<?= AMS_PLUGIN_URL ?>assets/vendor/@fortawesome/fontawesome-free/css/all.min.css" type="text/css">

<!-- Bootstrap CSS (specific to this template) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" type="text/css">

<!-- Custom CSS -->
<link rel="stylesheet" href="<?= AMS_PLUGIN_URL ?>assets/css/custom.css" type="text/css">
<link rel="stylesheet" href="<?= AMS_PLUGIN_URL ?>assets/css/argon.css?v=1.2.0" type="text/css">

<!-- jQuery and Bootstrap JS -->
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/jquery/dist/jquery.min.js"></script>
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/jquery/dist/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Other JS dependencies -->
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/js-cookie/js.cookie.js"></script>
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script>
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/chart.js/dist/Chart.min.js"></script>
<script src="<?= AMS_PLUGIN_URL ?>assets/vendor/chart.js/dist/Chart.extension.js"></script>
<script src="<?= AMS_PLUGIN_URL ?>assets/js/argon.js?v=1.2.0"></script>


<!--top menu-->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container-fluid">
    <a class="navbar-brand" href="?page=wc-amazon-affiliate">
      <img src="<?php echo esc_url(AMS_PLUGIN_URL . 'assets/img/brand/ams.png'); ?>" alt="" height="30">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
        <a class="nav-link <?php echo ($_GET['page'] == 'wc-amazon-affiliate') ? 'active text-primary border-bottom border-primary border-2' : ''; ?>" href="?page=wc-amazon-affiliate">
          <i class="ni ni-chart-bar-32 me-1"></i> <?php esc_html_e('Dashboard', 'ams-wc-amazon'); ?>
        </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($_GET['page'] == 'wc-product-search') ? 'active text-primary border-bottom border-primary border-2' : ''; ?>" href="?page=wc-product-search">
            <i class="ni ni-cloud-upload-96 me-1"></i> <?php esc_html_e('Import By PA-API 5', 'ams-wc-amazon'); ?>
          </a>
        </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?php echo (in_array($_GET['page'], ['product-import-by-url', 'products-search-without-api'])) ? 'active text-primary border-bottom border-primary border-2' : ''; ?>" href="#" id="noApiImportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ni ni-button-power me-1"></i> <?php esc_html_e('Import Without API', 'ams-wc-amazon'); ?>
            </a>
            <ul class="dropdown-menu shadow" aria-labelledby="navbarDropdown">
              <li><h6 class="dropdown-header"><?php esc_html_e('Import Options', 'ams-wc-amazon'); ?></h6></li>
              <li>
                <div class="dropdown-item d-flex align-items-center py-2 <?php echo ($_GET['page'] == 'products-search-without-api') ? 'active' : ''; ?>">
                  <a href="?page=products-search-without-api" class="d-flex align-items-center text-decoration-none text-reset flex-grow-1">
                    <i class="bi bi-search me-3"></i>
                    <span class="me-auto"><?php esc_html_e('Import by Search', 'ams-wc-amazon'); ?></span>
                  </a>
                  <a href="?page=products-search-without-api" class="ms-2" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                </div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <div class="dropdown-item d-flex align-items-center py-2 <?php echo ($_GET['page'] == 'product-import-by-url') ? 'active' : ''; ?>">
                  <a href="?page=product-import-by-url" class="d-flex align-items-center text-decoration-none text-reset flex-grow-1">
                    <i class="bi bi-globe me-3"></i>
                    <span class="me-auto"><?php esc_html_e('Import by URL', 'ams-wc-amazon'); ?></span>
                  </a>
                  <a href="?page=product-import-by-url" class="ms-2" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                    <i class="bi bi-box-arrow-up-right"></i>
                  </a>
                </div>
              </li>
            </ul>
          </li>
        <?php if ('1' == get_option('enable_amazon_review', true)) : ?>
        <li class="nav-item">
          <a class="nav-link <?php echo ($_GET['page'] == 'product-review-import') ? 'active text-primary border-bottom border-primary border-2' : ''; ?>" href="?page=product-review-import">
            <i class="ni ni-chat-round me-1"></i> <?php esc_html_e('Import Review', 'ams-wc-amazon'); ?>
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?php echo ($_GET['page'] == 'wc-product-setting-page') ? 'active text-primary border-bottom border-primary border-2' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ni ni-settings me-1"></i> <?php esc_html_e('Settings', 'ams-wc-amazon'); ?>
          </a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="navbarDropdown">
            <li>
              <div class="dropdown-item d-flex align-items-center py-2 <?php echo (isset($_GET['page']) && $_GET['page'] == 'wc-product-setting-page' && isset($_GET['tab']) && $_GET['tab'] == "pills-general-tab") ? 'active bg-light fw-bold' : ''; ?>">
                <a href="?page=wc-product-setting-page&tab=pills-general-tab" class="d-flex align-items-center text-decoration-none text-reset flex-grow-1">
                  <i class="bi bi-file-text me-3"></i>
                  <span class="me-auto"><?php esc_html_e('Configuration', 'ams-wc-amazon'); ?></span>
                </a>
                <a href="?page=wc-product-setting-page&tab=pills-general-tab" class="ms-2" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <?php if (isset($_GET['page']) && $_GET['page'] == 'wc-product-setting-page' && isset($_GET['tab']) && $_GET['tab'] == "pills-general-tab"): ?>
                  <i class="bi bi-check-lg text-primary ms-2"></i>
                <?php endif; ?>
              </div>
            </li>
            <li>
              <div class="dropdown-item d-flex align-items-center py-2 <?php echo (isset($_GET['page']) && $_GET['page'] == 'view-logs') ? 'active bg-light fw-bold' : ''; ?>">
                <a href="?page=view-logs" class="d-flex align-items-center text-decoration-none text-reset flex-grow-1">
                  <i class="bi bi-list-ul me-3"></i>
                  <span class="me-auto"><?php esc_html_e('View Logs', 'ams-wc-amazon'); ?></span>
                </a>
                <a href="?page=view-logs" class="ms-2" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <?php if (isset($_GET['page']) && $_GET['page'] == 'view-logs'): ?>
                  <i class="bi bi-check-lg text-primary ms-2"></i>
                <?php endif; ?>
              </div>
            </li>
            <li>
              <div class="dropdown-item d-flex align-items-center py-2 <?php echo (isset($_GET['page']) && $_GET['page'] == 'wc-product-setting-page' && isset($_GET['tab']) && $_GET['tab'] == "pills-az-settings-tab") ? 'active bg-light fw-bold' : ''; ?>">
                <a href="?page=wc-product-setting-page&tab=pills-az-settings-tab" class="d-flex align-items-center text-decoration-none text-reset flex-grow-1">
                  <i class="bi bi-key me-3"></i>
                  <span class="me-auto"><?php esc_html_e('Amazon API Setting', 'ams-wc-amazon'); ?></span>
                </a>
                <a href="?page=wc-product-setting-page&tab=pills-az-settings-tab" class="ms-2" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <?php if (isset($_GET['page']) && $_GET['page'] == 'wc-product-setting-page' && isset($_GET['tab']) && $_GET['tab'] == "pills-az-settings-tab"): ?>
                  <i class="bi bi-check-lg text-primary ms-2"></i>
                <?php endif; ?>
              </div>
            </li>
            <li>
              <div class="dropdown-item d-flex align-items-center py-2 <?php echo (isset($_GET['page']) && $_GET['page'] == 'wc-product-setting-page' && isset($_GET['tab']) && $_GET['tab'] == "pills-az-products-tab") ? 'active bg-light fw-bold' : ''; ?>">
                <a href="?page=wc-product-setting-page&tab=pills-az-products-tab" class="d-flex align-items-center text-decoration-none text-reset flex-grow-1">
                  <i class="bi bi-arrow-repeat me-3"></i>
                  <span class="me-auto"><?php esc_html_e('Auto Products Update', 'ams-wc-amazon'); ?></span>
                </a>
                <a href="?page=wc-product-setting-page&tab=pills-az-products-tab" class="ms-2" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <?php if (isset($_GET['page']) && $_GET['page'] == 'wc-product-setting-page' && isset($_GET['tab']) && $_GET['tab'] == "pills-az-products-tab"): ?>
                  <i class="bi bi-check-lg text-primary ms-2"></i>
                <?php endif; ?>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!--top menu-->

<!--License Alert All Pages-->
<div class="container-fluid mt-3">
    <?php if(ams_plugin_license_status() === false) { ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
        <div>
          <strong><?php esc_html_e('Alert!', 'ams-wc-amazon'); ?></strong>
          <?php esc_html_e('Affiliate Management System - WooCommerce Amazon plugin license not Activated. Please activate the plugin license.', 'ams-wc-amazon'); ?>
        </div>
      </div>
    </div>
  <?php } ?>
</div>
<!--License Alert All Pages-->

<?php if ($_GET['page'] != 'wc-amazon-affiliate'): ?>
<div class="bg-light border-bottom shadow-sm py-4">
  <div class="container-fluid">
    <div class="row gy-4 align-items-center">
      <div class="col-xl-3 col-lg-4">
        <h1 class="h4 mb-2 text-primary fw-bold"><?php echo esc_html(get_admin_page_title()); ?></h1>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 bg-transparent p-0 small">
            <?php
            $breadcrumbs = ams_generate_breadcrumbs();
            foreach ($breadcrumbs as $index => $crumb): ?>
              <?php if ($index === array_key_last($breadcrumbs)): ?>
                <li class="breadcrumb-item active" aria-current="page"><span class="text-muted"><?php echo esc_html($crumb['title']); ?></span></li>
              <?php else: ?>
                <li class="breadcrumb-item"><a href="<?php echo esc_url($crumb['url']); ?>" class="text-decoration-none"><?php echo esc_html($crumb['title']); ?></a></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>
        </nav>
      </div>
      <div class="col-xl-9 col-lg-8">
        <div class="d-flex flex-wrap justify-content-start justify-content-lg-end align-items-center gap-3">
          <div class="d-flex align-items-center bg-white rounded p-3 shadow-sm flex-grow-1" style="max-width: 180px; height: 80px;">
            <i class="fas fa-globe text-primary fs-4 me-3" title="Amazon Country"></i>
            <div>
              <small class="text-muted d-block mb-1"><?php esc_html_e('Country', 'ams-wc-amazon'); ?></small>
              <span class="fw-bold text-truncate d-inline-block" style="max-width: 100px;" title="<?php echo esc_attr($regions_placeholder); ?>"><?php echo esc_html($regions_placeholder); ?></span>
            </div>
          </div>
          <div class="d-flex align-items-center bg-white rounded p-3 shadow-sm flex-grow-1" style="max-width: 180px; height: 80px;">
            <i class="fas fa-folder text-success fs-4 me-3" title="Default Category"></i>
            <div>
              <small class="text-muted d-block mb-1"><?php esc_html_e('Category', 'ams-wc-amazon'); ?></small>
              <span class="fw-bold text-truncate d-inline-block" style="max-width: 100px;" title="<?php echo esc_attr($category_placeholder); ?>"><?php echo esc_html($category_placeholder); ?></span>
            </div>
          </div>
          <div class="d-flex align-items-center bg-white rounded p-3 shadow-sm flex-grow-1" style="max-width: 180px; height: 80px;">
            <i class="fas fa-money-bill-wave text-info fs-4 me-3" title="WooCommerce Currency"></i>
            <div>
              <small class="text-muted d-block mb-1"><?php esc_html_e('Currency', 'ams-wc-amazon'); ?></small>
              <span class="fw-bold text-truncate d-inline-block" style="max-width: 100px;" title="<?php echo esc_attr($currency_placeholder); ?>"><?php echo esc_html($currency_placeholder); ?></span>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <button type="button" data-bs-toggle="modal" data-bs-target="#settingsModel" class="btn btn-primary shadow-sm px-3 py-2" style="height: 80px; width: 180px;">
              <i class="fas fa-cog me-2 fs-5"></i>
              <span class="d-block mt-1"><?php esc_html_e('Country Settings', 'ams-wc-amazon'); ?></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- Modal -->
<div class="modal fade" id="settingsModel" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <div class="modal-header bg-primary">
          <h5 class="modal-title text-white fs-4" id="settingsModalLabel">
            <i class="bi bi-gear-fill me-2"></i><?php esc_html_e('Country Settings', 'ams-wc-amazon'); ?>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" name="action" value="<?php echo esc_attr('ams_wc_product_cat_setting'); ?>">
          <input type="hidden" name="page_redirect" value="<?php echo esc_attr($_GET['page']); ?>">
          
          <div class="mb-4">
            <label for="amazon_country" class="form-label fw-bold mb-2"><?php esc_html_e('Amazon Country', 'ams-wc-amazon'); ?></label>
            <?php
            $noCountry = isset($noCountry) ? $noCountry : 0;
            $regions = ($noCountry == 1) ? $regionsL : $regions;
            ?>
            <select name="ams_amazon_country" id="amazon_country" class="form-select">
              <option disabled selected><?php echo esc_html($regions_placeholder); ?></option>
              <?php foreach ( $regions as $key => $value ) { ?>
                <option <?php selected(get_option( 'ams_amazon_country' ), $key, true ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value['RegionName'] ); ?></option>
              <?php } ?>
            </select>
          </div>
          
          <div class="mb-4">
            <label for="modal_ams_default_category" class="form-label fw-bold mb-2"><?php esc_html_e('Default WooCommerce Product Category', 'ams-wc-amazon'); ?></label>
            <select name="ams_default_category" id="modal_ams_default_category" class="form-select">
              <option value="_auto_import_amazon" selected="selected"><?php esc_html_e('Auto Import From Amazon', 'ams-wc-amazon'); ?></option>
              <?php foreach (get_wc_terms() as $value) { ?>
                <option value="<?php echo esc_attr( $value['name'] ); ?>" <?php selected(get_option( 'ams_default_category' ), $value['name'] ); ?>><?php echo esc_html( $value['name'] ); ?></option>
              <?php } ?>
            </select>
          </div>
          
          <div class="mb-4">
            <label for="woocommerce_currency" class="form-label fw-bold mb-2"><?php esc_html_e('WooCommerce Currency Setting', 'ams-wc-amazon'); ?></label>
            <select name="woocommerce_currency" id="woocommerce_currency" class="form-select">
              <?php foreach ($currency_code_options as $code=>$value) { ?>
                <option value="<?php echo esc_attr( $code); ?>" <?php selected( $store_currency, $code ); ?>><?php echo esc_html( $value ); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?php esc_html_e('Close', 'ams-wc-amazon'); ?>
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i><?php esc_html_e('Save Settings', 'ams-wc-amazon'); ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
