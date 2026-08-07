<?php
/**
 * Plugin Name:       Healthedia
 * Plugin URI:        https://healthedia.com
 * Description:       Automatically applies a unified Header and Footer across the entire website immediately after installation, with an independent professional SaaS Dashboard and integrated Authentication page. Fully compatible with WordPress 7.0.3, PHP 8.3, and Astra Theme 4.13.8.
 * Version:           1.0.0
 * Author:            Jules
 * Author URI:        https://healthedia.com
 * License:           GPL-2.0+
 * Text Domain:       healthedia
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants
if ( ! defined( 'HEALTHEDIA_VERSION' ) ) {
	define( 'HEALTHEDIA_VERSION', '1.0.0' );
}
define( 'HEALTHEDIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'HEALTHEDIA_URL', plugin_dir_url( __FILE__ ) );
define( 'HEALTHEDIA_BASENAME', plugin_basename( __FILE__ ) );

// Require Core Files
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-bootstrap.php';
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-router.php';
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-auth-handler.php';
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-profile-handler.php';

/**
 * Run Healthedia on plugin activation.
 */
function activate_healthedia() {
	// Flush rewrite rules on activation to ensure virtual pages work
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'activate_healthedia' );

/**
 * Run Healthedia on plugin deactivation.
 */
function deactivate_healthedia() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_healthedia' );

/**
 * Initialize the plugin bootstrap
 */
function run_healthedia() {
	$plugin = new Healthedia_Bootstrap();
	$plugin->run();
}
add_action( 'plugins_loaded', 'run_healthedia' );
