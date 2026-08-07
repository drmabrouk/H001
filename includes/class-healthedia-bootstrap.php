<?php
/**
 * Main Bootstrap class for the Healthedia plugin.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Healthedia_Bootstrap {

	/**
	 * Initialize the plugin.
	 */
	public function run() {
		// Enqueue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Inject Header and Footer globally
		add_action( 'wp_body_open', array( $this, 'inject_header' ) );
		add_action( 'wp_footer', array( $this, 'inject_footer' ) );

		// Initialize components
		$router = new Healthedia_Router();
		$router->init();

		$auth_handler = new Healthedia_Auth_Handler();
		$auth_handler->init();

		$profile_handler = new Healthedia_Profile_Handler();
		$profile_handler->init();
	}

	/**
	 * Register and enqueue frontend scripts and styles.
	 */
	public function enqueue_assets() {
		// Custom styles for Header and Footer
		wp_enqueue_style(
			'healthedia-public',
			HEALTHEDIA_URL . 'assets/css/public.css',
			array(),
			HEALTHEDIA_VERSION,
			'all'
		);

		// Custom script for responsiveness and form transitions
		wp_enqueue_script(
			'healthedia-public',
			HEALTHEDIA_URL . 'assets/js/public.js',
			array( 'jquery' ),
			HEALTHEDIA_VERSION,
			true
		);

		// Localize script to pass configuration or AJAX URLs
		wp_localize_script(
			'healthedia-public',
			'healthedia_vars',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'security'    => wp_create_nonce( 'healthedia_nonce' ),
				'dashboard'   => home_url( '/healthedia-dashboard/' ),
				'auth'        => home_url( '/healthedia-auth/' ),
				'is_logged'   => is_user_logged_in() ? '1' : '0',
			)
		);
	}

	/**
	 * Inject Healthedia global Header right at the start of the body.
	 */
	public function inject_header() {
		// Only render on non-admin pages
		if ( ! is_admin() ) {
			include HEALTHEDIA_PATH . 'templates/layout-header.php';
		}
	}

	/**
	 * Inject Healthedia global Footer right before closing body.
	 */
	public function inject_footer() {
		// Only render on non-admin pages
		if ( ! is_admin() ) {
			include HEALTHEDIA_PATH . 'templates/layout-footer.php';
		}
	}
}
