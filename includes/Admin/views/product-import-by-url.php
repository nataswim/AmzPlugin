<?php include "common-header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Left Column: URL Input -->
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm d-flex flex-column">
                <div class="card-header bg-light border-bottom py-2">
                    <h5 class="mb-1 text-muted d-flex align-items-center">
                        <i class="fas fa-link me-2 text-secondary"></i>
                        <span class="text-truncate"><?php esc_html_e('Import Amazon Products', 'ams-wc-amazon'); ?></span>
                    </h5>
                    <p class="mb-0 small text-secondary lh-sm">
                        <?php esc_html_e('Paste URLs below for automatic formatting and de-duplication.', 'ams-wc-amazon'); ?>
                    </p>
                </div>
                <div class="card-body flex-grow-1 d-flex flex-column">
                    <div id="validation-error-message" class="alert alert-danger d-none"></div>
                    <?php
                    wp_nonce_field('ams_import_product_url');
                    ?>
                    <div class="mb-3 position-relative flex-grow-1">
                        <textarea class="form-control h-100" id="wca-product-all-url" rows="5" placeholder="https://www.amazon.com/dp/B01H0EPVBQ&#10;https://www.amazon.com/dp/B07L9ZWPNC"></textarea>
                        <div id="url-count" class="position-absolute bottom-0 end-0 p-2 text-muted small">0 URLs</div>
                    </div>
                    <div id="url-status-container" class="mt-3 border rounded p-2 d-none" style="max-height: 200px; overflow-y: auto;">
                        <!-- URLs will be dynamically added here -->
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-secondary border-secondary" id="clear-urls">
                            <i class="fas fa-trash-alt me-2"></i><?php esc_html_e('Clear All', 'ams-wc-amazon'); ?>
                        </button>
                        <button id="prod_cron" type="button" class="btn btn-primary" onclick="product_import();">
                            <i class="fas fa-file-import me-2"></i><?php esc_html_e('Import Products', 'ams-wc-amazon'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Import Console -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header bg-dark text-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php esc_html_e('Import Console', 'ams-wc-amazon'); ?>
                    </h5>
                </div>
                <div class="card-body bg-dark p-0 d-flex flex-column">
                    <div id="console-window" class="text-light p-3 flex-grow-1" style="height: 300px; overflow-y: auto; font-family: monospace;">
                        <div id="console-output" class="wca-amazon-product-by-url"></div>
                    </div>
                    <div class="bg-dark" style="height: 5px;">
                        <div id="import-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%; height: 100%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="card-footer bg-dark text-light d-flex justify-content-between py-2">
                    <span id="import-status-message"><?php esc_html_e('Ready to import', 'ams-wc-amazon'); ?></span>
                    <span id="import-product-count">0 / 0 <?php esc_html_e('products imported', 'ams-wc-amazon'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "common-footer.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('wca-product-all-url');
        const urlCount = document.getElementById('url-count');
        const clearButton = document.getElementById('clear-urls');

        textarea.addEventListener('paste', function(e) {
            e.preventDefault();
            let pastedText = (e.clipboardData || window.clipboardData).getData('text');
            let formattedText = formatUrls(pastedText);
            
            // Insert formatted text at cursor position
            let startPos = this.selectionStart;
            let endPos = this.selectionEnd;
            this.value = this.value.substring(0, startPos) + formattedText + this.value.substring(endPos);
            
            // Set cursor position after inserted text
            this.selectionStart = this.selectionEnd = startPos + formattedText.length;
            updateUrlCount();
        });

        textarea.addEventListener('blur', function() {
            this.value = formatUrls(this.value);
            updateUrlCount();
        });

        textarea.addEventListener('input', updateUrlCount);

        clearButton.addEventListener('click', function() {
            textarea.value = '';
            updateUrlCount();
        });

        function formatUrls(text) {
            // Split text by newlines, commas, or spaces
            let urls = text.split(/[\n,\s]+/);
            
            // Process each URL and remove duplicates
            let uniqueUrls = new Set();
            urls.forEach(url => {
                url = url.trim();
                if (url) {
                    // Extract ASIN and full domain for Amazon URLs
                    let amazonMatch = url.match(/https?:\/\/(www\.)?amazon\.([a-z.]{2,6}).*?\/dp\/([A-Z0-9]{10})/i);
                    if (amazonMatch) {
                        let fullDomain = amazonMatch[2];
                        let asin = amazonMatch[3];
                        uniqueUrls.add(`https://www.amazon.${fullDomain}/dp/${asin}`);
                    } else if (url.startsWith('http')) {
                        uniqueUrls.add(url);
                    }
                }
            });
            // Join unique URLs with newlines
            return Array.from(uniqueUrls).join('\n');
        }

        function updateUrlCount() {
            let count = textarea.value.split('\n').filter(url => url.trim()).length;
            urlCount.textContent = `${count} URL${count !== 1 ? 's' : ''}`;
        }

        // Initial URL count update
        updateUrlCount();
    });
