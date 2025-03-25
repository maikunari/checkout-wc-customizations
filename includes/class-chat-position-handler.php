<?php
/**
 * Chat Position Handler Class
 *
 * @package CheckoutWC_Customizations
 */

defined('ABSPATH') || exit;

class CKWC_Custom_Chat_Position {
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

        wp_enqueue_script(
            'ckwc-custom-chat-position',
            CKWC_CUSTOM_PLUGIN_URL . 'assets/js/chat-position.js',
            array('jquery'),
            CKWC_CUSTOM_VERSION,
            true
        );
    }
} 