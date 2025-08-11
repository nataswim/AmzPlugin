<input type="hidden" id="wca_license_status" value="<?php echo esc_attr($wca_license_status); ?>">

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Global handler for form submissions
        $(document).on('submit', 'form', function(event) {
            checkLicenseAndBlock(event);
        });

        // Block any file input changes (file upload interaction)
        $(document).on('change', 'input[type="file"]', function(event) {
            checkLicenseAndBlock(event);
        });

        // Also block clicks on buttons that may trigger file uploads or other actions
        $(document).on('click', 'button, input[type="submit"]', function(event) {
            checkLicenseAndBlock(event);
        });

        // Function to check license and block the action
        function checkLicenseAndBlock(event) {
            // Check license status
            var licenseStatus = $('#wca_license_status').val();
            if (licenseStatus === 'Not Activated') {
                event.preventDefault();
                event.stopPropagation();

                // Create the entire alert structure
                var alertHtml = `
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>${<?php echo json_encode(esc_html__('Warning', 'ams-wc-amazon')); ?>}:</strong> 
                        <span>Your license needs to be activated to unlock the full functionality of the plugin.</span>
                    </div>
                `;
                
                // Replace the content of the container with the new alert
                $('.container-fluid.mt-3').html(alertHtml);
            }
        }
    });
</script>