<?php include "common-header.php"; ?>

<div class="container-fluid">
  <!--row-1-->
  <header class="bg-gradient-primary py-6">
    <div class="container-fluid">
      <div class="header-body">
        <div class="row align-items-center mb-4">
          <div class="col-lg-6">
            <h1 class="display-4 text-white fw-bold mb-0"><?php esc_html_e(' Dashboard', 'ams-wc-amazon'); ?></h1>
          </div>
          <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
          <span class="badge bg-light text-primary p-2 fs-6 me-2">
              <?php echo esc_html(sprintf(__('Version %s', 'ams-wc-amazon'), get_file_data(AMS_PLUGIN_FILE, array('Version' => 'Version'))['Version'])); ?>
          </span>
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#quickActions" aria-controls="quickActions">
              <i class="fas fa-bolt me-1"></i> Quick Actions
            </button>
          </div>
        </div>
        <div class="row g-4">
          <?php
          $card_data = [
            [
              'title' => __('Total Number of products', 'ams-wc-amazon'),
              'value' => $products_info['products_count'],
              'icon' => 'fa-chart-bar',
              'color' => 'danger'
            ],
            [
              'title' => __('Total products views', 'ams-wc-amazon'),
              'value' => $products_info['total_view_count'],
              'icon' => 'fa-chart-pie',
              'color' => 'warning'
            ],
            [
              'title' => __('Products added to cart', 'ams-wc-amazon'),
              'value' => $products_info['total_product_added_to_cart'],
              'icon' => 'fa-shopping-cart',
              'color' => 'success'
            ],
            [
              'title' => __('Total redirected to Amazon', 'ams-wc-amazon'),
              'value' => $products_info['total_product_direct_redirected'],
              'icon' => 'fa-external-link-alt',
              'color' => 'info'
            ]
          ];
          foreach ($card_data as $card) : ?>
            <div class="col-xl-3 col-md-6">
              <div class="card bg-white shadow-sm h-100">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-auto">
                      <div class="icon-shape bg-<?php echo $card['color']; ?> text-white rounded-circle shadow p-3">
                        <i class="fas <?php echo $card['icon']; ?> fa-fw"></i>
                      </div>
                    </div>
                    <div class="col">
                      <h5 class="text-uppercase text-muted mb-1 fs-6"><?php echo $card['title']; ?></h5>
                      <span class="h2 fw-bold mb-0"><?php echo esc_html($card['value']); ?></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </header>
  <!--row-1-->

  <!-- Quick Actions Offcanvas -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="quickActions" aria-labelledby="quickActionsLabel">
    <div class="offcanvas-header bg-primary py-4 mt-5">
      <h5 class="offcanvas-title text-white fs-5" id="quickActionsLabel">
        <i class="fas fa-bolt me-2"></i>Quick Actions
      </h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-3">
      <div class="list-group list-group-flush">
        <a href="?page=wc-product-search" class="list-group-item list-group-item-action py-3 fs-6" target="_blank" rel="noopener noreferrer">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <span><i class="fas fa-file-import me-3 text-primary"></i>Import By PA-API 5</span>
            <i class="fas fa-external-link-alt"></i>
          </div>
        </a>
        <a href="?page=products-search-without-api" class="list-group-item list-group-item-action py-3 fs-6" target="_blank" rel="noopener noreferrer">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <span><i class="fas fa-search me-3 text-warning"></i>Import by Search (No API)</span>
            <i class="fas fa-external-link-alt"></i>
          </div>
        </a>
        <a href="?page=product-import-by-url" class="list-group-item list-group-item-action py-3 fs-6" target="_blank" rel="noopener noreferrer">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <span><i class="fas fa-link me-3 text-info"></i>Import by URL (No API)</span>
            <i class="fas fa-external-link-alt"></i>
          </div>
        </a>
        <a href="?page=product-review-import" class="list-group-item list-group-item-action py-3 fs-6" target="_blank" rel="noopener noreferrer">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <span><i class="fas fa-star me-3 text-warning"></i>Import Reviews</span>
            <i class="fas fa-external-link-alt"></i>
          </div>
        </a>
        <a href="?page=wc-product-setting-page&tab=pills-general-tab" class="list-group-item list-group-item-action py-3 fs-6" target="_blank" rel="noopener noreferrer">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <span><i class="fas fa-sliders-h me-3 text-primary"></i>General Settings</span>
            <i class="fas fa-external-link-alt"></i>
          </div>
        </a>
        <a href="?page=wc-product-setting-page&tab=pills-az-settings-tab" class="list-group-item list-group-item-action py-3 fs-6" target="_blank" rel="noopener noreferrer">
          <div class="d-flex w-100 justify-content-between align-items-center">
            <span><i class="fab fa-amazon me-3 text-warning"></i>Amazon API Settings</span>
            <i class="fas fa-external-link-alt"></i>
          </div>
        </a>
      </div>

      <div class="mt-4">
        <a href="?page=view-logs" class="btn btn-outline-primary d-block mb-3 py-2 fs-5" target="_blank" rel="noopener noreferrer">
          <i class="fas fa-list-ul me-2"></i><?php esc_html_e('View Logs', 'ams-wc-amazon'); ?>
        </a>
        <a href="knowledge-base/" class="btn btn-outline-info d-block mb-3 py-2 fs-5" target="_blank" rel="noopener noreferrer">
          <i class="fas fa-question-circle me-2"></i><?php esc_html_e('Help & Documentation', 'ams-wc-amazon'); ?>
        </a>
        <a href="suggestion-request/" class="btn btn-outline-success d-block py-2 fs-5" target="_blank" rel="noopener noreferrer">
          <i class="fas fa-lightbulb me-2"></i><?php esc_html_e('Submit Suggestion', 'ams-wc-amazon'); ?>
        </a>
      </div>
    </div>
    
    <div class="offcanvas-footer bg-light p-3">
      <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted fs-6"><?php esc_html_e('', 'ams-wc-amazon'); ?></span>
        <span class="badge bg-primary fs-6">v<?php echo esc_html(AMS_PLUGIN_VERSION); ?></span>
      </div>
    </div>
  </div>

  <!--row-2-->
  <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">

    <!-- AMAZON API -->
    <div class="col d-flex">
      <div class="card shadow-sm border-0 w-100">
        <!-- Card Header -->
        <div class="card-header bg-light d-flex align-items-center">
          <h5 class="mb-0 text-primary">
            <?php esc_html_e('Amazon API Statistics', 'ams-wc-amazon'); ?>
          </h5>
        </div>

        <!-- Card Body -->
        <div class="card-body">
          <div class="d-flex flex-column gap-3">
            <!-- Products search limit -->
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="text-muted mb-0">
                <?php esc_html_e('Products search limit', 'ams-wc-amazon'); ?>
              </h6>
              <div class="d-flex align-items-center">
                <span class="h5 fw-bold text-success mb-0 me-2">
                  <?php esc_html_e('Unlimited', 'ams-wc-amazon'); ?>
                </span>
                <div class="bg-success text-white rounded-circle shadow d-flex align-items-center justify-content-center"
                     style="width: 32px; height: 32px;">
                  <i class="fas fa-infinity"></i>
                </div>
              </div>
            </div>

            <!-- Total Products search -->
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="text-muted mb-0">
                <?php esc_html_e('Total Products search', 'ams-wc-amazon'); ?>
              </h6>
              <div class="d-flex align-items-center">
                <span class="h5 fw-bold mb-0 me-2">
                  <?php echo esc_html($products_info['products_search_count']); ?>
                </span>
                <div class="bg-primary text-white rounded-circle shadow d-flex align-items-center justify-content-center"
                     style="width: 32px; height: 32px;">
                  <i class="fas fa-search"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SCRAPER API -->
    <div class="col d-flex">
      <div class="card shadow-sm border-0 w-100">
        <?php
          $credits_count = ams_scraper_api_credits_count();
          $is_import = (get_option('ams_scraper_api_is_active') === '1');
          $is_update = (get_option('ams_scraper_api_on_update') === '1');
          $is_active = ($credits_count && isset($credits_count['requestLimit'], $credits_count['requestCount']));
        ?>
        <!-- Card Header -->
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-primary">
            <?php esc_html_e('Scraper API Proxy Service', 'ams-wc-amazon'); ?>
          </h5>
          <?php if ($is_active) : ?>
            <span class="badge rounded-pill bg-success d-flex align-items-center">
              <i class="fas fa-check-circle me-1"></i> <?php esc_html_e('Active', 'ams-wc-amazon'); ?>
            </span>
          <?php else : ?>
            <span class="badge rounded-pill bg-danger d-flex align-items-center">
              <i class="fas fa-times-circle me-1"></i> <?php esc_html_e('Inactive', 'ams-wc-amazon'); ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Card Body -->
        <div class="card-body">
          <!-- Import & Update Badges -->
          <div class="mb-3 d-flex">
            <div class="me-4">
              <strong class="me-1">Import:</strong>
              <span class="badge rounded-pill bg-<?php echo $is_import ? 'success' : 'danger'; ?> d-flex align-items-center">
                <i class="fas fa-<?php echo $is_import ? 'check' : 'times'; ?> me-1"></i>
                <?php echo $is_import ? 'On' : 'Off'; ?>
              </span>
            </div>
            <div>
              <strong class="me-1">Update:</strong>
              <span class="badge rounded-pill bg-<?php echo $is_update ? 'success' : 'danger'; ?> d-flex align-items-center">
                <i class="fas fa-<?php echo $is_update ? 'check' : 'times'; ?> me-1"></i>
                <?php echo $is_update ? 'On' : 'Off'; ?>
              </span>
            </div>
          </div>

          <?php if ($is_active) : 
            $used_credits = $credits_count['requestCount'];
            $total_credits = $credits_count['requestLimit'];
            $used_percentage = ($total_credits > 0) ? round(($used_credits / $total_credits) * 100, 2) : 0;
          ?>
            <!-- Usage Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fs-5">
                <?php echo esc_html(sprintf('%d/%d', $used_credits, $total_credits)); ?>
              </span>
              <span class="badge bg-primary fs-6">
                <?php echo esc_html($used_percentage); ?>%
              </span>
            </div>
            <div class="progress" style="height: 10px;">
              <div class="progress-bar bg-primary" role="progressbar"
                   style="width: <?php echo esc_attr($used_percentage); ?>%;"
                   aria-valuenow="<?php echo esc_attr($used_percentage); ?>"
                   aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>
          <?php else : ?>
            <!-- Not Connected -->
            <div class="alert alert-warning mb-0" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i><?php esc_html_e('Not Connected', 'ams-wc-amazon'); ?>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wc-product-setting-page&tab=pills-general-tab')); ?>"
               class="btn btn-primary mt-3">
              Go To Settings
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- SCRAPINGANT API -->
    <div class="col d-flex">
      <div class="card shadow-sm border-0 w-100">
        <?php
          $credits_status = scrapingant_api_credits_status();
          $is_import = (get_option('ams_scrapingant_is_active') === '1');
          $is_update = (get_option('ams_scrapingant_on_update') === '1');
          $is_active = ($credits_status && isset($credits_status['plan_total_credits'], $credits_status['remained_credits']));
        ?>
        <!-- Card Header -->
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-primary">
            <?php esc_html_e('ScrapingAnt API Proxy Service', 'ams-wc-amazon'); ?>
          </h5>
          <?php if ($is_active) : ?>
            <span class="badge rounded-pill bg-success d-flex align-items-center">
              <i class="fas fa-check-circle me-1"></i> <?php esc_html_e('Active', 'ams-wc-amazon'); ?>
            </span>
          <?php else : ?>
            <span class="badge rounded-pill bg-danger d-flex align-items-center">
              <i class="fas fa-times-circle me-1"></i> <?php esc_html_e('Inactive', 'ams-wc-amazon'); ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Card Body -->
        <div class="card-body">
          <!-- Import & Update Badges -->
          <div class="mb-3 d-flex">
            <div class="me-4">
              <strong class="me-1">Import:</strong>
              <span class="badge rounded-pill bg-<?php echo $is_import ? 'success' : 'danger'; ?> d-flex align-items-center">
                <i class="fas fa-<?php echo $is_import ? 'check' : 'times'; ?> me-1"></i>
                <?php echo $is_import ? 'On' : 'Off'; ?>
              </span>
            </div>
            <div>
              <strong class="me-1">Update:</strong>
              <span class="badge rounded-pill bg-<?php echo $is_update ? 'success' : 'danger'; ?> d-flex align-items-center">
                <i class="fas fa-<?php echo $is_update ? 'check' : 'times'; ?> me-1"></i>
                <?php echo $is_update ? 'On' : 'Off'; ?>
              </span>
            </div>
          </div>

          <?php if ($is_active) :
            $used_credits = $credits_status['plan_total_credits'] - $credits_status['remained_credits'];
            $total_credits = $credits_status['plan_total_credits'];
            $used_percentage = ($total_credits > 0) ? round(($used_credits / $total_credits) * 100, 2) : 0;
          ?>
            <!-- Usage Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fs-5">
                <?php echo esc_html(sprintf('%d/%d', $used_credits, $total_credits)); ?>
              </span>
              <span class="badge bg-info fs-6">
                <?php echo esc_html($used_percentage); ?>%
              </span>
            </div>
            <div class="progress" style="height: 10px;">
              <div class="progress-bar bg-info" role="progressbar"
                   style="width: <?php echo esc_attr($used_percentage); ?>%;"
                   aria-valuenow="<?php echo esc_attr($used_percentage); ?>"
                   aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>
          <?php else : ?>
            <!-- Not Connected -->
            <div class="alert alert-warning mb-0" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i><?php esc_html_e('Not Connected', 'ams-wc-amazon'); ?>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wc-product-setting-page&tab=pills-general-tab')); ?>"
               class="btn btn-primary mt-3">
              Go To Settings
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- LICENSE -->
    <div class="col d-flex">
      <div class="card shadow-sm border-0 w-100">
        <?php
          // Fetch license status and existing key
          $ams_activated_status = ($ams_activated_status ?? '') === 'success' ? 'success' : 'inactive';
          $saved_license_key = get_option('ams_activated_license', '');
          $is_active = ($ams_activated_status === 'success');
        ?>

        <!-- Card Header -->
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-primary">
            <i class="fas fa-key me-2"></i>
            <?php esc_html_e('License', 'ams-wc-amazon'); ?>
          </h5>
          <span id="license-status-badge"
                class="badge rounded-pill <?php echo $is_active ? 'bg-success' : 'bg-warning text-dark'; ?>">
            <i class="fas <?php echo $is_active ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-1"></i>
            <span id="license-status-text">
              <?php echo $is_active
                ? esc_html__('Active', 'ams-wc-amazon')
                : esc_html__('Inactive', 'ams-wc-amazon'); ?>
            </span>
          </span>
        </div>

        <!-- Card Body -->
        <div class="card-body p-3">
          <form id="license-form" class="wca-admin-page-activation-form-container">
            <div class="input-group">
              <input type="text"
                     id="purchase_code"
                     name="purchase_code"
                     class="form-control form-control-sm wca-purchase-code-input"
                     placeholder="<?php esc_attr_e('Enter CodeCanyon license key', 'ams-wc-amazon'); ?>"
                     value="<?php echo esc_attr($saved_license_key); ?>"
                     <?php echo $is_active ? 'disabled' : ''; ?>>
              <?php if ($is_active): ?>
                <!-- Deactivate Button -->
                <button type="button" class="btn btn-sm btn-outline-danger ams-deactivated">
                  <i class="fas fa-power-off"></i>
                </button>
              <?php else: ?>
                <!-- Activate Button -->
                <button type="submit" class="btn btn-sm btn-primary wca-activation-btn">
                  <i class="fas fa-check"></i>
                </button>
              <?php endif; ?>
            </div>
          </form>

          <!-- Info / Hint -->
          <div class="wca-purchase-massage mt-2 small text-muted">
            <i class="fas fa-info-circle me-1"
               id="license-info-icon"
               data-bs-toggle="popover"
               data-bs-trigger="hover focus"
               data-bs-html="true"
               data-bs-placement="top"
               data-bs-content="To find the license key, see:
                 <a href='https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code'
                    target='_blank'
                    rel='noopener noreferrer'
                    class='text-info'>
                   Where Is My Purchase Code?
                 </a>">
            </i>
            <?php esc_html_e('Enter your CodeCanyon purchase code to activate.', 'ams-wc-amazon'); ?>
          </div>
        </div>
      </div>
    </div>


    <script>
    jQuery(document).ready(function($) {
        const input = $('#purchase_code');
        const activateBtn = $('.wca-activation-btn');

        // Initialize Bootstrap popover
        var popover = new bootstrap.Popover(document.getElementById('license-info-icon'), {
          trigger: 'manual',
          html: true
        });

        // Show popover on hover or focus
        $('#license-info-icon').on('mouseenter focus', function() {
          popover.show();
        });

        // Hide popover when mouse leaves the icon and the popover itself
        $('#license-info-icon').on('mouseleave', function(e) {
          setTimeout(function() {
            if (!$('.popover:hover').length) {
              popover.hide();
            }
          }, 300);
        });

        // Hide popover when mouse leaves the popover
        $(document).on('mouseleave', '.popover', function() {
          popover.hide();
        });

        // Hide popover when clicking outside
        $(document).on('click', function(e) {
          if ($(e.target).closest('#license-info-icon, .popover').length === 0) {
            popover.hide();
          }
        });

        // Enable/disable activate button based on input
        input.on('input', function() {
            activateBtn.prop('disabled', !this.value.trim());
        });
    });
    </script>
  </div>
  <!--row-2-->


  <!--ServerSettings-->
  <div class="row">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-transparent">
          <h3 class="mb-0"><?php esc_html_e('System Requirements', 'ams-wc-amazon'); ?></h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-flush">
              <thead class="thead-light">
                <tr>
                  <th><?php esc_html_e('Setting', 'ams-wc-amazon'); ?></th>
                  <th><?php esc_html_e('Required', 'ams-wc-amazon'); ?></th>
                  <th><?php esc_html_e('Current', 'ams-wc-amazon'); ?></th>
                  <th><?php esc_html_e('Status', 'ams-wc-amazon'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php $formattedSettings = displayServerSettings(); ?>
                <?php foreach ($formattedSettings as $setting): ?>
                  <tr>
                    <td><?php echo esc_html($setting['name']); ?></td>
                    <td><?php echo esc_html($setting['required']); ?></td>
                    <td><?php echo esc_html($setting['value']); ?></td>
                    <td>
                      <?php if ($setting['meets_requirement']): ?>
                        <span class="badge bg-success">
                          <i class="fas fa-check"></i> <?php esc_html_e('Meets', 'ams-wc-amazon'); ?>
                        </span>
                      <?php else: ?>
                        <span class="badge bg-danger">
                          <i class="fas fa-times"></i> <?php esc_html_e('Does Not Meet', 'ams-wc-amazon'); ?>
                        </span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--ServerSettings-->

  <!--Changelogs-->
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-primary py-3">
          <div class="d-flex align-items-center">
            <div class="icon-shape bg-white text-primary rounded-circle shadow d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
              <i class="fas fa-history"></i>
            </div>
            <h5 class="text-white mb-0"><?= esc_html__('Changelogs', 'ams-wc-amazon'); ?></h5>
            <span class="badge bg-light text-primary ms-auto">
              v<?php echo esc_html(get_file_data(AMS_PLUGIN_FILE, array('Version' => 'Version'))['Version']); ?>
            </span>
          </div>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <a href="woocommerce-amazon-logs/" target="_blank" class="card bg-gradient-primary border-0 h-100">
                <div class="card-body d-flex align-items-center text-white">
                  <div class="icon-shape bg-white text-primary rounded-circle shadow d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                    <i class="fas fa-clipboard-list"></i>
                  </div>
                  <span class="fs-6"><?= esc_html__('View Complete Changelog', 'ams-wc-amazon'); ?></span>
                  <i class="fas fa-chevron-right ms-auto"></i>
                </div>
              </a>
            </div>
            
            <div class="col-md-4">
              <a href="knowledge-base/" target="_blank" class="card bg-gradient-info border-0 h-100">
                <div class="card-body d-flex align-items-center text-white">
                  <div class="icon-shape bg-white text-info rounded-circle shadow d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                    <i class="fas fa-book"></i>
                  </div>
                  <span class="fs-6"><?= esc_html__('Documentation', 'ams-wc-amazon'); ?></span>
                  <i class="fas fa-chevron-right ms-auto"></i>
                </div>
              </a>
            </div>

            <div class="col-md-4">
              <a href="suggestion-request/" target="_blank" class="card bg-gradient-success border-0 h-100">
                <div class="card-body d-flex align-items-center text-white">
                  <div class="icon-shape bg-white text-success rounded-circle shadow d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px;">
                    <i class="fas fa-lightbulb"></i>
                  </div>
                  <span class="fs-6"><?= esc_html__('Submit Suggestion', 'ams-wc-amazon'); ?></span>
                  <i class="fas fa-chevron-right ms-auto"></i>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--Changelogs-->


  <!--footer-->
  <footer class="footer bg-light py-4 mt-auto">
    <div class="container">
      <div class="row align-items-center justify-content-between">
        <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
          <div class="d-flex align-items-center justify-content-center justify-content-md-start">
            <img src="<?php echo esc_url(AMS_PLUGIN_URL . 'assets/img/brand/ams.png'); ?>" alt="AMS Amazon Logo" class="me-2" style="height: 30px;">
            <span class="text-muted">
              &copy; <?php echo date("Y"); ?> 
              <a href="" class="text-primary text-decoration-none fw-bold" target="_blank">
                <?php esc_html_e('AMS Amazon', 'ams-wc-amazon'); ?>
              </a>
            </span>
          </div>
        </div>
        <div class="col-md-8">
          <ul class="nav justify-content-center justify-content-md-end">
            <li class="nav-item">
              <a href="https://codecanyon.net/item/affiliate-management-system-woocommerce-amazon/29955429/support" class="nav-link px-2 text-muted" target="_blank">
                <i class="fas fa-headset me-1"></i><?php esc_html_e('Support', 'ams-wc-amazon'); ?>
              </a>
            </li>
            <li class="nav-item">
              <a href="https://codecanyon.net/user/affiliateprosaas/portfolio" class="nav-link px-2 text-muted" target="_blank">
                <i class="fas fa-store me-1"></i><?php esc_html_e('Store', 'ams-wc-amazon'); ?>
              </a>
            </li>
            <li class="nav-item">
              <a href="knowledge-base/" class="nav-link px-2 text-muted" target="_blank">
                <i class="fas fa-book me-1"></i><?php esc_html_e('Documentation', 'ams-wc-amazon'); ?>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <hr class="my-4">
      <div class="row">
        <div class="col-12 text-center">
          <p class="text-muted mb-0">
            <?php esc_html_e('Empowering your Amazon affiliate business with cutting-edge management tools.', 'ams-wc-amazon'); ?>
          </p>
        </div>
      </div>
    </div>
  </footer>
  <!--footer-->
</div>