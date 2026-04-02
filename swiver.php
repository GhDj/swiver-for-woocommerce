<?php
/**
 * Plugin Name:     Swiver for WooCommerce
 * Plugin URI:
 * Description: The Swiver extension for WordPress is a powerful tool designed to seamlessly integrate your company’s website with the Swiver invoicing platform
 * Author:          Swiver
 * Author URI:      https://swiver.io
 * Text Domain:     swiver-for-woocommerce
 * Domain Path:     /languages
 * Version:         1.0.0
 * WC tested up to: 9.5.2
 * Requires plugins: woocommerce
 * License:         GPLv3 or later
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 6.0
 * WC HPOS Compatibility: true
 */

namespace Swiver\Swiver_WooCommerce;

use Swiver\Swiver_WooCommerce\Admin\Swiver_Settings;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'SWIVER_VERSION', '1.0.0' );
define( 'SWIVER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SWIVER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SWIVER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define('SWIVER_API_URL', 'https://server.swiver.io/open_api/');

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );



// Autoloader
require_once plugin_dir_path(__FILE__) . 'inc/Autoloader.php';
\Swiver\Swiver_WooCommerce\Autoloader::register();

// Load plugin text domain for translations
function swiver_load_textdomain() {
	$domain = 'swiver-for-woocommerce';

	$locale = apply_filters( 'plugin_locale', is_admin() ? get_user_locale() : get_locale(), $domain );

	$mofile = $domain . '-' . $locale . '.mo';

	$path = '/'.trim( SWIVER_PLUGIN_DIR . 'languages', '/' );

	return load_textdomain( $domain, $path . '/' . $mofile );
}

add_action('plugins_loaded',  __NAMESPACE__ . '\\init' );

require_once SWIVER_PLUGIN_DIR . 'inc/admin/Swiver_Settings.php';


function init() {
    swiver_load_textdomain();
	Swiver_WooCommerce::get_instance();
}
if (is_admin()) {
	new Swiver_Settings();
} else {
	$order_sync = new \Swiver\Swiver_WooCommerce\Swiver_Order_Sync();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\swiver_for_woocommerce_activate');

function swiver_for_woocommerce_activate() {
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		deactivate_plugins(plugin_basename(__FILE__));

        wp_die(  esc_html(__('Swiver for WooCommerce requires WooCommerce to be installed and activated.', 'swiver-for-woocommerce')) );
    }
}

register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\swiver_for_woocommerce_deactivate');

function swiver_for_woocommerce_deactivate() {
	// Delete all plugin options
	delete_option('swiver_token');
	delete_option('swiver_api_retrieved_data');

	// Clear the helper cache
	if (class_exists('Swiver\Swiver_WooCommerce\Swiver_Helper')) {
		Swiver_Helper::clear_options_cache();
	}

	// Log the deactivation
	error_log(__('Swiver for WooCommerce has been deactivated and all data has been cleared.', 'swiver-for-woocommerce'));
}

add_action('admin_notices', __NAMESPACE__ . '\\swiver_for_woocommerce_admin_notice');

function swiver_for_woocommerce_admin_notice() {
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		echo '<div class="error"><p>'. esc_html(__('Swiver for WooCommerce requires WooCommerce to be installed and activated.', 'swiver-for-woocommerce')) . '</p></div>';
    }
}