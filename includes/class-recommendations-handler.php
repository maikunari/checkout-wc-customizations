<?php
/**
 * Recommendations Handler Class
 * Replaces the default CheckoutWC side-cart recommendations with a custom CSS Scroll Snap container
 * showing stacked product pairs.
 *
 * @package CheckoutWC_Customizations
 */
defined('ABSPATH') || exit;

class CKWC_Custom_Recommendations {

    public function __construct() {
        // Check if WooCommerce is available before setting up hooks
        if (!function_exists('WC') || !class_exists('WooCommerce')) {
            return;
        }
        
        // ONLY add hooks if it's NOT an AJAX request
        if ( ! wp_doing_ajax() ) {
            // error_log('CKWC Custom Recs (Scroll Snap): Adding hooks (NOT AJAX)');

            // Prevent CheckoutWC's React component from getting suggested products data on initial load
            add_filter( 'cfw_checkout_data', [ $this, 'modify_side_cart_data_for_custom_slider' ], 20 );

            // Output our custom scroll snap HTML in the side cart on initial load
            add_action( 'cfw_after_side_cart_proceed_to_checkout_button', [ $this, 'output_custom_slider_html' ] );

            // Enqueue CSS for our custom scroll snap container on initial load
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 100 ); // Use high priority

        } else {
             // error_log('CKWC Custom Recs (Scroll Snap): Skipping hooks (IS AJAX)');
        }
    }

    /**
     * Fetches up to 6 suggested products (cross-sells first, then random).
     * This function is called directly by output_custom_slider_html.
     *
     * @param array $original_products (Ignored)
     * @param int $limit (Ignored, we force 6)
     * @param bool $random_fallback If true, fills remaining slots with random products.
     * @return WC_Product[] Array of product objects.
     */
    public function increase_suggested_products( $original_products = [], $limit = 3, $random_fallback = false ): array {
        // error_log('CKWC Custom Recs (Scroll Snap): increase_suggested_products running internally.');
        
        // Check if WooCommerce functions are available
        if (!function_exists('WC') || !WC() || !WC()->cart) {
            return [];
        }

        $desired = 6; // We always want 6 for our custom display

        $cart_items      = WC()->cart->get_cart();
        $cart_item_ids   = empty($cart_items) ? [] : array_map( fn( $item ) => $item['data']->get_id(), $cart_items );
        $cross_sell_ids  = WC()->cart->get_cross_sells();
        $new_products    = [];
        $found_ids       = [];

        foreach ( $cross_sell_ids as $id ) {
            if ( count( $new_products ) >= $desired ) break;
            if ( in_array( $id, $cart_item_ids, true ) ) continue;
            if ( in_array( $id, $found_ids, true ) ) continue;

            $p = wc_get_product( $id );
            if ( $p && $p->get_status() === 'publish' && $p->is_in_stock() && $p->is_purchasable() && $p->is_visible() ) {
                $new_products[] = $p;
                $found_ids[] = $p->get_id();
            }
        }

        if ( count( $new_products ) < $desired && $random_fallback ) {
            // Check if wc_get_products function exists before using it
            if (!function_exists('wc_get_products')) {
                return $new_products;
            }
            
            $exclude_ids = array_unique( array_merge( $cart_item_ids, $found_ids ) );
            $random_args = [
                'limit'        => $desired - count( $new_products ),
                'exclude'      => $exclude_ids,
                'status'       => 'publish',
                'orderby'      => 'rand',
                'stock_status' => 'instock',
                'return'       => 'objects',
                'visibility'   => 'visible',
            ];
            $random = wc_get_products( $random_args );
            $purchasable_random = array_filter($random, fn($p) => $p instanceof WC_Product && $p->is_purchasable());
            $new_products = array_merge( $new_products, $purchasable_random );
        }

        $new_products = array_filter($new_products, fn($p) => $p instanceof WC_Product);
        // error_log('CKWC Custom Recs (Scroll Snap): increase_suggested_products returning ' . count($new_products) . ' products.');
        return array_slice( $new_products, 0, $desired );
    }

    /**
     * Remove suggested products data from the localized object (runs only on page load).
     */
    public function modify_side_cart_data_for_custom_slider( $data ) {
        // error_log('CKWC Custom Recs (Scroll Snap): modify_side_cart_data_for_custom_slider filter running.');
        if ( isset( $data['side_cart']['suggested_products'] ) ) {
            $data['side_cart']['suggested_products'] = [];
            // error_log('CKWC Custom Recs (Scroll Snap): Default suggested_products emptied.');
        }
        return $data;
    }


    /**
     * Output the HTML structure for our custom scroll snap container AND dot navigation.
     */
    public function output_custom_slider_html() {
        // error_log('CKWC Custom Recs (Scroll Snap): output_custom_slider_html action running.');
        
        // Ensure WooCommerce is available before proceeding
        if (!function_exists('WC') || !WC() || !function_exists('wc_get_product')) {
            return;
        }
        
        $products = $this->increase_suggested_products( [], 6, true );
        // error_log('CKWC Custom Recs (Scroll Snap): Found ' . count($products) . ' products.');
        if ( empty( $products ) ) return;
    
        echo '<div class="ckwc-custom-recommendations">';
        echo '<h3>' . esc_html__( 'You may also like...', 'checkout-wc-customizations' ) . '</h3>';
        echo '<div class="ckwc-custom-scroll-snap-container">'; // Scroll container

        $product_pairs = array_chunk( $products, 2 );
        foreach ( $product_pairs as $index => $pair ) {
            echo '<div class="ckwc-custom-scroll-slide" data-slide-index="' . esc_attr( $index ) . '">'; // Slide
            foreach ( $pair as $product ) {
                // ** START MODIFIED PRODUCT STRUCTURE **
                echo '<div class="ckwc-custom-product ckwc-layout-flex">'; // Add new layout class
    
                    // Column 1: Image
                    echo '<div class="ckwc-product-image">';
                    echo '<a href="' . esc_url( $product->get_permalink() ) . '">';
                    echo $product->get_image( 'woocommerce_thumbnail' ); // Adjust size if needed
                    echo '</a>';
                    echo '</div>'; // End image column
    
                    // Column 2: Details (Title, Price, Button)
                    echo '<div class="ckwc-product-details">';
                    echo '<h5 class="ckwc-product-title">'; // Use a specific class
                    echo '<a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a>'; // Link the title
                    echo '</h5>';
                    echo $product->get_price_html(); // Output price
                    // --- START INSERTED DEBUGGING AND CONDITIONAL ---
                // ** DEBUGGING **
                $is_purchasable = $product->is_purchasable();
                $product_type = $product->get_type();
                // error_log("CKWC Recs Product Debug: ID {$product->get_id()}, Type: {$product_type}, Purchasable: " . ($is_purchasable ? 'Yes' : 'No'));
                // ** END DEBUGGING **
                    // Add to cart button - wrapped for potential styling
                    echo '<div class="ckwc-add-to-cart-wrap">'; // Keep the wrapper div
                // Check purchasability before calling the template function
                if ($is_purchasable) {
                    // woocommerce_template_loop_add_to_cart(['product' => $product]);

                    // Manually generate the add-to-cart link/button HTML
                    echo apply_filters(
                        'woocommerce_loop_add_to_cart_link', // Apply the same filters WC would
                        sprintf(
                            '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                            esc_url($product->add_to_cart_url()),
                            esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
                            // Add 'ajax_add_to_cart' class for simple products
                            esc_attr(isset($args['class']) ? $args['class'] : 'button add_to_cart_button' . ($product->is_type('simple') ? ' ajax_add_to_cart' : '')),
                            // Generate necessary data attributes
                            isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : 'data-product_id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" aria-label="' . esc_attr($product->add_to_cart_description()) . '" rel="nofollow"',
                            // Get the appropriate button text (Add to cart / Select options etc)
                            esc_html($product->add_to_cart_text())
                        ),
                        $product,
                        isset($args) ? $args : [] // Pass empty args array
                    );


                } else {
                    // Log if the button is skipped because product isn't purchasable
                     // error_log("CKWC Recs Product Debug: ID {$product->get_id()} is not purchasable, button skipped.");
                     // Optional: Output a 'Read More' or similar link as a fallback
                     // echo '<a href="' . esc_url( $product->get_permalink() ) . '" class="button disabled wc-forward">Read More</a>';
                }
                echo '</div>'; // Close the wrapper div
                 // --- END INSERTED DEBUGGING AND CONDITIONAL ---

                echo '</div>'; // End details column
    
                echo '</div>'; // End .ckwc-custom-product
                 // ** END MODIFIED PRODUCT STRUCTURE **
            }
            echo '</div>'; // End scroll slide
        }
        echo '</div>'; // End scroll container
        echo '<ul class="ckwc-custom-dots"></ul>'; // Keep dots
        echo '</div>'; // End recommendations container
        // error_log('CKWC Custom Recs (Scroll Snap): Finished outputting custom slider HTML.');
    }


    /**
     * Enqueue CSS and updated JS file name.
     */
    public function enqueue_scripts() {
        if ( ! defined( 'CFW_VERSION' ) || is_admin() || wp_doing_ajax() ) {
            return;
        }
        // error_log('CKWC Custom Recs (Scroll Snap): Enqueuing Scroll Snap CSS & JS');

        // CSS (keep the simplified version v2 from before, potentially re-add float:none if needed)
        $scroll_snap_css = "
        /* ============================================= */
        /* Custom Recommendations Scroll Snap Styles v3  */
        /* ============================================= */

        /* Container & Slide styles (mostly unchanged) */
        .ckwc-custom-recommendations .ckwc-custom-scroll-snap-container { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; }
        .ckwc-custom-recommendations .ckwc-custom-scroll-snap-container::-webkit-scrollbar { display: none; }
        .ckwc-custom-recommendations .ckwc-custom-scroll-snap-container .ckwc-custom-scroll-slide { flex: 0 0 100%; width: 100%; scroll-snap-align: start; /* float: none !important; */ display: block !important; box-sizing: border-box; }

        
        /* ============================================= */
        /* Product Layout Styles                         */
        /* ============================================= */
        .ckwc-custom-recommendations { margin: 20px 0; padding: 0 15px; border-top: 1px solid #eee; padding-top: 20px; }
        .ckwc-custom-recommendations h3 { font-size: 1.1em; margin-bottom: 15px; text-align: center; }

        /* Flex Container for each Product */
        .ckwc-custom-product.ckwc-layout-flex {
            display: flex;
            align-items: center; /* Vertically align items in the middle */
            gap: 15px; /* Space between image and details */
            margin-bottom: 20px;
            text-align: left; /* Align text left in details column */
        }
        .ckwc-custom-product.ckwc-layout-flex:last-child {
            margin-bottom: 0;
        }

        /* Image Column */
        .ckwc-product-image {
            flex: 0 0 80px; /* Fixed width for image column, adjust as needed */
            max-width: 80px;
        }
        .ckwc-product-image img {
            display: block;
            width: 100%;
            height: auto;
        }

        /* Details Column */
        .ckwc-product-details {
            display: block;
            width: 100%;
        }
        .ckwc-product-title { /* Renamed from woocommerce-loop-product__title for specificity */
            font-size: 1em;
            margin: 0 0 5px 0; /* Adjust spacing */
            line-height: 1.3;
            font-weight: normal;
        }

        .ckwc-product-details .woocommerce-Price-amount {   
       
        }

        /* Ensure del (original) and ins (sale) display inline */
        .ckwc-product-details del,
        .ckwc-product-details ins {
            display: inline-block !important; /* Force inline display */
            flex: none !important; /* Prevent flex growth/shrinking */
            vertical-align: baseline; /* Align text nicely */
        }
        /* Add spacing after the original price */
        .ckwc-product-details del {
            margin-right: 0.5em;
            opacity: 0.7; /* Optional: slightly fade original price */
        }
        /* Style the sale price */
        .ckwc-product-details ins {
            text-decoration: none; /* Sale price usually isn't underlined */
            font-weight: bold; /* Make sale price stand out */
            /* Add color if desired: color: #some_sale_color; */
        }
        /* WC screen-reader text should already be visually hidden by WC core styles */
        /* We usually don't need to add rules for .screen-reader-text here */


        .ckwc-add-to-cart-wrap {
            margin-top: auto; /* Push button towards the bottom if details column has extra space */
            padding-top: 10px;
        }
        .ckwc-add-to-cart-wrap .button {
            /* Example: Make button slightly smaller if needed */
            /* padding: 6px 12px; font-size: 0.9em; */
            width: 100%; /* Make button full width of details column */
            box-sizing: border-box;
        }
        .ckwc-add-to-cart-wrap.added_to_cart .wc-forward {
            display: none !important;
        }    


        /* ============================================= */
        /* Dot Navigation Styles (Unchanged)             */
        /* ============================================= */
        .ckwc-custom-dots { list-style: none; padding: 0; margin: 10px 0 0 0; text-align: center; }
        .ckwc-custom-dots li { display: inline-block; margin: 0 4px; }
        .ckwc-custom-dots button { background: #ccc; border: none; width: 10px; height: 10px; border-radius: 50%; padding: 0; cursor: pointer; transition: background 0.3s ease; }
        .ckwc-custom-dots li.active button { background: #555; }
        ";
        wp_register_style( 'ckwc-custom-recs-style', false );
        wp_enqueue_style( 'ckwc-custom-recs-style' );
        wp_add_inline_style( 'ckwc-custom-recs-style', $scroll_snap_css );

        // Enqueue the renamed JS file
        wp_enqueue_script(
            'ckwc-custom-recommendations-scroll', // Updated handle
            CKWC_CUSTOM_PLUGIN_URL . 'assets/js/custom-recommendations-scroll.js', // Updated filename
            ['jquery'],
            CKWC_CUSTOM_VERSION,
            true
        );
    }

} // End class