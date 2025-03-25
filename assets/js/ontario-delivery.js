jQuery(function($) {
    'use strict';

    // Function to check if shipping is to Ontario
    function isOntarioShipping() {
        const shippingState = $('#shipping_state').val();
        return shippingState === 'ON';
    }

    // Function to toggle delivery options visibility
    function toggleDeliveryOptions() {
        const isOntario = isOntarioShipping();
        const deliveryMethod = $('#cfw-delivery-method');
        const deliveryHeading = $('.cfw-delivery-method-heading');

        if (isOntario) {
            deliveryMethod.removeClass('hidden');
            deliveryHeading.removeClass('hidden');
        } else {
            deliveryMethod.addClass('hidden');
            deliveryHeading.addClass('hidden');
        }
    }

    // Initialize on page load
    toggleDeliveryOptions();

    // Watch for changes to the shipping state
    $(document).on('change', '#shipping_state', toggleDeliveryOptions);

    // Re-check when CheckoutWC updates the form
    $(document).on('cfw-after-tab-change', toggleDeliveryOptions);
    $(document).on('cfw-after-customer-info-update', toggleDeliveryOptions);
}); 