</script>

<script type="text/javascript">
    var cronIndex = 0;
    var count = 0;
    var idArr = [];
    var total_products = 0;
    var isImporting = false;
    var importStartTimes = {};
    var timerIntervals = {};

    function product_import() {
        const textarea = document.getElementById('wca-product-all-url');
        const urls = textarea.value.split('\n').filter(url => url.trim());
        const totalUrls = urls.length;
        
        if (totalUrls === 0) {
            updateConsole('No URLs to import', 'text-warning');
            return;
        }

        // Clear the console
        jQuery('#console-output').empty();

        updateConsole('Initializing import...', 'text-info');
        jQuery('#prod_cron').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing').prop('disabled', true);
        jQuery('#import-status-message').text('Import in progress');
        jQuery('#wca-product-all-url').prop('disabled', true);
        jQuery('#clear-urls').prop('disabled', true).addClass('opacity-50');
        updateProgress(0);
        updateProductCount(0, totalUrls);

    const statusContainer = document.getElementById('url-status-container');
    statusContainer.innerHTML = '';
    urls.forEach((url, index) => {
        statusContainer.innerHTML += `
            <div id="url-status-${index}" class="url-status-item mb-1 text-truncate">
                <span class="status-indicator me-2">⚪</span>
                <span class="url-text">${url}</span>
                <a href="${url}" target="_blank" class="ms-2" title="Open in new tab">
                    <i class="fas fa-external-link-alt" style="font-size: 0.8em;"></i>
                </a>
                <span class="import-time ms-2"></span>
            </div>
        `;
    });
        
        // Show the status container
        statusContainer.classList.remove('d-none');

        isImporting = true;
        processUrls(urls, 0, totalUrls);
    }

    function processUrls(urls, index, total) {
        if (index >= total) {
            finishImport();
            return;
        }

        const url = urls[index];
        updateConsole(`Processing product URL: ${url}`, 'text-warning');
        updateUrlStatus(index, 'processing');
        importStartTimes[index] = Date.now();

        // Start the timer for this URL
        startTimer(index);

        jQuery.ajax({
            url: amsbackend.ajax_url,
            type: 'POST',
            data: {
                action: 'ams_product_import_by_url',
                product_url: url,
                nonce: jQuery('#_wpnonce').val()
            },
            success: function(response) {
                stopTimer(index);
                const importTime = ((Date.now() - importStartTimes[index]) / 1000).toFixed(2);
                updateConsole(response, 'text-success');
                updateUrlStatus(index, 'success', importTime);
                updateProgress(((index + 1) / total) * 100);
                updateProductCount(index + 1, total);
                processUrls(urls, index + 1, total);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                stopTimer(index);
                const importTime = ((Date.now() - importStartTimes[index]) / 1000).toFixed(2);
                updateConsole(`Error importing ${url}: ${textStatus}`, 'text-danger');
                updateUrlStatus(index, 'error', importTime);
                updateProgress(((index + 1) / total) * 100);
                updateProductCount(index + 1, total);
                processUrls(urls, index + 1, total);
            }
        });
    }

    function updateUrlStatus(index, status, importTime = null) {
        const statusItem = document.getElementById(`url-status-${index}`);
        if (statusItem) {
            let indicator, className;
            switch(status) {
                case 'processing':
                    indicator = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                    className = 'text-warning';
                    break;
                case 'success':
                    indicator = '<i class="fas fa-check-circle"></i>';
                    className = 'text-success';
                    break;
                case 'error':
                    indicator = '<i class="fas fa-times-circle"></i>';
                    className = 'text-danger';
                    break;
                default:
                    indicator = '⚪';
                    className = '';
            }
            statusItem.querySelector('.status-indicator').innerHTML = indicator;
            statusItem.className = `url-status-item mb-1 text-truncate ${className}`;
            
            if (importTime !== null) {
                statusItem.querySelector('.import-time').textContent = `(${importTime}s)`;
            }
        }
    }

    function startTimer(index) {
        const statusItem = document.getElementById(`url-status-${index}`);
        const timeElement = statusItem.querySelector('.import-time');
        
        timerIntervals[index] = setInterval(() => {
            const elapsedTime = ((Date.now() - importStartTimes[index]) / 1000).toFixed(2);
            timeElement.textContent = `(${elapsedTime}s)`;
        }, 100); // Update every 100ms for smoother display
    }

   function stopTimer(index) {
        clearInterval(timerIntervals[index]);
        delete timerIntervals[index];
    }

    function finishImport() {
        // Stop all timers
        Object.keys(timerIntervals).forEach(index => stopTimer(index));

        updateConsole('Import completed.', 'text-info font-weight-bold');
        jQuery('#prod_cron').html('Import Products').prop('disabled', false);
        jQuery('#import-status-message').text('Import completed');
        jQuery('#wca-product-all-url').prop('disabled', false);
        jQuery('#clear-urls').prop('disabled', false).removeClass('opacity-50');
        updateProgress(100);
        isImporting = false;
    }

    function updateConsole(message, className = '') {
        var timestamp = new Date().toLocaleTimeString();
        jQuery('#console-output').append(`<div class="${className}">[${timestamp}] ${message}</div>`);
        jQuery('#console-window').scrollTop(jQuery('#console-window')[0].scrollHeight);
    }

    function updateProgress(percentage) {
        jQuery('#import-progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage);
    }

    function updateProductCount(count, total) {
        jQuery('#import-product-count').text(`${count} / ${total} products imported`);
    }

    function finishImport() {
        updateConsole('Import completed.', 'text-info font-weight-bold');
        jQuery('#prod_cron').html('Import Products').prop('disabled', false);
        jQuery('#import-status-message').text('Import completed');
        jQuery('#wca-product-all-url').prop('disabled', false);
        jQuery('#clear-urls').prop('disabled', false).removeClass('opacity-50');
        updateProgress(100);
        isImporting = false;
    }

    function import_single_product(product_url) {
        if (!product_url) {
            finishImport();
            return;
        }

        // Make the URL clickable and open in a new tab
        var clickableUrl = `<a href="${product_url}" target="_blank" class="text-white">${product_url}</a>`;
        updateConsole(`Processing product URL: ${clickableUrl}`, 'text-warning');

        var nonce = jQuery('#_wpnonce').val();
        var data = {
            product_url: product_url,
            action: 'ams_product_import_by_url',
            nonce: nonce
        };

        jQuery.ajax({
            url: amsbackend.ajax_url,
            type: 'POST',
            data: data,
            success: function(data) {
                count++;
                updateProgress((count / total_products) * 100);
                updateProductCount(count);
                updateConsole(data, 'text-success');

                cronIndex++;
                if (idArr[cronIndex]) {
                    import_single_product(idArr[cronIndex]);
                } else {
                    finishImport();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                updateConsole(`Error importing product: ${clickableUrl}. ${textStatus}`, 'text-danger');
                cronIndex++;
                if (idArr[cronIndex]) {
                    import_single_product(idArr[cronIndex]);
                } else {
                    finishImport();
                }
            }
        });
    }

    // Add event listener for page unload
    window.addEventListener('beforeunload', function (e) {
        if (isImporting) {
            e.preventDefault(); // Cancel the event
            e.returnValue = ''; // Chrome requires returnValue to be set
        }
    });

    // Initialize stored messages
    jQuery(document).ready(function($) {
        $('.wca-amazon-product-by-url').html(localStorage.getItem('product_url_messages'));
        localStorage.removeItem('product_url_messages');
    });
</script>