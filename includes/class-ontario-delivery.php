<?php
/**
 * Ontario Delivery Handler Class
 *
 * @package CheckoutWC_Customizations
 */

defined('ABSPATH') || exit;

/**
 * Class CKWC_Custom_Ontario_Delivery
 */
class CKWC_Custom_Ontario_Delivery {
    /**
     * Single instance of the class
     *
     * @var CKWC_Custom_Ontario_Delivery|null
     */
    protected static $instance = null;

    /**
     * Returns single instance of the class
     *
     * @return CKWC_Custom_Ontario_Delivery
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
        // Add scripts and styles to footer
        add_action('wp_print_footer_scripts', array($this, 'inject_geo_scripts'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Inject geolocation scripts
     */
    public function inject_geo_scripts() {
        if (!is_checkout()) {
            return;
        }

        $geo = \WPEngine\GeoIp::instance();
        $country = $geo->country();
        $region = $geo->region();

        // Add debug comment
        echo "<!-- Debug: Country = $country, Region = $region -->";

        wp_enqueue_script(
            'ckwc-ontario-delivery',
            CKWC_CUSTOM_PLUGIN_URL . 'assets/js/ontario-delivery.js',
            array('jquery'),
            CKWC_CUSTOM_VERSION,
            true
        );

        wp_localize_script(
            'ckwc-ontario-delivery',
            'ckwcOntarioDelivery',
            array(
                'isOntario' => ($country === 'CA' && $region === 'ON') ? 'true' : 'false'
            )
        );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_checkout()) {
            return;
        }

        wp_enqueue_script(
            'ckwc-custom-ontario-delivery',
            CKWC_CUSTOM_PLUGIN_URL . 'assets/js/ontario-delivery.js',
            array('jquery'),
            CKWC_CUSTOM_VERSION,
            true
        );

        wp_enqueue_style(
            'ckwc-custom-ontario-delivery',
            CKWC_CUSTOM_PLUGIN_URL . 'assets/css/ontario-delivery.css',
            array(),
            CKWC_CUSTOM_VERSION
        );
    }
}

// Initialize the class
CKWC_Custom_Ontario_Delivery::get_instance(); 