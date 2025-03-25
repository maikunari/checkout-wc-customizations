<?php
/**
 * Floating Cart Handler Class
 *
 * @package CheckoutWC_Customizations
 */

defined('ABSPATH') || exit;

class CKWC_Custom_Floating_Cart {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!defined('CFW_VERSION')) {
            return;
        }

        $cart_top_position = get_option('ckwc_custom_cart_top_position', 20);

        wp_enqueue_style(
            'ckwc-custom-floating-cart',
            CKWC_CUSTOM_PLUGIN_URL . 'assets/css/floating-cart.css',
            array(),
            CKWC_CUSTOM_VERSION
        );

        wp_add_inline_style('ckwc-custom-floating-cart', "
            #cfw-side-cart-floating-button {
                top: {$cart_top_position}px !important;
                bottom: unset !important;
                position: fixed !important;
                z-index: 999999;
            }
            
            /* Ensure the button stays in position even when cart is open */
            body.cfw-side-cart-open #cfw-side-cart-floating-button {
                top: {$cart_top_position}px !important;
                bottom: unset !important;
            }
        ");
    }
} 