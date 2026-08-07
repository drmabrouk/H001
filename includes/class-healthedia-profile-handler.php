<?php
/**
 * Handles AJAX updates for User Profiles and Plugin Settings.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Healthedia_Profile_Handler {

	/**
	 * Register AJAX hooks.
	 */
	public function init() {
		add_action( 'wp_ajax_healthedia_update_profile', array( $this, 'update_profile' ) );
		add_action( 'wp_ajax_nopriv_healthedia_update_profile', array( $this, 'send_logged_out_error' ) );

		add_action( 'wp_ajax_healthedia_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_nopriv_healthedia_save_settings', array( $this, 'send_logged_out_error' ) );
	}

	/**
	 * Error handler for unauthenticated AJAX requests.
	 */
	public function send_logged_out_error() {
		wp_send_json_error( array( 'message' => 'You must be logged in to perform this action.' ) );
	}

	/**
	 * Securely update the current user's profile.
	 */
	public function update_profile() {
		// Nonce check
		if ( ! isset( $_POST['healthedia_profile_security'] ) || ! wp_verify_nonce( $_POST['healthedia_profile_security'], 'healthedia_profile_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'You must be logged in to update your profile.' ) );
		}

		$user_id = get_current_user_id();
		$first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
		$last_name  = sanitize_text_field( $_POST['last_name'] ?? '' );
		$email      = sanitize_email( $_POST['user_email'] ?? '' );
		$password   = $_POST['user_pass'] ?? '';

		// Validation
		if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) ) {
			wp_send_json_error( array( 'message' => 'First Name, Last Name, and Email are required fields.' ) );
		}

		// Email check
		$existing_user_by_email = get_user_by( 'email', $email );
		if ( $existing_user_by_email && $existing_user_by_email->ID !== $user_id ) {
			wp_send_json_error( array( 'message' => 'This email address is already in use by another user.' ) );
		}

		// Build update array
		$userdata = array(
			'ID'         => $user_id,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_email' => $email,
		);

		if ( ! empty( $password ) ) {
			if ( strlen( $password ) < 6 ) {
				wp_send_json_error( array( 'message' => 'Password must be at least 6 characters long.' ) );
			}
			$userdata['user_pass'] = $password;
		}

		$updated_user_id = wp_update_user( $userdata );

		if ( is_wp_error( $updated_user_id ) ) {
			wp_send_json_error( array( 'message' => $updated_user_id->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Your profile has been updated successfully.' ) );
	}

	/**
	 * Securely save the plugin settings. Only admins allowed.
	 */
	public function save_settings() {
		// Nonce check
		if ( ! isset( $_POST['healthedia_settings_security'] ) || ! wp_verify_nonce( $_POST['healthedia_settings_security'], 'healthedia_settings_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security verification failed. Please try again.' ) );
		}

		// Permission check
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to modify plugin settings.' ) );
		}

		$logo_text      = sanitize_text_field( $_POST['healthedia_logo_text'] ?? '' );
		$sub_text       = sanitize_text_field( $_POST['healthedia_sub_text'] ?? '' );
		$copyright_text = sanitize_text_field( $_POST['healthedia_copyright_text'] ?? '' );
		$clinic_status  = isset( $_POST['healthedia_clinic_status'] ) ? 'open' : 'closed';

		// Update options
		update_option( 'healthedia_logo_text', $logo_text );
		update_option( 'healthedia_sub_text', $sub_text );
		update_option( 'healthedia_copyright_text', $copyright_text );
		update_option( 'healthedia_clinic_status', $clinic_status );

		wp_send_json_success( array( 'message' => 'Global plugin settings saved successfully.' ) );
	}
}
