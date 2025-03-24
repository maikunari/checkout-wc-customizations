<?php
/**
 * Plugin Name: CheckoutWC Customizations
 * Plugin URI: https://sonicpixel.ca
 * Description: Custom modifications for CheckoutWC including phone number sync and Ontario delivery options
 * Version: 1.0.0
 * Author: Michael Sewell
 * Author URI: https://sonicpixel.ca
 * Text Domain: checkout-wc-customizations
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.0
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

/**
 * Check if WooCommerce and CheckoutWC are active
 */
function ckwc_custom_check_dependencies() {
    if (is_admin() && current_user_can('activate_plugins')) {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>' . 
                     __('CheckoutWC Customizations requires WooCommerce to be installed and active.', 'checkout-wc-customizations') . 
                     '</p></div>';
            });
            return false;
        }

        if (!class_exists('CFW')) {
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
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-phone-handler.php';
    require_once CKWC_CUSTOM_PLUGIN_DIR . 'includes/class-ontario-delivery.php';
}
add_action('plugins_loaded', 'ckwc_custom_init', 20);

/**
 * Activation hook
 */
function ckwc_custom_activate() {
    if (!ckwc_custom_check_dependencies()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('CheckoutWC Customizations requires WooCommerce and CheckoutWC to be installed and active.', 'checkout-wc-customizations'),
            'Plugin dependency check',
            array('back_link' => true)
        );
    }
}
register_activation_hook(__FILE__, 'ckwc_custom_activate'); 