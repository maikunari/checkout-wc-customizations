# CheckoutWC Customizations

A WordPress plugin that extends CheckoutWC functionality with custom features.

## Features

- **Floating Cart Position**: Customize the position of the floating cart button from the top of the screen
- **Phone Number Sync**: Automatically synchronize phone numbers between shipping and billing forms
- **Ontario Delivery Options**: Show/hide delivery options based on whether the shipping address is in Ontario
- **Tidio Chat Integration**: Smoothly animates the Tidio chat widget when the floating cart opens/closes to prevent overlap
- **Custom Side-Cart Recommendations**: Replaces the default "You may also like..." slider with a custom CSS scroll-snap version showing 6 products (2 stacked per view) with dot navigation.

## Requirements

- WordPress 5.8+
- WooCommerce 6.0+
- CheckoutWC (latest version)
- PHP 7.4 - 8.2

## Installation

1. Download or clone this repository
2. Upload to your WordPress plugins directory (`wp-content/plugins/`)
3. Activate the plugin through the WordPress admin interface

## Configuration

Navigate to WooCommerce → CheckoutWC Customizations in your WordPress admin panel to configure:

- Floating cart button position (distance from top in pixels)
- Enable/disable phone number synchronization
- Enable/disable Ontario delivery options

## Development

This plugin is built to extend CheckoutWC functionality while maintaining compatibility with future updates. It uses:

- WordPress coding standards
- Modern PHP practices (PHP 7.4+ compatible)
- CheckoutWC hooks and filters
- Vanilla JavaScript with jQuery

## License

GPL-2.0+ 