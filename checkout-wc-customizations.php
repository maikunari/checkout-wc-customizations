<?php
/**
 * Plugin Name: CheckoutWC Customizations-99
 * Plugin URI: https://sonicpixel.ca
 * Description: Custom modifications for CheckoutWC including phone number sync and Ontario delivery options
 * Version: 1.0.0
 * Author: Michael Sewell
 * Author URI: https://sonicpixel.ca
 * Text Domain: checkout-wc-customizations
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 *
 * @package CheckoutWC_Customizations
 */

defined('ABSPATH') || exit;

// Plugin constants
define('CKWC_CUSTOM_VERSION', '1.0.0');
define('CKWC_CUSTOM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CKWC_CUSTOM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CKWC_CUSTOM_MIN_PHP_VERSION', '7.4');

/**
 * Check if PHP version meets minimum requirements
 *
 * @return bool
 */
function ckwc_custom_check_php_version() {
    if (version_compare(PHP_VERSION, CKWC_CUSTOM_MIN_PHP_VERSION, '<')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . 
                 sprintf(
                     /* translators: %s: Minimum PHP version */
                     __('CheckoutWC Customizations requires PHP version %s or higher.', 'checkout-wc-customizations'),
                     CKWC_CUSTOM_MIN_PHP_VERSION
                 ) . 
                 '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Check if WooCommerce and CheckoutWC are active
 */
function ckwc_custom_check_dependencies() {
    if (!ckwc_custom_check_php_version()) {
        return false;
    }

    if (is_admin() && current_user_can('activate_plugins')) {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>' . 
                     __('CheckoutWC Customizations requires WooCommerce to be installed and active.', 'checkout-wc-customizations') . 
                     '</p></div>';
            });
            return false;
        }

        if (!defined('CFW_VERSION')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>' . 
                     __('CheckoutWC Customizations requires CheckoutWC to be installed and active.', 'checkout-wc-customizations') . 
                     '</p></div>';
            });
            return false;
        }
    }
    return true;
}

/**
 * Initialize the plugin
 */
function ckwc_custom_init() {
    if (!ckwc_custom_check_dependencies()) {
        return;
    }

    // Load plugin textdomain
    load_plugin_textdomain(
        'checkout-wc-customizations',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );

    // Include required files
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-settings-handler.php';
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-phone-handler.php';
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-ontario-delivery.php';
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-floating-cart-handler.php';
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-chat-position-handler.php';
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-recommendations-handler.php';

    // Initialize classes
    new CKWC_Custom_Settings();
    
    // Only initialize features if they're enabled in settings
    if (get_option('ckwc_custom_phone_sync_enabled', 1)) {
        new CKWC_Custom_Phone_Handler();
    }
    
    if (get_option('ckwc_custom_ontario_delivery_enabled', 1)) {
        new CKWC_Custom_Ontario_Delivery();
    }
    
    // Always initialize floating cart handler as it's controlled by CSS
    new CKWC_Custom_Floating_Cart();

    // Initialize chat position handler
    new CKWC_Custom_Chat_Position();

    // Initialize our recommendations tweaks
    new CKWC_Custom_Recommendations();
}
add_action('plugins_loaded', 'ckwc_custom_init', 20);

/**
 * Activation hook
 */
function ckwc_custom_activate() {
    if (!ckwc_custom_check_php_version()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            sprintf(
                /* translators: %s: Minimum PHP version */
                __('CheckoutWC Customizations requires PHP version %s or higher.', 'checkout-wc-customizations'),
                CKWC_CUSTOM_MIN_PHP_VERSION
            ),
            'Plugin dependency check',
            array('back_link' => true)
        );
    }

    if (!ckwc_custom_check_dependencies()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('CheckoutWC Customizations requires WooCommerce and CheckoutWC to be installed and active.', 'checkout-wc-customizations'),
            'Plugin dependency check',
            array('back_link' => true)
        );
    }
    
    // Set default options if they don't exist
    if (false === get_option('ckwc_custom_cart_top_position')) {
        add_option('ckwc_custom_cart_top_position', 20);
    }
    if (false === get_option('ckwc_custom_ontario_delivery_enabled')) {
        add_option('ckwc_custom_ontario_delivery_enabled', 1);
    }
    if (false === get_option('ckwc_custom_phone_sync_enabled')) {
        add_option('ckwc_custom_phone_sync_enabled', 1);
    }
}
register_activation_hook(__FILE__, 'ckwc_custom_activate'); 