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

/**
 * Disable WordPress admin bar for any regular or non-administrator users.
 */
function healthedia_disable_admin_bar( $show ) {
    if ( ! current_user_can( 'administrator' ) ) {
        return false;
    }
    return $show;
}
add_filter( 'show_admin_bar', 'healthedia_disable_admin_bar' );

/**
 * Restrict WordPress administrative dashboard access to administrators only.
 */
function healthedia_restrict_admin_access() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    if ( ! current_user_can( 'administrator' ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }
}
add_action( 'admin_init', 'healthedia_restrict_admin_access' );
