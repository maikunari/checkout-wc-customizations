<?php
/**
 * Phone Handler Class
 *
 * @package CheckoutWC_Customizations
 */

defined('ABSPATH') || exit;

/**
 * Class CKWC_Phone_Handler
 */
class CKWC_Phone_Handler {
    /**
     * Single instance of the class
     *
     * @var CKWC_Phone_Handler|null
     */
    protected static $instance = null;

    /**
     * Returns single instance of the class
     *
     * @return CKWC_Phone_Handler
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    protected function init_hooks() {
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue necessary scripts
     */
    public function enqueue_scripts() {
        if (!is_checkout()) {
            return;
        }

        wp_enqueue_script(
            'ckwc-phone-sync',
            CKWC_CUSTOM_PLUGIN_URL . 'assets/js/phone-sync.js',
            array('jquery'),
            CKWC_CUSTOM_VERSION,
            true
        );
    }
}

// Initialize the class
CKWC_Phone_Handler::get_instance(); 