document.addEventListener('DOMContentLoaded', function() {
    const amazonCountrySelect = document.getElementById('amazon_country');
    
    if (amazonCountrySelect) {
        amazonCountrySelect.addEventListener('change', function() {
            let selectedCountry = this.value;
            selectedCountry = selectedCountry.replace("co.", "").replace("com.", "");

            jQuery.ajax({
                url: amsFormControl.ajax_url,
                type: "POST",
                dataType: "JSON",
                data: {
                    action: "ams_get_currency_by_country",
                    country: selectedCountry
                },
                success: function(response) {
                    if (response.status) {
                        const currencySelect = document.getElementById('woocommerce_currency');
                        if (currencySelect) {
                            currencySelect.value = response.value;
                            
                            // Trigger change event
                            const event = new Event('change');
                            currencySelect.dispatchEvent(event);
                        }
                    }
                }
            });
        });
    }
});