<?php
/**
 * Backend handlers for AJAX user login, registration, and password retrieval.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Healthedia_Auth_Handler {

	/**
	 * Register actions for AJAX authentication endpoints.
	 */
	public function init() {
		// Login hooks
		add_action( 'wp_ajax_healthedia_ajax_login', array( $this, 'ajax_login' ) );
		add_action( 'wp_ajax_nopriv_healthedia_ajax_login', array( $this, 'ajax_login' ) );

		// Register hooks
		add_action( 'wp_ajax_healthedia_ajax_register', array( $this, 'ajax_register' ) );
		add_action( 'wp_ajax_nopriv_healthedia_ajax_register', array( $this, 'ajax_register' ) );

		// Forgot password hooks
		add_action( 'wp_ajax_healthedia_ajax_forgot', array( $this, 'ajax_forgot' ) );
		add_action( 'wp_ajax_nopriv_healthedia_ajax_forgot', array( $this, 'ajax_forgot' ) );
	}

	/**
	 * Secure AJAX Login handler.
	 */
	public function ajax_login() {
		// Nonce check
		if ( ! isset( $_POST['healthedia_login_security'] ) || ! wp_verify_nonce( $_POST['healthedia_login_security'], 'healthedia_login_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
		}

		$username = sanitize_text_field( $_POST['log'] ?? '' );
		$password = $_POST['pwd'] ?? '';
		$redirect_to = sanitize_url( $_POST['redirect_to'] ?? home_url( '/healthedia-dashboard/' ) );

		if ( empty( $username ) || empty( $password ) ) {
			wp_send_json_error( array( 'message' => 'Username and Password fields are required.' ) );
		}

		// Try logging in
		$info = array();
		$info['user_login']    = $username;
		$info['user_password'] = $password;
		$info['remember']      = true;

		$user_signon = wp_signon( $info, false );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json_error( array( 'message' => 'Invalid email address, username, or password.' ) );
		}

		wp_send_json_success( array(
			'message'      => 'Login successful! Redirecting...',
			'redirect_url' => $redirect_to,
		) );
	}

	/**
	 * Secure AJAX Registration handler.
	 */
	public function ajax_register() {
		// Nonce check
		if ( ! isset( $_POST['healthedia_register_security'] ) || ! wp_verify_nonce( $_POST['healthedia_register_security'], 'healthedia_register_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
		}

		$username = sanitize_user( $_POST['user_login'] ?? '' );
		$email    = sanitize_email( $_POST['user_email'] ?? '' );
		$password = $_POST['user_pass'] ?? '';
		$redirect_to = sanitize_url( $_POST['redirect_to'] ?? home_url( '/healthedia-dashboard/' ) );

		if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
			wp_send_json_error( array( 'message' => 'All fields are required.' ) );
		}

		if ( strlen( $password ) < 6 ) {
			wp_send_json_error( array( 'message' => 'Password must be at least 6 characters long.' ) );
		}

		// Validation: Username check
		if ( username_exists( $username ) ) {
			wp_send_json_error( array( 'message' => 'This username is already registered.' ) );
		}

		// Validation: Email check
		if ( email_exists( $email ) ) {
			wp_send_json_error( array( 'message' => 'This email address is already registered.' ) );
		}

		// Create user
		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
		}

		// Automatically log the user in
		$info = array();
		$info['user_login']    = $username;
		$info['user_password'] = $password;
		$info['remember']      = true;

		$user_signon = wp_signon( $info, false );

		wp_send_json_success( array(
			'message'      => 'Account created and signed in successfully! Redirecting...',
			'redirect_url' => $redirect_to,
		) );
	}

	/**
	 * Secure AJAX Forgot Password recovery handler.
	 */
	public function ajax_forgot() {
		// Nonce check
		if ( ! isset( $_POST['healthedia_forgot_security'] ) || ! wp_verify_nonce( $_POST['healthedia_forgot_security'], 'healthedia_forgot_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
		}

		$email = sanitize_email( $_POST['user_email'] ?? '' );

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => 'Please enter your institutional email address.' ) );
		}

		// Check if user exists
		$user = get_user_by( 'email', $email );

		// Security best practice: Always return success message even if email doesn't exist
		// to prevent username harvesting, but on mock/development we handle it dynamically
		if ( ! $user ) {
			wp_send_json_success( array(
				'message' => 'If this email is registered in our archive, a password recovery link has been dispatched.',
			) );
		}

		// Emulate / Run password recovery
		if ( function_exists( 'retrieve_password' ) ) {
			retrieve_password( $user->user_login );
		}

		wp_send_json_success( array(
			'message' => 'A password recovery link has been successfully sent to ' . esc_html( $email ) . '.',
		) );
	}
}
