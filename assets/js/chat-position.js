jQuery(function($) {
    'use strict';

    // Wait for Tidio to be ready
    document.addEventListener("tidioChat-ready", function() {
        // Give Tidio a moment to fully initialize
        setTimeout(function() {
            const iframe = document.getElementById('tidio-chat-iframe');
            
            if (!iframe) {
                console.log('Tidio iframe not found');
                return;
            }

            // Set up the mutation observer for cart open/close
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.target.classList.contains('cfw-side-cart-open')) {
                        // Add transition before transform for smooth opening
                        iframe.style.setProperty('transition', 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)', 'important');
                        // Small delay to ensure transition is applied
                        requestAnimationFrame(() => {
                            iframe.style.setProperty('transform', 'translateX(-500px)', 'important');
                        });
                    } else {
                        // Add transition before transform for smooth closing
                        iframe.style.setProperty('transition', 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)', 'important');
                        // Small delay to ensure transition is applied
                        requestAnimationFrame(() => {
                            iframe.style.setProperty('transform', 'none', 'important');
                        });
                        // Remove transition after animation completes
                        setTimeout(() => {
                            iframe.style.setProperty('transition', 'none', 'important');
                        }, 300);
                    }
                });
            });

            // Start observing the body for the cart class changes
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Handle initial state
            if (document.body.classList.contains('cfw-side-cart-open')) {
                iframe.style.setProperty('transform', 'translateX(-500px)', 'important');
            }

            console.log('Tidio chat position handler initialized');
        }, 1000); // Wait 1 second after Tidio is ready
    });
}); 