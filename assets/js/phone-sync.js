jQuery(function($) {
    'use strict';

    // Function to sync phone numbers
    function syncPhoneNumbers() {
        const shippingPhone = $('#shipping_phone');
        const billingPhone = $('#billing_phone');

        if (!shippingPhone.length || !billingPhone.length) {
            return;
        }

        // Sync shipping to billing
        shippingPhone.on('input', function() {
            billingPhone.val($(this).val()).trigger('change');
        });

        // Sync billing to shipping
        billingPhone.on('input', function() {
            shippingPhone.val($(this).val()).trigger('change');
        });

        // Initial sync if billing phone has a value
        if (billingPhone.val()) {
            shippingPhone.val(billingPhone.val()).trigger('change');
        }
        // Or if shipping phone has a value
        else if (shippingPhone.val()) {
            billingPhone.val(shippingPhone.val()).trigger('change');
        }
    }

    // Initialize on page load
    syncPhoneNumbers();

    // Re-initialize when CheckoutWC updates the form
    $(document).on('cfw-after-tab-change', syncPhoneNumbers);
    $(document).on('cfw-after-customer-info-update', syncPhoneNumbers);
}); 