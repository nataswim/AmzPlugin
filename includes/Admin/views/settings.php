<?php include "common-header.php"; ?>

<div class="wrap" style="margin-top: 20px;">
    <!-- Sidebar Button -->
    <div class="position-fixed top-50 end-0 translate-middle-y">
        <div class="card shadow-sm bg-light border-0 rounded-start">
            <div class="card-body p-0">
                <button class="btn py-3 px-2 d-flex align-items-center" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#settingsSidebar">
                    <i class="fas fa-chevron-left text-primary"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="offcanvas offcanvas-end w-auto mt-4" tabindex="-1" id="settingsSidebar">
        <div class="card shadow-sm h-100 overflow-y-auto">
            <div class="card-header bg-light">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-bars me-2"></i>
                        <?php esc_html_e('Fast Navigation', 'ams-wc-amazon'); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Settings toggles -->
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-0">
                                <?php esc_html_e('Auto-close sidebar after clicking navigation item', 'ams-wc-amazon'); ?>
                            </p>
                        </div>
                        <div class="ms-3">
                            <label class="switch">
                                <input type="checkbox" name="sidebar_autoclose" id="autoCloseSwitch" value="1" <?php checked(get_option('ams_sidebar_autoclose', '1'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation links -->
                <div class="list-group list-group-flush">
                    <!-- General Import Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#general-settings">
                        <i class="fas fa-cog me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('General Import Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Basic import configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Cron Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#daily-cron-settings">
                        <i class="fas fa-clock me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('Cron Job Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Configure automated tasks', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- AMSWOO Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#amswoo-settings">
                        <i class="fas fa-tools me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('AMSWOO Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Advanced AMSWOO configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Shortcode Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#shortcode-settings">
                        <i class="fas fa-code me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('ShortCode Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('ShortCode configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Theme Hook Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#custom-theme-hook-settings">
                        <i class="fas fa-plug me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('Theme Hook Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Theme Hook configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Buy Now Button Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#buy-now-settings">
                        <i class="fas fa-shopping-cart me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('Buy Now Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Configure button behavior', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Image Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#image-settings">
                        <i class="fas fa-image me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('Image Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Product image configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Review Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#review-settings">
                        <i class="fas fa-star me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('Review Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Amazon review configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- No API Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#no-api-settings">
                        <i class="fas fa-ban me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('No API Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('No API configuration options', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- API Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#api-settings">
                        <i class="fas fa-cog me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('API Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('API configuration options', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>

                    <!-- Proxy Settings -->
                    <a class="list-group-item list-group-item-action d-flex align-items-center py-3" href="#proxy-settings">
                        <i class="fas fa-shield-alt me-3 text-primary"></i>
                        <div>
                            <strong class="d-block mb-1"><?php esc_html_e('Proxy Settings', 'ams-wc-amazon'); ?></strong>
                            <small class="text-muted"><?php esc_html_e('Proxy service configuration', 'ams-wc-amazon'); ?></small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const autoCloseSwitch = document.getElementById('autoCloseSwitch');
    
    // Load saved preference from localStorage
    const savedAutoClose = localStorage.getItem('sidebarAutoClose');
    autoCloseSwitch.checked = savedAutoClose === null ? true : savedAutoClose === 'true';
    
    // Save preference when changed
    autoCloseSwitch.addEventListener('change', function() {
        localStorage.setItem('sidebarAutoClose', this.checked);
    });
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            // Remove active class from all items
            document.querySelectorAll('.list-group-item').forEach(item => {
                item.classList.remove('active', 'bg-light', 'text-primary');
            });
            
            // Add active state using Bootstrap classes
            this.classList.add('active', 'bg-light', 'text-primary');
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
                
                if (autoCloseSwitch.checked) {
                    const sidebar = bootstrap.Offcanvas.getInstance(document.getElementById('settingsSidebar'));
                    if (sidebar) {
                        sidebar.hide();
                    }
                }
            }
        });
    });
});
</script>



<div class="container-fluid">
    <div class="setting_wrapper text-left">
          <div class="tab-content" id="pills-tabContent">

