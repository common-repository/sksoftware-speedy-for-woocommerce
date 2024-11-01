<?php

/**
 *
 * @link              https://sk-soft.net
 * @since             1.0.0
 * @package           Sksoftware_Speedy_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:          SKSoftware Speedy for WooCommerce
 * Plugin URI:           https://sk-soft.net/plugins/speedy-for-woocommerce/
 * Description:          This plugin integrates Speedy shipping method for WooCommerce.
 * Version:              1.1.1
 * Author:               Simeon Kolev & Martin Shterev from SK Software Ltd.
 * Author URI:           https://sk-soft.net
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          sksoftware-speedy-for-woocommerce
 * Domain Path:          /languages
 * Tested up to:         6.4
 * WC requires at least: 3.5
 * WC tested up to:      8.5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_VERSION', '1.1.0' );

/**
 * Define API host
 */
if ( false === defined( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST' ) ) {
	define( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST', 'https://shipping.sk-soft.net' );
}
if ( false === defined( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_SSL_VERIFY' ) ) {
	define( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_SSL_VERIFY', true );
}
if ( false === defined( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_TIMEOUT' ) ) {
	define( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_TIMEOUT', 15 );
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sksoftware-speedy-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function sksoftware_speedy_for_woocommerce_run() {
	$plugin = new Sksoftware_Speedy_For_Woocommerce();
	$plugin->run();
}

sksoftware_speedy_for_woocommerce_run();
