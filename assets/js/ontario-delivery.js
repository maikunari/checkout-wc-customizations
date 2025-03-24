jQuery(document).ready(function($) {
    // Add a class to the <h3> that immediately precedes #cfw-delivery-method
    $('#cfw-delivery-method').prev('h3').addClass('cfw-delivery-method-heading');

    // Function to update visibility based on location
    function updateDeliveryVisibility() {
        if (typeof ckwcOntarioDelivery !== 'undefined' && ckwcOntarioDelivery.isOntario === 'true') {
            $('#cfw-delivery-method').removeClass('hidden').addClass('visible');
            $('.cfw-delivery-method-heading').removeClass('hidden');
        } else {
            $('#cfw-delivery-method').removeClass('visible').addClass('hidden');
            $('.cfw-delivery-method-heading').addClass('hidden');
        }
    }

    // Run initially
    updateDeliveryVisibility();

    // Observe DOM changes in case CheckoutWC reloads the checkout
    const observer = new MutationObserver(function() {
        updateDeliveryVisibility();
    });
    
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
}); 