<div class="tab-pane fade <?= ( !isset($_GET['tab']) || $_GET['tab'] == "pills-general-tab")?'show active':''; ?> " id="pills-general" role="tabpanel" aria-labelledby="pills-general-tab">
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data">
        <div class="setting_inner">

            <!--General Import Settings section-->
            <?php
            $enable_no_follow_link = get_option('ams_enable_no_follow_link', 'follow');
            $is_no_follow_enabled = ($enable_no_follow_link === 'nofollow');

            $remove_unavailable_products = $this->get_option('ams_remove_unavailable_products', 'No');
            $is_remove_enabled = ($remove_unavailable_products === 'Yes');

            $fast_product_importer = $this->get_option('ams_fast_product_importer', 'No');
            $is_fast_import_enabled = ($fast_product_importer === 'Yes');
            ?>

            <div class="card shadow-sm mb-4" id="general-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('General Import Settings', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">

                    <div class="mb-4">
                        <?php
                        $options = [
                            'no_follow' => [
                                'title' => __('No Follow Link', 'ams-wc-amazon'),
                                'description' => __('Add "nofollow" attribute to external links', 'ams-wc-amazon'),
                                'icon' => 'fas fa-link',
                                'name' => 'enable_no_follow_link',
                                'value' => 'nofollow',
                                'checked' => $is_no_follow_enabled
                            ],
                            'remove_unavailable' => [
                                'title' => __('Remove Unavailable/Zero Price Products', 'ams-wc-amazon'),
                                'description' => __('Remove products that are unavailable or have zero price from your store', 'ams-wc-amazon'),
                                'icon' => 'fas fa-trash-alt',
                                'name' => 'remove_unavailable_products',
                                'value' => 'Yes',
                                'checked' => $is_remove_enabled
                            ],
                            'fast_import' => [
                                'title' => __('Enable Fast Import', 'ams-wc-amazon'),
                                'description' => __('Speed up product import process', 'ams-wc-amazon'),
                                'icon' => 'fas fa-bolt',
                                'name' => 'fast_product_importer',
                                'value' => 'Yes',
                                'checked' => $is_fast_import_enabled
                            ]
                        ];

                        foreach ($options as $key => $option) :
                            $isChecked = $option['checked'] ? 'checked' : '';
                        ?>
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">
                                        <i class="<?php echo esc_attr($option['icon']); ?> me-2"></i>
                                        <?php echo esc_html($option['title']); ?>
                                    </h5>
                                    <p class="mb-0 text-muted small"><?php echo esc_html($option['description']); ?></p>
                                </div>
                                <div class="ms-3">
                                    <label class="switch">
                                        <input type="checkbox" name="<?php echo esc_attr($option['name']); ?>" value="<?php echo esc_attr($option['value']); ?>" <?php echo $isChecked; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>


                    <!-- Product Import Default Category -->
                    <div class="mb-4">
                       <label for="settings_ams_default_category" class="form-label text-primary fw-bold d-flex align-items-center">
                           <i class="fas fa-list-alt me-2"></i>
                           <?php esc_html_e('Default Category', 'ams-wc-amazon'); ?>
                       </label>
                       <div class="d-flex align-items-center mb-2">
                           <div class="dropdown">
                               <button class="btn btn-outline-primary btn-sm dropdown-toggle w-auto text-start" type="button" id="dropdownCategoryButton" data-bs-toggle="dropdown" aria-expanded="false">
                                   <span id="selectedCategoryLabel">
                                       <?php
                                       $saved_category = $this->get_option('ams_default_category', '_auto_import_amazon');
                                       echo $saved_category === '_auto_import_amazon'
                                           ? esc_html__('Auto Import From Amazon', 'ams-wc-amazon')
                                           : esc_html($saved_category);
                                       ?>
                                   </span>
                               </button>
                               <ul class="dropdown-menu w-auto" aria-labelledby="dropdownCategoryButton" style="max-height: 300px; overflow-y: auto;">
                                   <!-- Search Bar -->
                                   <li class="px-3 py-2">
                                       <input type="text" id="categorySearch" class="form-control form-control-sm" placeholder="<?php esc_attr_e('Search categories...', 'ams-wc-amazon'); ?>">
                                   </li>
                                   <li>
                                       <hr class="dropdown-divider">
                                   </li>
                                   <!-- Categories -->
                                   <li>
                                       <label class="dropdown-item">
                                           <input type="radio" name="ams_default_category" value="_auto_import_amazon" <?php checked($saved_category, "_auto_import_amazon"); ?> class="category-item">
                                           <?php esc_html_e('Auto Import From Amazon', 'ams-wc-amazon'); ?>
                                       </label>
                                   </li>
                                   <?php foreach ($this->get_wc_terms() as $value) : ?>
                                       <li>
                                           <label class="dropdown-item">
                                               <input type="radio" name="ams_default_category" value="<?php echo esc_attr($value['name']); ?>" <?php checked($saved_category, $value['name']); ?> class="category-item">
                                               <?php echo esc_html($value['name']); ?>
                                           </label>
                                       </li>
                                   <?php endforeach; ?>
                               </ul>
                           </div>
                           <div class="ms-3 d-flex align-items-center">
                               <?php $category_updates_enabled = get_option('product_category_cron', true); ?>
                               <div class="d-flex flex-column">
                                   <label class="form-label small mb-0 text-muted">
                                       <?php esc_html_e('Category Updates', 'ams-wc-amazon'); ?>
                                   </label>
                                   <div class="d-flex align-items-center">
                                       <label class="switch" style="transform: scale(0.7);">
                                           <input type="checkbox" 
                                               name="product_category_cron" 
                                               value="1" 
                                               <?php checked($category_updates_enabled, 1); ?>>
                                           <span class="slider round"></span>
                                       </label>
                                       <span class="ms-1 small <?php echo $category_updates_enabled ? 'text-success' : 'text-danger'; ?>">
                                           <?php echo $category_updates_enabled 
                                               ? esc_html__('Enabled', 'ams-wc-amazon') 
                                               : esc_html__('Disabled', 'ams-wc-amazon'); ?>
                                       </span>
                                   </div>
                               </div>
                           </div>
                       </div>
                       <p class="text-muted small">
                           <?php esc_html_e('Select default category for imports. When updates are enabled, categories will be updated during scheduled updates.', 'ams-wc-amazon'); ?>
                       </p>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const categoryItems = document.querySelectorAll('.category-item');
                            const selectedCategoryLabel = document.getElementById('selectedCategoryLabel');
                            const categorySearch = document.getElementById('categorySearch');
                            const categoryListItems = document.querySelectorAll('.dropdown-item');

                            // Update the selected category dynamically
                            categoryItems.forEach(item => {
                                item.addEventListener('change', function () {
                                    const selectedText = this.parentNode.textContent.trim();
                                    selectedCategoryLabel.textContent = selectedText;
                                });
                            });

                            // Filter categories dynamically
                            categorySearch.addEventListener('input', function () {
                                const query = this.value.toLowerCase();
                                categoryListItems.forEach(item => {
                                    const text = item.textContent.toLowerCase();
                                    item.style.display = text.includes(query) ? 'block' : 'none';
                                });
                            });
                        });
                    </script>
                    <!-- Product Import Default Category -->



                    <!-- Product Import Default Status -->
                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center text-primary fw-bold">
                            <i class="fas fa-tasks me-2"></i>
                            <?php esc_html_e('Set Default Product Status', 'ams-wc-amazon'); ?>
                        </label>
                        <p class="text-muted small mb-2">
                            <?php esc_html_e('Choose the default status for imported products.', 'ams-wc-amazon'); ?>
                        </p>
                        <div class="btn-group" role="group">
                            <!-- Publish -->
                            <input type="radio" class="btn-check" name="ams_product_import_status" id="product_status_publish" value="publish" <?php checked($this->get_option('ams_product_import_status'), "publish"); ?>>
                            <label class="btn btn-outline-primary btn-sm" for="product_status_publish">
                                <i class="fas fa-check-circle me-2"></i><?php esc_html_e('Publish', 'ams-wc-amazon'); ?>
                            </label>

                            <!-- Pending -->
                            <input type="radio" class="btn-check" name="ams_product_import_status" id="product_status_pending" value="pending" <?php checked($this->get_option('ams_product_import_status'), "pending"); ?>>
                            <label class="btn btn-outline-primary btn-sm" for="product_status_pending">
                                <i class="fas fa-clock me-2"></i><?php esc_html_e('Pending', 'ams-wc-amazon'); ?>
                            </label>

                            <!-- Draft -->
                            <input type="radio" class="btn-check" name="ams_product_import_status" id="product_status_draft" value="draft" <?php checked($this->get_option('ams_product_import_status'), "draft"); ?>>
                            <label class="btn btn-outline-primary btn-sm" for="product_status_draft">
                                <i class="fas fa-pencil-alt me-2"></i><?php esc_html_e('Draft', 'ams-wc-amazon'); ?>
                            </label>
                        </div>
                    </div>


                    <!-- Variation Import Limit -->
                        <?php
                        // Get the current max_execution_time from the server
                        $max_execution_time = ini_get('max_execution_time');
                        $variation_limit = $this->get_option('ams_variation_limit', 5);
                        ?>

                        <div class="mb-4">
                            <label for="variation_limit" class="form-label"><?php esc_html_e('Variation Import Limit', 'ams-wc-amazon'); ?></label>
                            <input type="number" class="form-control w-25" id="variation_limit" name="variation_limit" min="1" max="100" value="<?php echo esc_attr($variation_limit); ?>">
                            <div class="form-text">
                                <?php esc_html_e('Set the maximum number of variations to import. Default is 5.', 'ams-wc-amazon'); ?>
                            </div>

                            <!-- Badge Message (Initially Hidden or Visible Based on Initial Value) -->
                            <div id="executionTimeWarning" class="form-text fw-bold text-danger" style="display: <?php echo ($variation_limit > 10) ? 'block' : 'none'; ?>;">
                                <?php printf(esc_html__('The current max_execution_time is set to %d seconds. It is recommended to set it to 600 or higher for importing more than 10 variations.', 'ams-wc-amazon'), $max_execution_time); ?>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const variationLimitField = document.getElementById('variation_limit');
                                const warningElement = document.getElementById('executionTimeWarning');

                                // Function to check and display the warning based on the input value
                                function checkVariationLimit() {
                                    const value = parseInt(variationLimitField.value, 10);
                                    if (value > 10) {
                                        warningElement.style.display = 'block';
                                    } else {
                                        warningElement.style.display = 'none';
                                    }
                                }

                                // Trigger check on page load in case the value is already greater than 10
                                checkVariationLimit();

                                // Attach event listener for dynamic update when typing
                                variationLimitField.addEventListener('input', checkVariationLimit);
                            });
                        </script>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
                toggles.forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const card = this.closest('.d-flex');
                        if (this.checked) {
                            card.classList.add('text-primary');
                        } else {
                            card.classList.remove('text-primary');
                        }
                    });
                });
            });
            </script>
            <!--General Import Settings section-->

            <!-- Daily Cron & Cleanup Settings -->
            <div class="card shadow-sm mb-5" id="daily-cron-settings">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-cogs me-2"></i>
                        <?php esc_html_e('Automation & Cleanup Settings', 'ams-wc-amazon'); ?>
                    </h5>
                </div>

                <div class="card-body">

                    <!-- WordPress Cron -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="fas fa-clock me-1 text-secondary"></i>
                            <?php esc_html_e('WordPress Cron (Default)', 'ams-wc-amazon'); ?>
                        </h6>
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted small mb-0">
                                    <?php esc_html_e('Enable or disable WP Cron-based daily jobs. Works with WP Crontrol.', 'ams-wc-amazon'); ?>
                                </p>
                            </div>
                            <div>
                                <label class="switch">
                                    <input type="checkbox" name="enable_daily_cron" value="1" <?php checked(get_option('enable_daily_cron'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="alert mt-3 <?php echo wp_next_scheduled('ams_daily_cron_event') ? 'alert-success' : 'alert-danger'; ?>">
                            <strong>
                                <?php echo wp_next_scheduled('ams_daily_cron_event')
                                    ? esc_html__('Cron job is scheduled via WordPress.', 'ams-wc-amazon')
                                    : esc_html__('Cron job is NOT scheduled via WordPress.', 'ams-wc-amazon'); ?>
                            </strong>
                        </div>
                        <a href="https://wordpress.org/plugins/wp-crontrol/" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <?php esc_html_e('Download WP Crontrol Plugin', 'ams-wc-amazon'); ?>
                        </a>
                    </div>

                    <hr class="my-4">

                    <!-- Server-Side Cron -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="fas fa-server me-1 text-secondary"></i>
                            <?php esc_html_e('Server-Side Cron (Advanced)', 'ams-wc-amazon'); ?>
                        </h6>
                        <p class="text-muted small">
                            <?php esc_html_e('Use a server-level cron for better reliability. Click below for setup instructions.', 'ams-wc-amazon'); ?>
                        </p>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#cronGuideModal">
                            <?php esc_html_e('Server-Side Cron Guide', 'ams-wc-amazon'); ?>
                        </button>
                    </div>

                    <hr class="my-4">

                    <!-- Cleanup Options -->
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="fas fa-broom me-1 text-secondary"></i>
                        <?php esc_html_e('Automatic Cleanup Options', 'ams-wc-amazon'); ?>
                    </h6>

                    <!-- Clean Completed Actions -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-0">
                                <?php esc_html_e('Delete completed WooCommerce Action Scheduler tasks automatically.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="enable_clean_completed_actions" value="1" <?php checked(get_option('enable_clean_completed_actions'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Clean Logs -->
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-0">
                                <?php esc_html_e('Truncate Action Scheduler logs to reduce database size.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="enable_clean_action_logs" value="1" <?php checked(get_option('enable_clean_action_logs'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!--AMSWOO Settings-->
            <div class="card shadow-lg rounded-lg mb-4" id="amswoo-settings">
                <!-- Header -->
                <div class="card-header bg-light border-bottom p-4">
                    <h3 class="m-0 d-flex align-items-center">
                        <i class="bi bi-gear-fill me-2 text-primary"></i>
                        <?php esc_html_e('AMSWOO Settings', 'ams-wc-amazon'); ?>
                    </h3>
                </div>

                <div class="card-body">
                    <!-- Data Cleanup Section -->
                    <div class="settings-section mb-4 pb-4 border-bottom">
                        <div class="section-header d-flex align-items-center mb-3">
                            <i class="bi bi-trash text-danger me-2 fs-4"></i>
                            <h4 class="m-0"><?php esc_html_e('Data Cleanup', 'ams-wc-amazon'); ?></h4>
                        </div>

                        <div class="bg-light p-4 rounded-3">
                            <div class="mb-3">
                                <button id="delete-amswoofiu-data" class="btn btn-danger d-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                    <i class="bi bi-trash me-2"></i>
                                    <span class="button-text"><?php esc_html_e('Delete AMSWOO Data', 'ams-wc-amazon'); ?></span>
                                </button>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong><?php esc_html_e('Warning:', 'ams-wc-amazon'); ?></strong>
                                <p class="mb-2"><?php esc_html_e('This will delete:', 'ams-wc-amazon'); ?></p>
                                <ol class="ps-3 mb-0">
                                    <li><?php esc_html_e('All product image URLs (_thumbnail_id_url, _amswoofiu_wcgallary, _amswoofiu_url, _thumbnail_ext_url)', 'ams-wc-amazon'); ?></li>
                                    <li><?php esc_html_e('All uploaded product images and their variations from Media Library', 'ams-wc-amazon'); ?></li>
                                    <li><?php esc_html_e('All related image metadata', 'ams-wc-amazon'); ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Unlinked Variants Cleanup Section -->
                    <div class="settings-section mb-4 pb-4 border-bottom">
                        <div class="section-header d-flex align-items-center mb-3">
                            <i class="bi bi-eye-slash text-warning me-2 fs-4"></i>
                            <h4 class="m-0"><?php esc_html_e('Unlinked Variants/Products', 'ams-wc-amazon'); ?></h4>
                        </div>

                        <div class="bg-light p-4 rounded-3">
                            <?php
                            // Fetch unlinked variants
                            $unlinked_variants = get_unlinked_variants();

                            if (!empty($unlinked_variants)) {
                                echo '<table class="table table-bordered mb-3">';
                                echo '<thead><tr>';
                                echo '<th>' . esc_html__('SKU', 'ams-wc-amazon') . '</th>';
                                echo '<th>' . esc_html__('Post ID', 'ams-wc-amazon') . '</th>';
                                echo '</tr></thead>';
                                echo '<tbody>';
                                foreach ($unlinked_variants as $variant) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($variant->sku) . '</td>';
                                    echo '<td>' . esc_html($variant->post_id) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo '<p class="text-muted">' . esc_html__('No unlinked variants/products found.', 'ams-wc-amazon') . '</p>';
                            }
                            ?>

                            <div class="mb-3">
                                <button id="delete-unlinked-variants" class="btn btn-danger d-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                                    <i class="bi bi-trash me-2"></i>
                                    <span class="button-text"><?php esc_html_e('Clean Up Unlinked Variants', 'ams-wc-amazon'); ?></span>
                                </button>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong><?php esc_html_e('Warning:', 'ams-wc-amazon'); ?></strong>
                                <p class="mb-2"><?php esc_html_e('This action will permanently delete all SKUs that are not linked to any valid WooCommerce products or variations.', 'ams-wc-amazon'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Variation Image Settings -->
                    <div class="settings-section mb-4 pb-4 border-bottom">
                        <div class="section-header d-flex align-items-center mb-3">
                            <i class="bi bi-image text-primary me-2 fs-4"></i>
                            <h4 class="m-0"><?php esc_html_e('Variation meta-key for custom Themes', 'ams-wc-amazon'); ?></h4>
                        </div>

                        <div class="bg-light p-4 rounded-3">
                            <div class="mb-3">
                                <label for="variation_image_meta_key" class="form-label fw-bold">
                                    <?php esc_html_e('Custom Theme Variation Image Meta Key', 'ams-wc-amazon'); ?>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-key"></i>
                                    </span>
                                    <input type="text" 
                                           id="variation_image_meta_key" 
                                           name="variation_image_meta_key"
                                           class="form-control"
                                           placeholder="<?php esc_attr_e('Leave empty for default WooCommerce behavior', 'ams-wc-amazon'); ?>"
                                           value="<?php echo esc_attr(get_option('variation_image_meta_key', '')); ?>" />
                                </div>
                            </div>

                            <div class="alert alert-info mb-3">
                                <div class="d-flex mb-2">
                                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                    <strong><?php esc_html_e('Important Information:', 'ams-wc-amazon'); ?></strong>
                                </div>
                                <ul class="mb-0 ps-3">
                                    <li><?php esc_html_e('Only required for custom themes using different meta keys', 'ams-wc-amazon'); ?></li>
                                    <li><?php esc_html_e('Leave empty if using default WooCommerce setup', 'ams-wc-amazon'); ?></li>
                                    <li><?php esc_html_e('Consult your theme developer for the correct meta key', 'ams-wc-amazon'); ?></li>
                                </ul>
                            </div>

                            <div class="text-muted">
                                <i class="bi bi-lightbulb me-2"></i>
                                <small><?php esc_html_e('Example: Some custom themes use "theme_variation_images" as their meta key.', 'ams-wc-amazon'); ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Page Speed Test Settings -->
                    <div class="settings-section mb-4 pb-4 border-bottom">
                        <div class="section-header d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-speedometer2 text-primary me-2 fs-4"></i>
                                <h4 class="m-0"><?php esc_html_e('Page Speed Test Settings', 'ams-wc-amazon'); ?></h4>
                            </div>
                            <!-- Original Switch Button Exactly as Provided -->
                            <div class="ms-3">
                                <label class="switch">
                                    <input type="checkbox" id="enable_page_speed_test" 
                                           name="enable_page_speed_test" value="1" 
                                           <?php checked(get_option('enable_page_speed_test'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="bg-light p-4 rounded-3">
                            <div class="mb-3">
                                <label for="page_speed_test_style" class="form-label fw-bold">
                                    <?php esc_html_e('Select Style', 'ams-wc-amazon'); ?>
                                </label>
                                <select class="form-select" 
                                        id="page_speed_test_style" 
                                        name="page_speed_test_style">
                                    <option value="style1" <?php selected(get_option('page_speed_test_style'), 'style1'); ?>>
                                        <?php esc_html_e('Style 1 (Dark)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style2" <?php selected(get_option('page_speed_test_style'), 'style2'); ?>>
                                        <?php esc_html_e('Style 2 (Light)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style3" <?php selected(get_option('page_speed_test_style'), 'style3'); ?>>
                                        <?php esc_html_e('Style 3 (Compact)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style4" <?php selected(get_option('page_speed_test_style'), 'style4'); ?>>
                                        <?php esc_html_e('Style 4 (Large)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style5" <?php selected(get_option('page_speed_test_style'), 'style5'); ?>>
                                        <?php esc_html_e('Style 5 (Custom)', 'ams-wc-amazon'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Last Updated Date Settings -->
                    <div class="settings-section mb-4 pb-4 border-bottom">
                        <div class="section-header d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar-check text-primary me-2 fs-4"></i>
                                <h4 class="m-0"><?php esc_html_e('Last Updated Date Settings', 'ams-wc-amazon'); ?></h4>
                            </div>
                            <div class="ms-3">
                                <label class="switch">
                                    <input type="checkbox" id="enable_last_updated_date" 
                                           name="enable_last_updated_date" value="1" 
                                           <?php checked(get_option('enable_last_updated_date'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="bg-light p-4 rounded-3">
                            <p class="text-muted mb-2">
                                <?php esc_html_e('Enable or disable the display of the "Last Updated Date" for products on the front end.', 'ams-wc-amazon'); ?>
                            </p>

                            <!-- Enable/Disable Custom Notice -->
                            <div class="d-flex align-items-center mb-3">
                                <label for="enable_custom_message" class="form-label me-2 fw-bold">
                                    <?php esc_html_e('Enable Custom Notice Message', 'ams-wc-amazon'); ?>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" id="enable_custom_message" 
                                           name="enable_custom_message" value="1" 
                                           <?php checked(get_option('enable_custom_message'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            <!-- Custom Notice Message -->
                            <div class="mb-3">
                                <label for="last_updated_custom_message" class="form-label fw-bold">
                                    <?php esc_html_e('Custom Notice Message', 'ams-wc-amazon'); ?>
                                </label>
                                <textarea id="last_updated_custom_message" 
                                          name="last_updated_custom_message" 
                                          class="form-control" 
                                          rows="3"><?php 
                                              echo esc_html(get_option('last_updated_custom_message', 'Important Notice: Product details may change. Please check regularly for updates.')); 
                                          ?></textarea>
                            </div>
                            <p class="text-muted">
                                <?php esc_html_e('This message will be displayed above the "Last Updated On" date. Leave blank to use the default message.', 'ams-wc-amazon'); ?>
                            </p>

                            <!-- Notice Style -->
                            <div class="mb-3">
                                <label for="last_updated_notice_style" class="form-label fw-bold">
                                    <?php esc_html_e('Select Notice Style', 'ams-wc-amazon'); ?>
                                </label>
                                <select id="last_updated_notice_style" 
                                        name="last_updated_notice_style" 
                                        class="form-select">
                                    <option value="style1" <?php selected(get_option('last_updated_notice_style'), 'style1'); ?>>
                                        <?php esc_html_e('Style 1 (Default)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style2" <?php selected(get_option('last_updated_notice_style'), 'style2'); ?>>
                                        <?php esc_html_e('Style 2 (Alert)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style3" <?php selected(get_option('last_updated_notice_style'), 'style3'); ?>>
                                        <?php esc_html_e('Style 3 (Success)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style4" <?php selected(get_option('last_updated_notice_style'), 'style4'); ?>>
                                        <?php esc_html_e('Style 4 (Warning)', 'ams-wc-amazon'); ?>
                                    </option>
                                    <option value="style5" <?php selected(get_option('last_updated_notice_style'), 'style5'); ?>>
                                        <?php esc_html_e('Style 5 (Modern)', 'ams-wc-amazon'); ?>
                                    </option>
                                </select>
                            </div>
                            <p class="text-muted">
                                <?php esc_html_e('Choose a predefined style for the custom notice message displayed on the product page.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
            <!--End AMSWOO Settings-->

             <!-- Unlinked Variants Cleanup js -->
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                $('#delete-unlinked-variants').on('click', function (e) {
                    e.preventDefault();
                    var $button = $(this);
                    var $spinner = $button.find('.spinner-border');
                    var $buttonText = $button.find('.button-text');

                    if (confirm('<?php esc_html_e('Are you sure you want to clean up all unlinked variants?', 'ams-wc-amazon'); ?>')) {
                        // Disable button and show spinner
                        $button.prop('disabled', true);
                        $spinner.removeClass('d-none');
                        $buttonText.text('<?php esc_html_e('Cleaning...', 'ams-wc-amazon'); ?>');

                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'delete_unlinked_variants_cleanup'
                            },
                            success: function (response) {
                                $button.prop('disabled', false);
                                $spinner.addClass('d-none');
                                $buttonText.text('<?php esc_html_e('Clean Up Unlinked Variants', 'ams-wc-amazon'); ?>');
                                if (response.success) {
                                    alert('<?php esc_html_e('Unlinked variants cleaned up successfully!', 'ams-wc-amazon'); ?>' + '\n' +
                                        '<?php esc_html_e('SKUs deleted: ', 'ams-wc-amazon'); ?>' + response.data.deleted_count);
                                    location.reload();
                                } else {
                                    alert('<?php esc_html_e('There was an error cleaning up unlinked variants.', 'ams-wc-amazon'); ?>');
                                }
                            },
                            error: function () {
                                $button.prop('disabled', false);
                                $spinner.addClass('d-none');
                                $buttonText.text('<?php esc_html_e('Clean Up Unlinked Variants', 'ams-wc-amazon'); ?>');

                                alert('<?php esc_html_e('There was an issue with the request.', 'ams-wc-amazon'); ?>');
                            }
                        });
                    }
                });
            });
            </script>
             <!-- Unlinked Variants Cleanup js -->


            <!-- AMSWOO Data Cleanup section -->
            <script type="text/javascript">
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                jQuery(document).ready(function($) {
                    $('#delete-amswoofiu-data').on('click', function(e) {
                        e.preventDefault();
                        var $button = $(this);
                        var $spinner = $button.find('.spinner-border');
                        var $buttonText = $button.find('.button-text');

                        if (confirm('<?php esc_html_e('Are you sure you want to delete all AMSWOO data?', 'ams-wc-amazon'); ?>')) {
                            // Disable button and show spinner
                            $button.prop('disabled', true);
                            $spinner.removeClass('d-none');
                            $buttonText.text('<?php esc_html_e('Deleting...', 'ams-wc-amazon'); ?>');

                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'delete_amswoofiu_data_cleanup'
                                },
                                success: function(response) {
                                    $button.prop('disabled', false);
                                    $spinner.addClass('d-none');
                                    $buttonText.text('<?php esc_html_e('Delete AMSWOO Data', 'ams-wc-amazon'); ?>');
                                    if (response.success) {
                                        var urls_deleted = response.data.urls_deleted;
                                        var images_deleted = response.data.images_deleted;
                                        alert('<?php esc_html_e('AMSWOO data deleted successfully!', 'ams-wc-amazon'); ?>' + '\n' +
                                            '<?php esc_html_e('URLs deleted: ', 'ams-wc-amazon'); ?>' + urls_deleted + '\n' +
                                            '<?php esc_html_e('Images deleted: ', 'ams-wc-amazon'); ?>' + images_deleted);
                                        location.reload();
                                    } else {
                                        alert('<?php esc_html_e('There was an error deleting AMSWOO data.', 'ams-wc-amazon'); ?>');
                                    }
                                },
                                error: function() {
                                    // Re-enable button and hide spinner on error
                                    $button.prop('disabled', false);
                                    $spinner.addClass('d-none');
                                    $buttonText.text('<?php esc_html_e('Delete AMSWOO Data', 'ams-wc-amazon'); ?>');

                                    alert('<?php esc_html_e('There was an issue with the request.', 'ams-wc-amazon'); ?>');
                                }
                            });
                        }
                    });
                });
            </script>
            <!-- AMSWOO Data Cleanup section -->


            <!--WooCommerce Shortcode Settings-->
            <div class="card shadow-lg rounded-lg mb-4" id="shortcode-settings">
                <div class="bg-light p-4 rounded-3">
                    <!-- Title -->
                    <h2 class="fw-bold text-primary mb-3"><?php esc_html_e('WooCommerce Shortcode Settings', 'ams-wc-amazon'); ?></h2>

                    <!-- Shortcode Examples Section -->
                    <div class="mb-4 p-3 bg-white border rounded shadow-sm">
                        <h3 class="fw-bold text-info"><?php esc_html_e('How to Use Shortcodes', 'ams-wc-amazon'); ?></h3>
                        <p class="text-muted">
                            <?php esc_html_e('Use the following shortcodes to display WooCommerce products on your WordPress posts or pages:', 'ams-wc-amazon'); ?>
                        </p>
                        <div class="bg-dark text-white p-3 mb-3 rounded shadow-sm">
                            <strong>[ams_display_products ids="123" layout="single"]</strong>
                            <p class="mb-0 text-light">
                                <?php esc_html_e('Displays a single product. Replace 123 with the product ID.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>
                        <div class="bg-dark text-white p-3 rounded shadow-sm">
                            <strong>[ams_display_products ids="123,456,789" columns="3" layout="grid"]</strong>
                            <p class="mb-0 text-light">
                                <?php esc_html_e('Displays multiple products in a grid layout. Replace 123, 456, 789 with your product IDs.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>

                        <!-- Auto Create Shortcode Pages/Posts Section -->
                        <div class="mt-4 p-3 bg-white border rounded shadow-sm">
                            <h3 class="fw-bold text-info"><?php esc_html_e('Auto Create Shortcode Pages/Posts', 'ams-wc-amazon'); ?></h3>
                            <p class="text-muted">
                                <?php esc_html_e('Automatically create pages or posts for the shortcode examples.', 'ams-wc-amazon'); ?>
                            </p>

                            <!-- Page/Post Selector -->
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="create_type" id="create_page" value="page" checked>
                                <label class="form-check-label" for="create_page"><?php esc_html_e('Page', 'ams-wc-amazon'); ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="create_type" id="create_post" value="post">
                                <label class="form-check-label" for="create_post"><?php esc_html_e('Post', 'ams-wc-amazon'); ?></label>
                            </div>

                            <!-- Create and Delete Buttons -->
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" id="autoCreatePagesButton">
                                    <i class="fas fa-plus me-2"></i><?php esc_html_e('Create Pages/Posts', 'ams-wc-amazon'); ?>
                                </button>
                                <button type="button" class="btn btn-danger ms-2" id="deletePagesButton">
                                    <i class="fas fa-trash-alt me-2"></i><?php esc_html_e('Delete Pages/Posts', 'ams-wc-amazon'); ?>
                                </button>
                            </div>

                            <!-- Success/Failure Messages -->
                            <div id="autoCreateMessage" class="mt-3"></div>
                        </div>
                    </div>


                    <!-- Finding Product IDs Section -->
                    <div class="mb-4 p-3 bg-white border rounded shadow-sm">
                        <h3 class="fw-bold text-info"><?php esc_html_e('Finding Product IDs', 'ams-wc-amazon'); ?></h3>
                        <p class="text-muted mb-2">
                            <?php esc_html_e('You can find product IDs in the WooCommerce Products page of your WordPress admin dashboard.', 'ams-wc-amazon'); ?>
                        </p>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <?php esc_html_e('1. Navigate to the "Products" section in the WordPress admin dashboard.', 'ams-wc-amazon'); ?>
                            </li>
                            <li class="list-group-item">
                                <?php esc_html_e('2. Hover over the product name to see its ID appear beneath the name.', 'ams-wc-amazon'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Display Settings Section -->
                    <div class="mb-4 p-3 bg-white border rounded shadow-sm">
                        <h3 class="fw-bold text-info"><?php esc_html_e('Display Settings', 'ams-wc-amazon'); ?></h3>

                        <!-- Enable/Disable Legal Notice -->
                        <div class="d-flex align-items-center mb-3">
                            <label for="enable_legal_notice" class="form-label me-3 fw-bold text-dark">
                                <?php esc_html_e('Enable Legal Notice', 'ams-wc-amazon'); ?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" id="enable_legal_notice" 
                                       name="enable_legal_notice" value="1" 
                                       <?php checked(get_option('enable_legal_notice'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <!-- Editable Legal Notice -->
                        <div class="mb-4">
                            <label for="legal_notice_text" class="form-label fw-bold text-dark">
                                <?php esc_html_e('Legal Notice Text', 'ams-wc-amazon'); ?>
                            </label>
                            <textarea id="legal_notice_text" 
                                      name="legal_notice_text" 
                                      class="form-control rounded" 
                                      rows="2"><?php 
                                          echo esc_html(get_option('legal_notice_text', 'Affiliate Products | Advertisement | Sponsored')); 
                                      ?></textarea>
                            <p class="text-muted mt-1">
                                <?php esc_html_e('Customize the legal notice text displayed above the products.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>

                        <!-- Enable/Disable Per-Product Last Updated Message -->
                        <div class="d-flex align-items-center mb-3">
                            <label for="enable_product_last_updated" class="form-label me-3 fw-bold text-dark">
                                <?php esc_html_e('Enable Per-Product Last Updated Message', 'ams-wc-amazon'); ?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" id="enable_product_last_updated" 
                                       name="enable_product_last_updated" value="1" 
                                       <?php checked(get_option('enable_product_last_updated'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <!-- Editable Message for Per-Product Last Updated -->
                        <div class="mb-4">
                            <label for="product_last_updated_message" class="form-label fw-bold text-dark">
                                <?php esc_html_e('Per-Product Last Updated Message', 'ams-wc-amazon'); ?>
                            </label>
                            <textarea id="product_last_updated_message" 
                                      name="product_last_updated_message" 
                                      class="form-control rounded" 
                                      rows="2"><?php 
                                          echo esc_html(get_option('product_last_updated_message', 'Last updated: {date}')); 
                                      ?></textarea>
                            <p class="text-muted mt-1">
                                <?php esc_html_e('Use {date} as a placeholder for the date and time.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>

                        <!-- Enable/Disable Global Last Updated Message -->
                        <div class="d-flex align-items-center mb-3">
                            <label for="enable_global_last_updated" class="form-label me-3 fw-bold text-dark">
                                <?php esc_html_e('Enable Global Last Updated Message', 'ams-wc-amazon'); ?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" id="enable_global_last_updated" 
                                       name="enable_global_last_updated" value="1" 
                                       <?php checked(get_option('enable_global_last_updated'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <!-- Editable Global Last Updated Message -->
                        <div class="mb-4">
                            <label for="global_last_updated_message" class="form-label fw-bold text-dark">
                                <?php esc_html_e('Global Last Updated Message', 'ams-wc-amazon'); ?>
                            </label>
                            <textarea id="global_last_updated_message" 
                                      name="global_last_updated_message" 
                                      class="form-control rounded" 
                                      rows="2"><?php 
                                          echo esc_html(get_option('global_last_updated_message', 'Last updated on {date}.')); 
                                      ?></textarea>
                            <p class="text-muted mt-1">
                                <?php esc_html_e('Use {date} as a placeholder for the date and time.', 'ams-wc-amazon'); ?>
                            </p>
                        </div>

                        <!-- Enable/Disable Custom Notification -->
                        <div class="d-flex align-items-center mb-3">
                            <label for="enable_custom_notification" class="form-label me-3 fw-bold text-dark">
                                <?php esc_html_e('Enable Custom Notification', 'ams-wc-amazon'); ?>
                            </label>
                            <label class="switch">
                                <input type="checkbox" id="enable_custom_notification" 
                                       name="enable_custom_notification" value="1" 
                                       <?php checked(get_option('enable_custom_notification'), '1'); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <!-- Editable Custom Notification Message -->
                        <div class="mb-4">
                            <label for="custom_notification_message" class="form-label fw-bold text-dark">
                                <?php esc_html_e('Custom Notification Message', 'ams-wc-amazon'); ?>
                            </label>
                            <textarea id="custom_notification_message" 
                                      name="custom_notification_message" 
                                      class="form-control rounded" 
                                      rows="3"><?php 
                                          echo esc_html(get_option('custom_notification_message', 'Please note: Product details may change.')); 
                                      ?></textarea>
                        </div>

                        <!-- Alignment Options -->
                        <div class="mb-4">
                            <label for="message_alignment" class="form-label fw-bold text-dark">
                                <?php esc_html_e('Message Alignment', 'ams-wc-amazon'); ?>
                            </label>
                            <select id="message_alignment" 
                                    name="message_alignment" 
                                    class="form-select rounded">
                                <option value="left" <?php selected(get_option('message_alignment'), 'left'); ?>>
                                    <?php esc_html_e('Left', 'ams-wc-amazon'); ?>
                                </option>
                                <option value="right" <?php selected(get_option('message_alignment'), 'right'); ?>>
                                    <?php esc_html_e('Right', 'ams-wc-amazon'); ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.getElementById('autoCreatePagesButton').addEventListener('click', () => {
                    const createType = document.querySelector('input[name="create_type"]:checked').value;
                    const nonce = '<?php echo wp_create_nonce('auto_create_pages_nonce'); ?>';

                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=auto_create_pages&type=${createType}&_ajax_nonce=${nonce}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        const message = document.getElementById('autoCreateMessage');
                        if (data.success) {
                            message.innerHTML = `<div class="alert alert-success">
                                <strong><?php esc_html_e('Success:', 'ams-wc-amazon'); ?></strong> 
                                <?php esc_html_e('Pages/Posts created successfully!', 'ams-wc-amazon'); ?>
                            </div>`;
                        } else {
                            message.innerHTML = `<div class="alert alert-danger">
                                <strong><?php esc_html_e('Error:', 'ams-wc-amazon'); ?></strong> 
                                ${data.data.message}
                            </div>`;
                        }
                    });
                });

                document.getElementById('deletePagesButton').addEventListener('click', () => {
                    const nonce = '<?php echo wp_create_nonce('delete_pages_nonce'); ?>';

                    fetch(ajaxurl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete_existing_pages&_ajax_nonce=${nonce}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        const message = document.getElementById('autoCreateMessage');
                        if (data.success) {
                            message.innerHTML = `<div class="alert alert-success">
                                <strong><?php esc_html_e('Success:', 'ams-wc-amazon'); ?></strong> 
                                <?php esc_html_e('Pages/Posts deleted successfully!', 'ams-wc-amazon'); ?>
                            </div>`;
                        } else {
                            message.innerHTML = `<div class="alert alert-danger">
                                <strong><?php esc_html_e('Error:', 'ams-wc-amazon'); ?></strong> 
                                ${data.data.message}
                            </div>`;
                        }
                    });
                });
            </script>
            <!--WooCommerce Shortcode Settings-->


            <!--Custom Theme Hook Settings-->
            <div class="card shadow-sm mb-4" id="custom-theme-hook-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('Custom Theme Hook Settings', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">
                    <!-- Enable Custom Theme Hook Toggle -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center">
                            <label class="form-label mb-0"><?php esc_html_e('Enable Custom Theme Hook', 'ams-wc-amazon'); ?></label>
                            <div class="ms-3">
                                <label class="switch">
                                    <input type="checkbox" 
                                           id="use_custom_button" 
                                           name="use_custom_button" 
                                           value="1" 
                                           <?php checked(get_option('ams_use_custom_button', '0'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Theme Hook Input -->
                    <div class="mb-4">
                        <label for="theme_hook" class="form-label"><?php esc_html_e('Custom Theme Hook Name', 'ams-wc-amazon'); ?></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="bi bi-code-slash"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="theme_hook" 
                                   name="theme_hook" 
                                   value="<?php echo esc_attr(get_option('ams_theme_hook', '')); ?>"
                                   placeholder="<?php esc_attr_e('Enter your theme\'s custom hook name', 'ams-wc-amazon'); ?>">
                        </div>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <?php esc_html_e('Enter the custom hook name for the "Buy Now" button on shop and category pages. Leave empty to use default WooCommerce layout.', 'ams-wc-amazon'); ?>
                        </small>
                    </div>

                    <!-- Information Alert -->
                    <div class="alert alert-info mb-0" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong><?php esc_html_e('Important:', 'ams-wc-amazon'); ?></strong>
                        <?php esc_html_e('These settings control the "Buy Now" button placement in your shop:', 'ams-wc-amazon'); ?>
                        <ul class="mt-2 mb-0">
                            <li><?php esc_html_e('Affects button display on shop pages and category listings', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Enable only if your theme replaces default WooCommerce button location', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Consult your theme documentation for the correct hook name', 'ams-wc-amazon'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!--Buy now button section-->
            <div class="card shadow-sm mb-4" id="buy-now-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('Buy Now Button Action', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">

                <!-- Buy Now Label -->
                <div class="mb-4">
                    <label for="buy_now_label" class="form-label"><?php esc_html_e('Buy Now Label', 'ams-wc-amazon'); ?></label>
                    <input type="text" class="form-control" id="buy_now_label" name="buy_now_label" placeholder="Buy on Amazon" value="<?php echo esc_attr($this->get_option('ams_buy_now_label')); ?>">
                </div>
                    <div class="mb-4">
                        <?php
                        $options = [
                            'redirect' => [
                                'title' => __('Direct Amazon Details Page', 'ams-wc-amazon'),
                                'description' => __('For Affiliate 24 hour cookie', 'ams-wc-amazon'),
                                'icon' => 'bi-box-arrow-up-right'
                            ],
                            'cart_page' => [
                                'title' => __('Direct Amazon Cart Page', 'ams-wc-amazon'),
                                'description' => __('For Affiliate 90 day cookie', 'ams-wc-amazon'),
                                'icon' => 'bi-cart'
                            ],
                            'multi_cart' => [
                                'title' => __('Site Cart Then Amazon', 'ams-wc-amazon'),
                                'description' => __('Add to site cart, redirect to Amazon on checkout', 'ams-wc-amazon'),
                                'icon' => 'bi-arrow-left-right',
                                'warning' => __('To use for multi countries you need to choose either the "Direct Amazon Details Page" or "Direct Amazon Cart Page"', 'ams-wc-amazon')
                            ],
                            'dropship' => [
                                'title' => __('Dropship', 'ams-wc-amazon'),
                                'description' => __('Add to site with DropShip fee, checkout on your site', 'ams-wc-amazon'),
                                'icon' => 'bi-shop'
                            ]
                        ];

                        foreach ($options as $value => $option) :
                            $isChecked = checked($this->get_option('ams_buy_action_btn'), $value, false);
                        ?>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="buy_action_btn" id="<?php echo esc_attr($value); ?>" value="<?php echo esc_attr($value); ?>" <?php echo $isChecked; ?>>
                                <label class="form-check-label" for="<?php echo esc_attr($value); ?>">
                                    <i class="bi <?php echo esc_attr($option['icon']); ?> me-2"></i>
                                    <?php echo esc_html($option['title']); ?>
                                    <small class="d-block text-muted mt-1"><?php echo esc_html($option['description']); ?></small>
                                </label>
                                <?php if (isset($option['warning'])) : ?>
                                    <div class="ams-warning mt-2 small text-warning <?php echo $isChecked ? '' : 'd-none'; ?>">
                                        <?php echo esc_html($option['warning']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Checkout Settings placed exactly under multi_cart option -->
                                <?php if ($value === 'multi_cart'): ?>
                                    <div id="checkout_settings" class="card shadow-sm mb-4 mt-3 <?php echo $this->get_option('ams_buy_action_btn') != 'multi_cart' ? 'd-none' : ''; ?>">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><?php esc_html_e('Site Cart & Redirect Amazon - Settings', 'ams-wc-amazon'); ?></h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- Checkout Message -->
                                            <div class="mb-3">
                                                <label for="checkout_mass_redirected" class="form-label"><?php esc_html_e('Checkout Message', 'ams-wc-amazon'); ?></label>
                                                <textarea 
                                                    class="form-control" 
                                                    id="checkout_mass_redirected" 
                                                    name="checkout_mass_redirected" 
                                                    rows="3"><?php echo esc_textarea(get_option('ams_checkout_mass_redirected')); ?></textarea>
                                                <small class="form-text text-muted">
                                                    <?php esc_html_e('Message displayed to users while redirecting to Amazon.', 'ams-wc-amazon'); ?>
                                                </small>
                                            </div>

                                            <!-- Checkout Redirect Timer -->
                                            <div class="mb-3">
                                                <label for="checkout_redirected_seconds" class="form-label"><?php esc_html_e('Checkout Redirect Time (seconds)', 'ams-wc-amazon'); ?></label>
                                                <div class="input-group">
                                                    <input 
                                                        type="number" 
                                                        class="form-control" 
                                                        id="checkout_redirected_seconds" 
                                                        name="checkout_redirected_seconds"  
                                                        value="<?php echo esc_attr(get_option('ams_checkout_redirected_seconds')); ?>"
                                                    >
                                                    <span class="input-group-text"><?php esc_html_e('seconds', 'ams-wc-amazon'); ?></span>
                                                </div>
                                                <small class="form-text text-muted">
                                                    <?php esc_html_e('Specify how many seconds to wait before redirecting to Amazon.', 'ams-wc-amazon'); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <!-- Dropship Percentage (Displayed only when 'dropship' is selected) -->
                        <div id="dropshipPercentage" class="card shadow-sm mb-4 <?php echo $this->get_option('ams_buy_action_btn') != 'dropship' ? 'd-none' : ''; ?>">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><?php esc_html_e('Dropship Settings', 'ams-wc-amazon'); ?></h5>
                            </div>
                            <div class="card-body">
                                <label for="percentage_profit" class="form-label"><?php esc_html_e('Custom Tax (% Percentage)', 'ams-wc-amazon'); ?></label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="percentage_profit" name="percentage_profit" value="<?php echo esc_attr(get_option('ams_percentage_profit')); ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="form-text text-muted">
                                    <?php esc_html_e('Percentage added to the price as profit amount for single product', 'ams-wc-amazon'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const buyActionBtns = document.querySelectorAll('input[name="buy_action_btn"]');
                    const dropshipPercentage = document.getElementById('dropshipPercentage');
                    const warnings = document.querySelectorAll('.ams-warning');
                    const checkoutSettings = document.getElementById('checkout_settings');

                    buyActionBtns.forEach(function(btn) {
                        btn.addEventListener('change', function() {
                            // Handle dropshipPercentage visibility
                            if (dropshipPercentage) {
                                dropshipPercentage.classList.toggle('d-none', this.value !== 'dropship');
                            }

                            // Handle warnings visibility
                            warnings.forEach(warning => warning.classList.add('d-none'));
                            const selectedWarning = this.parentElement.querySelector('.ams-warning');
                            if (selectedWarning) selectedWarning.classList.remove('d-none');

                            // Handle Checkout Settings visibility
                            if (checkoutSettings) {
                                checkoutSettings.classList.toggle('d-none', this.value !== 'multi_cart');
                            }
                        });
                    });
                });
            </script>
            <!--Buy now button section-->


            <!-- Images Section -->
            <div class="card shadow-sm mb-4" id="image-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('Amazon Product Image Settings', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Remote Amazon Images -->
                        <div class="col-md-6">
                            <label for="remote_amazon_images" class="form-label"><?php esc_html_e('Remote Amazon Images', 'ams-wc-amazon'); ?></label>
                            <select id="remote_amazon_images" name="remote_amazon_images" class="form-select">
                                <option value="Yes" <?php selected($this->get_option('ams_remote_amazon_images'), 'Yes', true); ?>><?php esc_html_e('Yes', 'ams-wc-amazon'); ?></option>
                                <option value="No" <?php selected($this->get_option('ams_remote_amazon_images'), 'No', true); ?>><?php esc_html_e('No', 'ams-wc-amazon'); ?></option>
                            </select>
                        </div>

                        <!-- Remote Image Sizes -->
                        <div class="col-md-6">
                            <label for="product_thumbnail_size" class="form-label"><?php esc_html_e('Select Remote Image Sizes', 'ams-wc-amazon'); ?></label>
                            <select id="product_thumbnail_size" name="product_thumbnail_size" class="form-select">
                                <option value="hd" <?php selected($this->get_option('ams_product_thumbnail_size'), 'hd', true); ?>><?php esc_html_e('HD (2048 X 2048)', 'ams-wc-amazon'); ?></option>
                                <option value="extra_large" <?php selected($this->get_option('ams_product_thumbnail_size'), 'extra_large', true); ?>><?php esc_html_e('Extra Large (1024 X 1024)', 'ams-wc-amazon'); ?></option>
                                <option value="Large" <?php selected($this->get_option('ams_product_thumbnail_size'), 'Large', true); ?>><?php esc_html_e('Large (500 X 500)', 'ams-wc-amazon'); ?></option>
                                <option value="Medium" <?php selected($this->get_option('ams_product_thumbnail_size'), 'Medium', true); ?>><?php esc_html_e('Medium (160 X 160)', 'ams-wc-amazon'); ?></option>
                                <option value="Small" <?php selected($this->get_option('ams_product_thumbnail_size'), 'Small', true); ?>><?php esc_html_e('Small (75 X 75)', 'ams-wc-amazon'); ?></option>
                            </select>
                        </div>

                        <!-- Variation Image Limit -->
                        <div class="col-md-6">
                            <label for="variation_image_limit" class="form-label"><?php esc_html_e('Variation Image Limit', 'ams-wc-amazon'); ?></label>
                            <div class="input-group">
                                <input type="number" id="variation_image_limit" name="variation_image_limit" class="form-control" 
                                       value="<?php echo esc_attr(get_option('ams_variation_image_limit', 5)); ?>" min="1" max="10">
                                <span class="input-group-text"><?php esc_html_e('Images', 'ams-wc-amazon'); ?></span>
                            </div>
                            <div class="form-text"><?php esc_html_e('Set a value between 1 and 10 images.', 'ams-wc-amazon'); ?></div>
                        </div>

                        <!-- Image Fit Option -->
                        <div class="col-md-6">
                            <label for="ams_image_fit" class="form-label"><?php esc_html_e('Product Image Fit', 'ams-wc-amazon'); ?></label>
                            <select id="ams_image_fit" name="ams_image_fit" class="form-select">
                                <option value="cover" <?php selected($this->get_option('ams_image_fit'), 'cover', true); ?>><?php esc_html_e('Cover (Recommended) - Crops the image to fill the container.', 'ams-wc-amazon'); ?></option>
                                <option value="contain" <?php selected($this->get_option('ams_image_fit'), 'contain', true); ?>><?php esc_html_e('Contain - Fits the entire image inside the container, no cropping.', 'ams-wc-amazon'); ?></option>
                                <option value="fill" <?php selected($this->get_option('ams_image_fit'), 'fill', true); ?>><?php esc_html_e('Fill - Stretches the image to fill the container, may distort.', 'ams-wc-amazon'); ?></option>
                                <option value="none" <?php selected($this->get_option('ams_image_fit'), 'none', true); ?>><?php esc_html_e('None - Displays the image at its original size.', 'ams-wc-amazon'); ?></option>
                                <option value="scale-down" <?php selected($this->get_option('ams_image_fit'), 'scale-down', true); ?>><?php esc_html_e('Scale Down - Displays the image at its original size or smaller if necessary.', 'ams-wc-amazon'); ?></option>
                            </select>
                            <div class="form-text"><?php esc_html_e('Choose how product images will fit inside containers.', 'ams-wc-amazon'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Images Section -->

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('#ams_image_fit').on('change', function() {
                        var imageFitValue = $(this).val();

                        // AJAX call to save the selected option
                        $.ajax({
                            url: ajaxurl, // WordPress's built-in AJAX handler URL
                            type: 'POST',
                            data: {
                                action: 'ams_save_image_fit', // Custom action hook
                                image_fit: imageFitValue,     // Send the selected value
                                _wpnonce: '<?php echo wp_create_nonce("ams_image_fit_nonce"); ?>' // Security nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    console.log('Image Fit option saved: ' + imageFitValue); // Success log
                                } else {
                                    console.error('Error saving Image Fit option.');
                                }
                            },
                            error: function() {
                                console.error('AJAX error while saving Image Fit option.');
                            }
                        });
                    });
                });
            </script>

            <!--Review section-->
            <div class="card shadow-sm mb-4" id="review-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('Amazon Review Settings', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Enable Amazon Review -->
                        <div class="col-12 d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php esc_html_e('Enable Amazon Review', 'ams-wc-amazon'); ?></h5>
                            </div>
                            <div class="ms-3">
                                <label class="switch">
                                    <input type="checkbox" id="enable_amazon_review" name="enable_amazon_review" value="1" <?php checked($this->get_option('enable_amazon_review'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <!-- Enable Reviewer Image -->
                        <div class="col-12 d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php esc_html_e('Enable Reviewer Image', 'ams-wc-amazon'); ?></h5>
                            </div>
                            <div class="ms-3">
                                <label class="switch">
                                    <input type="checkbox" id="enable_reviewer_image" name="enable_reviewer_image" value="1" <?php checked($this->get_option('enable_reviewer_image'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <!-- Enable Review Title -->
                        <div class="col-12 d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php esc_html_e('Enable Review Title', 'ams-wc-amazon'); ?></h5>
                            </div>
                            <div class="ms-3">
                                <label class="switch">
                                    <input type="checkbox" id="enable_review_title" name="enable_review_title" value="1" <?php checked($this->get_option('enable_review_title'), '1'); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <!-- Review Limit Options -->
                        <div id="review-limit-options" class="col-12" style="display: <?php echo $this->get_option('enable_amazon_review') == '1' ? 'block' : 'none'; ?>;">
                            <div class="row g-3">
                                <!-- Single Import Review Limit -->
                                <div class="col-md-6">
                                    <label for="single_import_review_limit" class="form-label"><?php esc_html_e('Single Import Review Limit', 'ams-wc-amazon'); ?></label>
                                    <select class="form-control" id="single_import_review_limit" name="single_import_review_limit">
                                        <option value="5" <?php selected($this->get_option('single_import_review_limit'), '5'); ?>>5</option>
                                        <option value="10" <?php selected($this->get_option('single_import_review_limit'), '10'); ?>>10</option>
                                        <option value="15" <?php selected($this->get_option('single_import_review_limit'), '15'); ?>>15</option>
                                        <option value="20" <?php selected($this->get_option('single_import_review_limit'), '20'); ?>>20</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <?php esc_html_e('Number of comments to import from Amazon. Choose between 5, 10, 15, or 20. Default = 5', 'ams-wc-amazon'); ?>
                                    </small>
                                </div>
                                <!-- Multiple Import Review Limit -->
                                <div class="col-md-6">
                                    <label for="multiple_import_review_limit" class="form-label"><?php esc_html_e('Multiple Import Review Limit', 'ams-wc-amazon'); ?></label>
                                    <select class="form-control" id="multiple_import_review_limit" name="multiple_import_review_limit">
                                        <option value="5" <?php selected($this->get_option('multiple_import_review_limit'), '5'); ?>>5</option>
                                        <option value="10" <?php selected($this->get_option('multiple_import_review_limit'), '10'); ?>>10</option>
                                        <option value="15" <?php selected($this->get_option('multiple_import_review_limit'), '15'); ?>>15</option>
                                        <option value="20" <?php selected($this->get_option('multiple_import_review_limit'), '20'); ?>>20</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <?php esc_html_e('Number of comments to import from Amazon. Choose between 5, 10, 15, or 20. Default = 5', 'ams-wc-amazon'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Review section-->

            <!--NO API Settings-->
            <div class="card shadow-sm mb-4" id="no-api-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('NO API Settings', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Number of Search Results -->
                        <div class="col-lg-6">
                            <h5 class="mb-3"><?php esc_html_e('Number of Search Results - Import by-search module', 'ams-wc-amazon'); ?></h5>
                            <div id="container" class="dropDown_container">
                                <?php
                                $ams_results_limit_placeholder = $this->get_option('ams_results_limit') ? $this->get_option('ams_results_limit') . ' results' : 'Choose number of results';
                                ?>
                                <select id="settings_ams_results_limit" name="ams_results_limit" class="form-select" placeholder-text="<?= esc_attr($ams_results_limit_placeholder); ?>">
                                    <option value="20" <?php selected($this->get_option('ams_results_limit'), '20', true); ?> class="select-dropdown__list-item"><?php esc_html_e('20 results', 'ams-wc-amazon'); ?></option>
                                    <option value="50" <?php selected($this->get_option('ams_results_limit'), '50', true); ?> class="select-dropdown__list-item"><?php esc_html_e('50 results', 'ams-wc-amazon'); ?></option>
                                    <option value="100" <?php selected($this->get_option('ams_results_limit'), '100', true); ?> class="select-dropdown__list-item"><?php esc_html_e('100 results', 'ams-wc-amazon'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--NO API Settings-->

            <!-- API Settings -->
            <div class="card shadow-sm mb-4" id="api-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('API Settings', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- API Settings - Results Per Page -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="product_per_page" class="form-label"><?php esc_html_e('API Results Per Page', 'ams-wc-amazon'); ?></label>
                                <input class="form-control" id="product_per_page" min="0" max="10" type="number" name="product_per_page" value="<?php echo esc_attr($this->get_option('ams_product_per_page')); ?>" placeholder="10">
                                <div class="form-text">
                                    <?php esc_html_e('Display products per Amazon API request. Max is 10.', 'ams-wc-amazon'); ?>
                                </div>
                            </div>
                        </div>
                    <div class="row">
                        <!-- Minimum Category Depth -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_min_depth" class="form-label"><?php esc_html_e('Minimum Category Depth', 'ams-wc-amazon'); ?></label>
                                <input class="form-control" id="category_min_depth" min="1" max="10" type="number" name="category_min_depth" value="<?php echo esc_attr($this->get_option('ams_category_min_depth', 1)); ?>" placeholder="1">
                                <div class="form-text">
                                    <?php esc_html_e('Specify the minimum number of category levels.', 'ams-wc-amazon'); ?>
                                </div>
                            </div>
                        </div>
                        <!-- Maximum Category Depth -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_max_depth" class="form-label"><?php esc_html_e('Maximum Category Depth', 'ams-wc-amazon'); ?></label>
                                <input class="form-control" id="category_max_depth" min="1" max="10" type="number" name="category_max_depth" value="<?php echo esc_attr($this->get_option('ams_category_max_depth', 10)); ?>" placeholder="10">
                                <div class="form-text">
                                    <?php esc_html_e('Specify the maximum number of category levels.', 'ams-wc-amazon'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <!--API Settings-->

            <!--Proxy section-->
            <div class="card shadow-sm mb-4" id="proxy-settings">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><?php esc_html_e('Proxy Service Configuration', 'ams-wc-amazon'); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- ScraperAPI Section -->
                        <div class="col-lg-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0 text-white">ScraperAPI</h5>
                                    <small>High-speed Amazon product import and updates</small>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $is_scraper_active = ($this->get_option('ams_scraper_api_is_active') == '1' || $this->get_option('ams_scraper_api_on_update') == '1');
                                    $scraper_status_class = $is_scraper_active ? 'bg-success' : 'bg-danger';
                                    $scraper_status_text = $is_scraper_active ? __('Active', 'ams-wc-amazon') : __('Inactive', 'ams-wc-amazon');
                                    ?>
                                    <div class="mb-3 text-end">
                                        <span class="badge <?php echo $scraper_status_class; ?> px-3 py-2">
                                            <?php echo $scraper_status_text; ?>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?php esc_html_e('Import Products', 'ams-wc-amazon'); ?></h6>
                                            <label class="switch">
                                                <input type="checkbox" name="scraper_api_is_active" value="1" <?php checked($this->get_option('ams_scraper_api_is_active'), '1', true); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?php esc_html_e('Update Products', 'ams-wc-amazon'); ?></h6>
                                            <label class="switch">
                                                <input type="checkbox" name="scraper_api_on_update" value="1" <?php checked($this->get_option('ams_scraper_api_on_update'), '1', true); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="mb-2"><?php esc_html_e('ScraperAPI Key', 'ams-wc-amazon'); ?></h6>
                                        <div class="input-group">
                                            <input type="text" id="scraper_api_key" name="scraper_api_key" class="form-control" value="<?php echo esc_attr($this->get_option('ams_scraper_api_key')); ?>" placeholder="<?php esc_html_e('Enter API KEY', 'ams-wc-amazon'); ?>">
                                            <button type="button" name="test-it" id="test-it" class="btn btn-primary"><?php esc_html_e('Test', 'ams-wc-amazon'); ?></button>
                                        </div>
                                    </div>
                                    <div id="test-it-response" class="alert alert-primary d-none mt-3"></div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-primary me-2">Free</span>
                                                <small class="text-muted">
                                                    <?php echo wp_kses_post(sprintf(__('<strong>%s</strong> API calls/month', 'ams-wc-amazon'), '1,000')); ?>
                                                </small>
                                            </div>
                                            <a href="https://www.scraperapi.com/?fp_ref=ams" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                                <?php esc_html_e('Create Account', 'ams-wc-amazon'); ?>
                                                <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ScrapingAnt Section -->
                        <div class="col-lg-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">ScrapingAnt</h5>
                                    <small>Reliable Amazon product import and updates</small>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $is_scrapingant_active = ($this->get_option('ams_scrapingant_is_active') == '1' || $this->get_option('ams_scrapingant_on_update') == '1');
                                    $scrapingant_status_class = $is_scrapingant_active ? 'bg-success' : 'bg-danger';
                                    $scrapingant_status_text = $is_scrapingant_active ? __('Active', 'ams-wc-amazon') : __('Inactive', 'ams-wc-amazon');
                                    ?>
                                    <div class="mb-3 text-end">
                                        <span class="badge <?php echo $scrapingant_status_class; ?> px-3 py-2">
                                            <?php echo $scrapingant_status_text; ?>
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?php esc_html_e('Import Products', 'ams-wc-amazon'); ?></h6>
                                            <label class="switch">
                                                <input type="checkbox" name="scrapingant_is_active" value="1" <?php checked($this->get_option('ams_scrapingant_is_active'), '1', true); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?php esc_html_e('Update Products', 'ams-wc-amazon'); ?></h6>
                                            <label class="switch">
                                                <input type="checkbox" name="scrapingant_on_update" value="1" <?php checked($this->get_option('ams_scrapingant_on_update'), '1', true); ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <h6 class="mb-2"><?php esc_html_e('ScrapingAnt API Key', 'ams-wc-amazon'); ?></h6>
                                        <div class="input-group">
                                            <input type="text" id="scrapingant_api_key" name="scrapingant_api_key" class="form-control" value="<?php echo esc_attr($this->get_option('ams_scrapingant_api_key')); ?>" placeholder="<?php esc_html_e('Enter API KEY', 'ams-wc-amazon'); ?>">
                                            <button type="button" name="test-scrapingant" id="test-scrapingant" class="btn btn-success"><?php esc_html_e('Test', 'ams-wc-amazon'); ?></button>
                                        </div>
                                    </div>
                                    <div id="test-scrapingant-response" class="alert alert-success d-none mt-3"></div>
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-success me-2">Free</span>
                                                <small class="text-muted">
                                                    <?php echo wp_kses_post(sprintf(__('<strong>%s</strong> API calls/month', 'ams-wc-amazon'), '10,000')); ?>
                                                </small>
                                            </div>
                                            <a href="https://scrapingant.com/?ref=n2mzmtb" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm">
                                                <?php esc_html_e('Create Account', 'ams-wc-amazon'); ?>
                                                <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Proxy section-->


            <!--Save settings section-->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0"><?php esc_html_e('Save All Settings', 'ams-wc-amazon'); ?></h5>
                            <p class="text-muted small mb-0"><?php esc_html_e('Click to save all changes', 'ams-wc-amazon'); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <input type="hidden" name="action" value="<?php echo esc_attr( 'ams-wc-general-setting' ); ?>">
                            <input type="hidden" name="action_tab" value="pills-general-tab">
                            
                            <?php wp_nonce_field("general_setting_nonce") ?>
                            <button type="submit" name="general-setting-submit" id="general-setting-submit" value="Save Settings" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php esc_html_e("Save Settings", "ams-wc-amazon"); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--Save settings section-->


            <!-- Floating Save Button -->
            <div class="position-fixed bottom-0 end-0 p-3">
                <button 
                    type="submit" 
                    name="general-setting-submit" 
                    id="general-setting-submit" 
                    value="Save Settings" 
                    class="btn btn-primary d-flex align-items-center"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="left" 
                    title="<?php esc_attr_e('Save all your changes', 'ams-wc-amazon'); ?>"
                >
                    <i class="fas fa-save me-2"></i>
                    <?php esc_html_e('Save', 'ams-wc-amazon'); ?>
                </button>
            </div>

            <script>
                // Initialize Bootstrap tooltips
                document.addEventListener('DOMContentLoaded', function () {
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                });
            </script>

        </div>
    </form>
</div>

<div class="tab-pane fade <?= ( isset($_GET['tab']) && $_GET['tab'] == "pills-az-settings-tab")?'show active':''; ?> " id="pills-az-settings" role="tabpanel" aria-labelledby="pills-az-settings-tab">
    <h2><?php esc_html_e('Amazon API Credentials ', 'ams-wc-amazon'); ?></h2>
    <div class="setting_inner">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <div class="form-group row">
                <label class="col-lg-3 col-form-label"><?php esc_html_e('Amazon Affiliate Access Key ID', 'ams-wc-amazon'); ?></label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="password" id="access_key_id" name="access_key_id"
                               class="form-control"
                               placeholder="<?php echo esc_attr__('Enter your amazon access Key ID', 'ams-wc-amazon'); ?>"
                               value="<?php echo esc_attr($this->get_option('ams_access_key_id')); ?>"
                               onfocus="this.removeAttribute('readonly');"
                               onblur="this.setAttribute('readonly', 'readonly');"
                               autocomplete="off" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-eye toggle-password-access-key" onclick="toggleVisibility('access_key_id', 'toggle-password-access-key')"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label"><?php esc_html_e('Amazon Affiliate Secret Access Key', 'ams-wc-amazon'); ?></label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="password" id="secret_access_key" name="secret_access_key"
                               class="form-control"
                               placeholder="<?php echo esc_attr__('Enter your Amazon Secret Access Key', 'ams-wc-amazon'); ?>"
                               value="<?php echo esc_attr($this->get_option('ams_secret_access_key')); ?>"
                               onfocus="this.removeAttribute('readonly');"
                               onblur="this.setAttribute('readonly', 'readonly');"
                               autocomplete="off" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-eye toggle-password-secret" onclick="toggleVisibility('secret_access_key', 'toggle-password-secret')"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 col-form-label"><?php esc_html_e('Amazon Affiliate Associate Tag', 'ams-wc-amazon'); ?></label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="password" id="input-username" name="ams_associate_tag"
                               class="form-control"
                               placeholder="<?php echo esc_attr__('Enter your amazon associate ID', 'ams-wc-amazon'); ?>"
                               value="<?php echo esc_attr($this->get_option('ams_associate_tag')); ?>"
                               onfocus="this.removeAttribute('readonly');"
                               onblur="this.setAttribute('readonly', 'readonly');"
                               autocomplete="off" readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-eye toggle-password-associate" onclick="toggleVisibility('input-username', 'toggle-password-associate')"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-lg-3">
                    <label><?php esc_html_e('Amazon Affiliate Country', 'ams-wc-amazon'); ?></label>
                </div>
                <div class="col-lg-9">
                    <div id="container" class="dropDown_container" style="opacity: 0.5; pointer-events: none;">
                        <?php
                        $regions = ams_get_amazon_regions();
                        $regions_placeholder = $this->get_option('ams_amazon_country');
                        foreach ($regions as $key=>$value) {
                            if($regions_placeholder == $key) {
                                $regions_placeholder = $value;                             
                                break;
                            }
                        }
                        ?>
                    <select name="ams_amazon_country" id="amazon_country" placeholder-text="<?= $value['RegionName']; ?>">
                        <?php
                            foreach ( $regions as $key => $value ) {
                            ?>
                            <option class="select-dropdown__list-item" <?php selected( $this->get_option( 'ams_amazon_country' ), $key, true ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value['RegionName'] ); ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-lg-3">
                <label><?php esc_html_e('Currency Is Auto set by country', 'ams-wc-amazon'); ?></label>
                </div>
                <div class="col-lg-9">
                    <div id="container" class="dropDown_container" style="opacity: 0.5; pointer-events: none;">
                        <?php $store_currency = get_woocommerce_currency();
                        $currency_code_options = get_woocommerce_currencies();
                        $currency_placeholder = $store_currency;
                        foreach ($currency_code_options as $code=>$value) {
                            if($store_currency == $code) {
                                $currency_placeholder = $value;                             
                                break;
                            }
                        }
                        ?>
                    <select name="woocommerce_currency" id="woocommerce_currency" placeholder-text="<?= $currency_placeholder; ?>">
                        <?php foreach ($currency_code_options as $code=>$value) { ?>
                            <option value="<?php echo esc_attr( $code); ?>" <?php selected( $store_currency, $code ); ?>><?php echo esc_html( $value ); ?></option>
                        <?php } ?>
                    </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <input type="hidden" name="action" value="<?php echo esc_attr( 'ams_wc_amazon_general_setting' ); ?>">
                <input type="hidden" name="action_tab" value="pills-az-settings-tab">
                <?php wp_nonce_field( 'general_amazon_setting_nonce' ); ?>
                <button type="submit" name="general-setting-submit" id="general-setting-submit" value="<?= esc_html__( 'Save Settings', 'ams-wc-amazon' ); ?>" class="btn btn-primary"><?= esc_html__( 'Save Settings', 'ams-wc-amazon' ); ?></button>

                <button type="button" class="btn btn-info ams-test-api-btn">
                    <?php echo esc_html__('Test Amazon API Key', 'ams-wc-amazon'); ?>
                </button>
                <div class="ams-api-message mt-2"></div>
            </div>
  
        </form>
    </div>
</div>

<div class="tab-pane fade <?= (isset($_GET['tab']) && $_GET['tab'] == "pills-az-products-tab") ? 'show active' : ''; ?>" id="pills-az-products" role="tabpanel" aria-labelledby="pills-az-products-tab">
    <div class="row d-flex flex-row">
        <!-- Left Column: Settings -->
        <div class="col-lg-5 d-flex flex-column">
            <div class="card shadow-sm flex-grow-1">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-muted">
                        <i class="fas fa-cog me-2 text-secondary"></i>
                        <?php esc_html_e('Cron Job Settings', 'ams-wc-amazon'); ?>
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#statusOverview" aria-expanded="false" aria-controls="statusOverview">
                        <?php esc_html_e('Toggle Status', 'ams-wc-amazon'); ?>
                    </button>
                </div>
                <div class="card-body p-2 d-flex flex-column">
                    <!-- Status Overview -->
                    <div class="collapse mb-3" id="statusOverview">
                        <div class="card card-body p-2">
                            <h6 class="mb-2"><?php esc_html_e('Quick Status Overview', 'ams-wc-amazon'); ?></h6>
                            <div class="row row-cols-2 g-2">
                                <?php
                                $all_options = [
                                    'product_name_cron', 'product_price_cron', 'product_sku_cron',
                                    'product_description_cron', 'product_image_cron', 'product_category_cron',
                                    'product_variants_cron', 'product_variant_image_cron', 'product_variant_description_cron',
                                    'product_out_of_stock_cron', 'product_review_cron'
                                ];
                                foreach ($all_options as $option) :
                                    $is_enabled = $this->get_option($option) === '1';
                                    $option_label = ucwords(str_replace('_', ' ', preg_replace('/_cron$/', '', $option)));
                                ?>
                                <div class="col">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-<?php echo $is_enabled ? 'success' : 'danger'; ?> me-2" style="width: 10px; height: 10px;"></div>
                                        <small><?php echo esc_html($option_label); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" class="flex-grow-1 d-flex flex-column">
                        <!-- Cron Command Section -->
                        <div class="input-group input-group-sm mb-3">
                            <span class="input-group-text">Cron Command</span>
                            <input type="text" class="form-control form-control-sm bg-light" id="cron-command" value="wget -q -O - <?php echo esc_url(get_bloginfo('wpurl')); ?>/wp-cron.php?doing_wp_cron >/dev/null 2>&1" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copy-cron-command" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php esc_attr_e('Copy to clipboard', 'ams-wc-amazon'); ?>">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#cronGuideModal">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>

                        <!-- Cron Options as Accordion -->
                        <div class="accordion accordion-flush flex-grow-1" id="cronOptionsAccordion">
                            <?php
                            $cron_options = [
                                'Product Info' => ['product_name_cron', 'product_price_cron', 'product_sku_cron'],
                                'Product Details' => ['product_description_cron', 'product_image_cron', 'product_category_cron'],
                                'Product Variants' => ['product_variants_cron', 'product_variant_image_cron', 'product_variant_description_cron'],
                                'Other Options' => ['product_out_of_stock_cron', 'product_review_cron']
                            ];
                            $counter = 0;
                            foreach ($cron_options as $section => $options) :
                                $counter++;
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $counter; ?>">
                                    <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $counter; ?>" aria-expanded="false" aria-controls="collapse<?php echo $counter; ?>">
                                        <?php echo esc_html($section); ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $counter; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $counter; ?>" data-bs-parent="#cronOptionsAccordion">
                                    <div class="accordion-body p-2">
                                        <?php foreach ($options as $option_name) :
                                            $is_enabled = $this->get_option($option_name) === '1';
                                            $option_label = ucwords(str_replace('_', ' ', preg_replace('/_cron$/', '', $option_name)));
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="me-2 small"><?php esc_html_e($option_label, 'ams-wc-amazon'); ?></span>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="<?php echo esc_attr($option_label); ?> toggle">
                                                <input type="radio" class="btn-check" name="<?php echo esc_attr($option_name); ?>" id="<?php echo esc_attr($option_name); ?>_enabled" value="1" <?php checked($is_enabled, true); ?> autocomplete="off">
                                                <label class="btn btn-outline-success" for="<?php echo esc_attr($option_name); ?>_enabled">On</label>
                                                <input type="radio" class="btn-check" name="<?php echo esc_attr($option_name); ?>" id="<?php echo esc_attr($option_name); ?>_disabled" value="0" <?php checked($is_enabled, false); ?> autocomplete="off">
                                                <label class="btn btn-outline-secondary" for="<?php echo esc_attr($option_name); ?>_disabled">Off</label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="text-end mt-3">
                            <input type="hidden" name="action" value="<?php echo esc_attr('ams_product_cron'); ?>">
                            <input type="hidden" name="action_tab" value="pills-az-products-tab">
                            <?php wp_nonce_field("general_setting_nonce") ?>
                            <button type="submit" name="general-setting-submit" id="general-setting-submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i><?php esc_html_e('Save Settings', 'ams-wc-amazon'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Cron Console -->
        <div class="col-lg-7 d-flex flex-column">
            <?php
                global $wpdb;
                @$product_name_cron = get_option('product_name_cron');
                @$product_price_cron = get_option('product_price_cron');
                @$product_sku_cron = get_option('product_sku_cron');
                @$product_variants_cron = get_option('product_variants_cron');
                @$product_variant_image_cron = get_option('product_variant_image_cron');
                @$product_variant_description_cron = get_option('product_variant_description_cron');
                @$product_out_of_stock_cron = get_option('product_out_of_stock_cron');
                @$product_description_cron = get_option('product_description_cron');
                @$product_image_cron = get_option('product_image_cron');
                @$product_category_cron = get_option('product_category_cron');
                @$product_review_cron = get_option('product_review_cron');
                @$product_tags_cron = get_option('product_tags_cron');

                @$asins = ams_get_all_products_info();
                @$id_asin = (is_array($asins['id'])) ? array_combine( $asins['id'], $asins['asin'] ) : array();
                @$import_id = $asins['product_id'];
                @$ids_bunch = array_chunk( $id_asin, 1, true);
                $url = 'ams_product_availability';
                $region = null;
                if(isset($import_ids['method']) && $import_ids['method'] == 1){
                    $url = 'ams_product_availability';
                } else if(isset($import_ids['method']) && $import_ids['method'] == 2){
                    $url = 'ams_product_availability';
                }
            ?>
            <div class="card flex-grow-1 d-flex flex-column">
                <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-warning">
                        <i class="fas fa-sync-alt me-2"></i>
                        <?php esc_html_e('Amazon Product Cron Console', 'ams-wc-amazon'); ?>
                    </h5>
                    <button id="prod_cron" class="btn btn-success btn-sm" onclick="cron_run();">
                        <?php esc_html_e('Start Cron', 'ams-wc-amazon'); ?>
                    </button>
                </div>
                <div class="card-body bg-dark p-0 d-flex flex-column flex-grow-1">
                    <div id="console-window" class="text-light p-3 flex-grow-1" style="height: 300px; overflow-y: auto; font-family: monospace;">
                        <div id="console-output"></div>
                    </div>
                    <div class="bg-dark" style="height: 5px;">
                        <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; height: 100%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="card-footer bg-dark text-light d-flex justify-content-between py-2">
                    <span id="status-message"><?php esc_html_e('Ready to start cron job', 'ams-wc-amazon'); ?></span>
                    <span id="product-count">0 / <?php echo esc_html($asins['products_count']); ?> <?php esc_html_e('products updated', 'ams-wc-amazon'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>

<!-- Right side scripts -->
<script type="text/javascript">
    var cronIndex = 0;
    var count = 0;
    var idArr = [];
    var import_id = <?php echo json_encode($import_id); ?>;
    var total_products = <?php echo $asins['products_count']; ?>;

    function cron_run() {
        cronIndex = 0;
        count = 0;
        idArr = <?php echo json_encode($ids_bunch); ?>;
        
        // Clear the console
        jQuery('#console-output').empty();
        
        updateConsole('Initializing cron job...', 'text-info');
        jQuery('#prod_cron').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Running').attr('disabled', true);
        jQuery('#status-message').text('Cron job in progress');
        updateProgress(0);
        run_cron_single_bunch(idArr[cronIndex]);
    }

    function run_cron_single_bunch(id_array) {
        if (!id_array || Object.keys(id_array).length === 0) {
            finishCron();
            return;
        }

        var pid = Object.keys(id_array)[0];
        var url = 'ams_product_availability';
        var region = asin = product_url = '';
        
        if (import_id[pid]) {
            if (import_id[pid]['method'] == 1) {
                url = 'ams_product_import';
                region = import_id[pid]['region'];
                asin = id_array[pid];
            } else {
                url = 'ams_product_availability';
                region = import_id[pid]['region'];
                asin = id_array[pid];
            }
            product_url = import_id[pid]['url'];
        }
        
        var data = {data:id_array, action:url, region:region, is_cron:'1', asin:asin, prod_id:pid, product_url:product_url};
        
        updateConsole(`Processing product ID: ${pid}`, 'text-warning');
        
        jQuery.ajax({
            url: "<?php echo admin_url("admin-ajax.php") ?>",
            type: 'POST',
            data: data,
            success: function(data) {
                count += Object.keys(id_array).length;
                updateProgress((count / total_products) * 100);
                updateProductCount(count);
                updateConsole(data, 'text-success');
                
                cronIndex++;
                if (idArr[cronIndex]) {
                    run_cron_single_bunch(idArr[cronIndex]);
                } else {
                    finishCron();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                updateConsole(`Error updating product ID: ${pid}. ${textStatus}`, 'text-danger');
                cronIndex++;
                if (idArr[cronIndex]) {
                    run_cron_single_bunch(idArr[cronIndex]);
                } else {
                    finishCron();
                }
            }
        });
    }

    function updateConsole(message, className = '') {
        var timestamp = new Date().toLocaleTimeString();
        jQuery('#console-output').append(`<div class="${className}">[${timestamp}] ${message}</div>`);
        jQuery('#console-window').scrollTop(jQuery('#console-window')[0].scrollHeight);
    }

    function updateProgress(percentage) {
        jQuery('#progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage);
    }

    function updateProductCount(count) {
        jQuery('#product-count').text(`${count} / ${total_products} <?php esc_html_e('products updated', 'ams-wc-amazon'); ?>`);
    }

    function finishCron() {
        updateConsole('Cron job completed.', 'text-info font-weight-bold');
        jQuery('#prod_cron').html('<?php esc_html_e('Start Cron', 'ams-wc-amazon'); ?>').removeAttr('disabled');
        jQuery('#status-message').text('<?php esc_html_e('Cron job completed', 'ams-wc-amazon'); ?>');
        updateProgress(100);
    }

    // Add event listener for page unload
    window.addEventListener('beforeunload', function (e) {
        if (cronIndex > 0 && cronIndex < idArr.length) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
<!-- Right side scripts -->


<!-- Left side scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy functionality for cron command
    const copyButton = document.getElementById('copy-cron-command');
    const cronCommand = document.getElementById('cron-command');

    if (copyButton && cronCommand) {
        const tooltip = new bootstrap.Tooltip(copyButton);

        copyButton.addEventListener('click', function() {
            cronCommand.select();
            document.execCommand('copy');
            
            copyButton.setAttribute('data-bs-original-title', '<?php esc_attr_e('Copied!', 'ams-wc-amazon'); ?>');
            tooltip.show();
            
            setTimeout(() => {
                copyButton.setAttribute('data-bs-original-title', '<?php esc_attr_e('Copy to clipboard', 'ams-wc-amazon'); ?>');
                tooltip.hide();
            }, 2000);
        });
    }

    // Initialize all tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<!-- Left side scripts -->


<!-- Cron Guide Modal -->
<div class="modal fade" id="cronGuideModal" tabindex="-1" aria-labelledby="cronGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cronGuideModalLabel"><?php esc_html_e('How to Set Up Cron Jobs', 'ams-wc-amazon'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Instructions for WP Crontrol -->
                <h6 class="text-primary mb-3">
                    <?php esc_html_e('Option 1: Using WP Crontrol Plugin (Recommended)', 'ams-wc-amazon'); ?>
                </h6>
                <ol>
                    <li>
                        <strong><?php esc_html_e('Install WP Crontrol Plugin:', 'ams-wc-amazon'); ?></strong>
                        <ul>
                            <li><?php esc_html_e('Go to your WordPress admin panel.', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Navigate to Plugins > Add New.', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Search for "WP Crontrol" and install it.', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Activate the plugin.', 'ams-wc-amazon'); ?></li>
                        </ul>
                    </li>
                    <li>
                        <strong><?php esc_html_e('View and Manage Cron Events:', 'ams-wc-amazon'); ?></strong>
                        <ul>
                            <li><?php esc_html_e('Go to Tools > Cron Events in your WordPress admin.', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Locate "ams_daily_cron_event" in the list of scheduled events.', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Ensure it is set to run daily. If needed, you can edit the schedule.', 'ams-wc-amazon'); ?></li>
                        </ul>
                    </li>
                </ol>
                <p class="text-muted">
                    <?php esc_html_e('This method relies on WordPress to handle scheduled tasks automatically.', 'ams-wc-amazon'); ?>
                </p>

                <hr class="my-4">

                <!-- Instructions for Server-Side Cron -->
                <h6 class="text-primary mb-3">
                    <?php esc_html_e('Option 2: Setting Up Server-Side Cron Job', 'ams-wc-amazon'); ?>
                </h6>
                <ol>
                    <li>
                        <strong><?php esc_html_e('Disable WordPress Default Cron:', 'ams-wc-amazon'); ?></strong>
                        <ul>
                            <li><?php esc_html_e('Open your wp-config.php file.', 'ams-wc-amazon'); ?></li>
                            <li>
                                <?php esc_html_e('Add the following line of code:', 'ams-wc-amazon'); ?>
                                <pre><code>define('DISABLE_WP_CRON', true);</code></pre>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Set Up Cron Job on Server:', 'ams-wc-amazon'); ?></strong>
                        <ul>
                            <li><?php esc_html_e('Log into your hosting control panel (e.g., cPanel).', 'ams-wc-amazon'); ?></li>
                            <li><?php esc_html_e('Navigate to the "Cron Jobs" or "Scheduled Tasks" section.', 'ams-wc-amazon'); ?></li>
                            <li>
                                <?php esc_html_e('Add the following command:', 'ams-wc-amazon'); ?>
                                <pre><code>wget -q -O - <?php echo esc_url(get_bloginfo('wpurl')); ?>/wp-cron.php?doing_wp_cron >/dev/null 2>&1</code></pre>
                            </li>
                            <li><?php esc_html_e('Set the interval to run once daily and save the cron job.', 'ams-wc-amazon'); ?></li>
                        </ul>
                    </li>
                </ol>
                <p class="text-muted">
                    <?php esc_html_e('This method ensures tasks are handled directly by your server, making it more reliable for high-traffic websites.', 'ams-wc-amazon'); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php esc_html_e('Close', 'ams-wc-amazon'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Cron Guide Modal -->


<!-- Cron settings show/hide eye icon-->
<script>
    function toggleVisibility(inputId, iconClass) {
        var passwordInput = document.getElementById(inputId);
        var passwordIcon = document.querySelector('.' + iconClass);
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
        }
    }
</script>
<!-- Cron settings show/hide eye icon-->


<!-- Review section script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const enableReviewCheckbox = document.getElementById('enable_amazon_review');
    const reviewLimitOptions = document.getElementById('review-limit-options');

    function toggleReviewLimitOptions() {
        reviewLimitOptions.style.display = enableReviewCheckbox.checked ? 'block' : 'none';
    }

    enableReviewCheckbox.addEventListener('change', toggleReviewLimitOptions);
    toggleReviewLimitOptions(); // Initial state
});
</script>
<!-- Review section script -->


<!-- Proxy section script -->
<script>
(function($) {
    $(document).ready(function() {
        function testAPI(buttonId, responseId, action) {
            $(buttonId).on('click', function(e) {
                e.preventDefault();
                let _this = $(this);
                _this.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Testing...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: { action: action },
                    success: function(response) {
                        var $responseEl = $(responseId);
                        $responseEl.removeClass('d-none alert-primary alert-success alert-danger');
                        
                        if (response.status) {
                            $responseEl.addClass('alert-success');
                        } else {
                            $responseEl.addClass('alert-danger');
                        }
                        
                        $responseEl.html(response.message).fadeIn();
                        _this.prop('disabled', false).html('Test');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                        $(responseId)
                            .removeClass('d-none alert-primary alert-success')
                            .addClass('alert-danger')
                            .html('<small>An unexpected error occurred. Please try again.</small>')
                            .fadeIn();
                        _this.prop('disabled', false).html('Test');
                    }
                });
            });
        }

        testAPI('#test-it', '#test-it-response', 'scraper_api_test_code');
        testAPI('#test-scrapingant', '#test-scrapingant-response', 'scrapingant_test_code');
    });
})(jQuery);
</script>
<!-- Proxy section script -->