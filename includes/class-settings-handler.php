<?php
/**
 * Settings Handler Class
 *
 * @package CheckoutWC_Customizations
 */

defined('ABSPATH') || exit;

class CKWC_Custom_Settings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            __('CheckoutWC Customizations', 'checkout-wc-customizations'),
            __('CheckoutWC Customizations', 'checkout-wc-customizations'),
            'manage_options',
            'ckwc-customizations',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('ckwc_custom_settings', 'ckwc_custom_cart_top_position');
        register_setting('ckwc_custom_settings', 'ckwc_custom_ontario_delivery_enabled');
        register_setting('ckwc_custom_settings', 'ckwc_custom_phone_sync_enabled');
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings if data was posted
        if (isset($_POST['ckwc_custom_settings_submit'])) {
            check_admin_referer('ckwc_custom_settings_nonce');
            
            $cart_top_position = isset($_POST['ckwc_custom_cart_top_position']) ? 
                absint($_POST['ckwc_custom_cart_top_position']) : 20;
            update_option('ckwc_custom_cart_top_position', $cart_top_position);

            $ontario_delivery_enabled = isset($_POST['ckwc_custom_ontario_delivery_enabled']) ? 1 : 0;
            update_option('ckwc_custom_ontario_delivery_enabled', $ontario_delivery_enabled);

            $phone_sync_enabled = isset($_POST['ckwc_custom_phone_sync_enabled']) ? 1 : 0;
            update_option('ckwc_custom_phone_sync_enabled', $phone_sync_enabled);
            
            add_settings_error(
                'ckwc_custom_settings',
                'settings_updated',
                __('Settings saved successfully.', 'checkout-wc-customizations'),
                'updated'
            );
        }

        // Get current settings
        $cart_top_position = get_option('ckwc_custom_cart_top_position', 20);
        $ontario_delivery_enabled = get_option('ckwc_custom_ontario_delivery_enabled', 1);
        $phone_sync_enabled = get_option('ckwc_custom_phone_sync_enabled', 1);

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('CheckoutWC Customizations Settings', 'checkout-wc-customizations'); ?></h1>
            <?php settings_errors('ckwc_custom_settings'); ?>

            <form method="post" action="">
                <?php wp_nonce_field('ckwc_custom_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <?php echo esc_html__('Floating Cart Top Position', 'checkout-wc-customizations'); ?>
                        </th>
                        <td>
                            <input type="number" 
                                   name="ckwc_custom_cart_top_position" 
                                   value="<?php echo esc_attr($cart_top_position); ?>" 
                                   class="small-text" 
                                   min="0"
                            /> px
                            <p class="description">
                                <?php echo esc_html__('The position from the top of the screen in pixels.', 'checkout-wc-customizations'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php echo esc_html__('Enable Ontario Delivery Options', 'checkout-wc-customizations'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="ckwc_custom_ontario_delivery_enabled" 
                                       value="1" 
                                       <?php checked(1, $ontario_delivery_enabled); ?>
                                />
                                <?php echo esc_html__('Show/hide delivery options based on Ontario address', 'checkout-wc-customizations'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php echo esc_html__('Enable Phone Number Sync', 'checkout-wc-customizations'); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="ckwc_custom_phone_sync_enabled" 
                                       value="1" 
                                       <?php checked(1, $phone_sync_enabled); ?>
                                />
                                <?php echo esc_html__('Synchronize shipping and billing phone numbers', 'checkout-wc-customizations'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" 
                           name="ckwc_custom_settings_submit" 
                           class="button button-primary" 
                           value="<?php echo esc_attr__('Save Changes', 'checkout-wc-customizations'); ?>"
                    />
                </p>
            </form>
        </div>
        <?php
    }
} 