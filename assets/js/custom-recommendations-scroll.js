jQuery(function($) {
    var initComplete = false; // Flag to prevent multiple full initializations
    var scrollTimeout; // For debouncing scroll event
    var dotUpdateTimeout; // Separate timeout for dot updates
    var forceScrollInterval; // Interval handle for forcing scrollLeft

    // --- Function to Force Scroll Position to Start ---
    function forceScrollToStart($container) {
        if (!$container || !$container.length || !$container.is(':visible')) {
            // Don't try if container isn't present or visible
            return;
        }

        var attempts = 0;
        var maxAttempts = 10; // Try for up to 500ms (10 * 50ms)
        clearInterval(forceScrollInterval); // Clear any previous attempts

        console.log("Scroll Snap Fix: Starting checks/force scrollLeft to 0.");

        forceScrollInterval = setInterval(function() {
            attempts++;
            var currentScroll = $container[0].scrollLeft;

            // Only force if it's not already 0
            if (currentScroll !== 0) {
                console.log(`Scroll Snap Fix: Attempt ${attempts}. Forcing scrollLeft 0 from ${currentScroll}`);
                $container[0].scrollLeft = 0;

                // Optional: Check immediately if it stuck
                // if ($container[0].scrollLeft !== 0) {
                //     console.warn(`Scroll Snap Fix: Attempt ${attempts}. scrollLeft didn't stick immediately.`);
                // }
            }

            // Stop if it IS 0 or max attempts reached
            if ($container[0].scrollLeft === 0 || attempts >= maxAttempts) {
                clearInterval(forceScrollInterval);
                var finalScrollLeft = $container[0].scrollLeft;
                console.log(`Scroll Snap Fix: Stopped forcing scrollLeft after ${attempts} attempts. Final scrollLeft: ${finalScrollLeft}`);
                // Update dots based on the *final* position after trying to force it
                 triggerDotUpdate($container, finalScrollLeft); // Pass final scrollLeft
            }
        }, 50); // Check/force every 50ms
    }

    // --- Function to Update Active Dot ---
    function updateActiveDot(activeIndex) {
        var $dotContainer = $('#cfw-side-cart .ckwc-custom-dots');
        if (!$dotContainer.length) return;
        // console.log(`Scroll Snap Dots: Updating active dot to ${activeIndex}`); // Optional log
        $dotContainer.children('li').removeClass('active')
            .eq(activeIndex).addClass('active');
    }

     // --- Function to Trigger Dot Update (Debounced) ---
     // Accepts optional knownScrollLeft to avoid re-reading during forceScrollToStart callback
     function triggerDotUpdate($container, knownScrollLeft = null) {
         if (!$container || !$container.length) return;
         clearTimeout(dotUpdateTimeout);
         dotUpdateTimeout = setTimeout(function() {
             var containerWidth = $container[0].clientWidth;
             // Use knownScrollLeft if provided, otherwise read current scrollLeft
             var scrollLeft = (knownScrollLeft !== null) ? knownScrollLeft : $container[0].scrollLeft;
             var slideCount = $container.children('.ckwc-custom-scroll-slide').length;
             // Prevent division by zero if containerWidth is 0
             var activeIndex = (containerWidth > 0) ? Math.round(scrollLeft / containerWidth) : 0;
             // Ensure index is within bounds
             activeIndex = Math.max(0, Math.min(activeIndex, slideCount - 1));
             updateActiveDot(activeIndex);
         }, 60); // Slightly increased debounce
     }

    // --- Main Initialization Function ---
    function initializeRecommendationsSlider() {
        if (initComplete) return;

        var $container = $('#cfw-side-cart .ckwc-custom-scroll-snap-container');
        var $dotContainer = $('#cfw-side-cart .ckwc-custom-dots');
        var $slides = $container.children('.ckwc-custom-scroll-slide');

        if (!$container.length || !$slides.length || !$dotContainer.length) {
            return; // Not ready
        }

        console.log("Scroll Snap Init: Elements found. Initializing dots and scroll listener...");
        initComplete = true; // Mark before potentially long-running tasks

        var slideCount = $slides.length;

        // 1. Generate Dots
        $dotContainer.empty();
        for (var i = 0; i < slideCount; i++) {
            $('<li><button type="button" data-slide-goto="' + i + '"></button></li>').appendTo($dotContainer);
        }

        // 2. Dot Click Handler
        $dotContainer.off('click.ckwcRecs').on('click.ckwcRecs', 'button', function(e) { // Use namespaced event
            e.preventDefault();
            if (!$container.length) return;
            var targetIndex = $(this).data('slide-goto');
            var slideWidth = $container[0].clientWidth;
            if (slideWidth > 0) { // Avoid scrolling if width is 0
                var targetScrollLeft = targetIndex * slideWidth;
                $container[0].scrollTo({ left: targetScrollLeft, behavior: 'smooth' });
                updateActiveDot(targetIndex); // Update dot immediately
            }
        });

        // 3. Update Active Dot on Scroll
        $container.off('scroll.ckwcRecs').on('scroll.ckwcRecs', function() { // Use namespaced event
             triggerDotUpdate($container);
        });

         // 4. Force Scroll Position AFTER potential animations
         // Increased delay significantly
         setTimeout(function() {
             forceScrollToStart($container);
         }, 350); // <<< INCREASED DELAY (try 350ms, adjust up/down if needed)

    } // End initializeRecommendationsSlider

    // --- Observer/Interval Logic to Trigger Initialization ---
    var targetNode = document.getElementById('cfw-side-cart');
    if (targetNode) {
        var observer = new MutationObserver(function(mutationsList, observer) {
             if (initComplete && !document.contains($container[0])) { // Check if container removed
                 console.log("Scroll Snap Init: Container removed, resetting init flag.");
                 initComplete = false;
                 clearInterval(forceScrollInterval);
                 $('#cfw-side-cart .ckwc-custom-dots').off('click.ckwcRecs'); // Clean up listeners
                 $('#cfw-side-cart .ckwc-custom-scroll-snap-container').off('scroll.ckwcRecs');
             }

            if (!initComplete && $('#cfw-side-cart .ckwc-custom-scroll-snap-container').length) {
                 setTimeout(initializeRecommendationsSlider, 150); // Slightly longer delay after detection
            }
        });
        observer.observe(targetNode, { childList: true, subtree: true });
        console.log("Scroll Snap Init: MutationObserver watching #cfw-side-cart.");
        setTimeout(initializeRecommendationsSlider, 250); // Initial check

    } else {
        console.warn("Scroll Snap Init: Could not find #cfw-side-cart to observe.");
        // Add fallback interval if needed
    }
});