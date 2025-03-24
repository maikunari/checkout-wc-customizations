jQuery(document).ready(function($) {
    // Function to sync shipping phone to billing phone
    function syncPhoneNumbers() {
        const shippingPhone = $('#shipping_phone').val();
        const billingPhone = $('#billing_phone');

        // Only sync if billing phone is empty
        if (billingPhone.val() === '') {
            billingPhone.val(shippingPhone);
        }
    }

    // Watch for changes to the shipping phone field
    $(document).on('change', '#shipping_phone', function() {
        syncPhoneNumbers();
    });

    // Watch for changes to the billing checkbox
    $(document).on('change', '#billing_same_as_shipping', function() {
        if (!$(this).is(':checked')) {
            // When "Use different billing address" is selected
            syncPhoneNumbers();
        }
    });

    // Watch for CheckoutWC's dynamic updates
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                syncPhoneNumbers();
            }
        });
    });

    // Start observing the checkout form
    const checkoutForm = document.getElementById('cfw-checkout-main');
    if (checkoutForm) {
        observer.observe(checkoutForm, { 
            childList: true, 
            subtree: true 
        });
    }
}); 