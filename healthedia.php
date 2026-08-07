<?php
/**
 * Plugin Name: Healthedia
 * Plugin URI: https://healthedia.com
 * Description: A unified Header and Footer plugin for Healthedia with built-in Archive Search, Authentication, and SaaS-style Dashboard.
 * Version: 1.0.0
 * Author: Jules
 * Author URI: https://healthedia.com
 * License: GPL2 or later
 * Text Domain: healthedia
 * Requires PHP: 8.3
 * Requires at least: 6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'HEALTHEDIA_VERSION', '1.0.0' );
define( 'HEALTHEDIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'HEALTHEDIA_URL', plugin_dir_url( __FILE__ ) );

// Include activator file
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-activator.php';

// Include router file
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-router.php';

// Include header/footer manager file
require_once HEALTHEDIA_PATH . 'includes/class-healthedia-header-footer.php';

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'healthedia_activate_plugin' );
register_deactivation_hook( __FILE__, 'healthedia_deactivate_plugin' );
