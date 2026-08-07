<?php
/**
 * Authentication Page Template for Healthedia (Login, Register, and Forgot Password).
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect URL
$redirect_to = sanitize_url( $_GET['redirect_to'] ?? home_url( '/healthedia-dashboard/' ) );
?>
<style>
	/* Authentication Page Layout */
	.healthedia-auth-wrapper {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 60px 24px;
		min-height: calc(100vh - 160px); /* Adjust for header/footer */
		background-color: #f8fafc;
	}

	.healthedia-auth-container {
		width: 100%;
		max-width: 520px;
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 24px;
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
		padding: 40px;
		position: relative;
		overflow: hidden;
	}

	/* Switcher Tab Bar */
	.healthedia-tab-bar {
		display: flex;
		background-color: #f1f5f9;
		padding: 6px;
		border-radius: 12px;
		margin-bottom: 32px;
	}

	.healthedia-tab-btn {
		flex: 1;
		border: none;
		background: none;
		padding: 12px;
		font-family: var(--healthedia-font-sans);
		font-size: 13px;
		font-weight: 700;
		color: #64748b;
		cursor: pointer;
		border-radius: 8px;
		transition: all 0.25s ease;
		text-align: center;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		outline: none;
	}

	.healthedia-tab-btn:hover {
		color: #0f172a;
	}

	.healthedia-tab-btn.active {
		background-color: #ffffff;
		color: #000000;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
	}

	/* Card Body Forms */
	.healthedia-auth-card-body {
		display: none;
		opacity: 0;
		transform: translateY(10px);
		transition: opacity 0.3s ease, transform 0.3s ease;
	}

	.healthedia-auth-card-body.active {
		display: block;
		opacity: 1;
		transform: translateY(0);
	}

	/* Form Typography */
	.healthedia-auth-heading {
		font-family: var(--healthedia-font-display);
		font-size: 22px;
		font-weight: 800;
		color: #000000;
		text-align: center;
		margin: 0 0 8px 0;
		text-transform: uppercase;
		letter-spacing: -0.3px;
	}

	.healthedia-auth-subheading {
		font-family: var(--healthedia-font-sans);
		font-size: 13px;
		color: #64748b;
		text-align: center;
		margin: 0 0 32px 0;
		line-height: 1.5;
	}

	/* Input Fields & Groups */
	.healthedia-auth-group {
		margin-bottom: 18px;
		position: relative;
	}

	.healthedia-auth-input {
		width: 100%;
		padding: 14px 18px;
		border: 1px solid #cbd5e1;
		border-radius: 12px;
		font-size: 14px;
		font-family: var(--healthedia-font-sans);
		color: #000000;
		background-color: #ffffff;
		outline: none;
		transition: border-color 0.2s, box-shadow 0.2s;
	}

	.healthedia-auth-input::placeholder {
		color: #94a3b8;
	}

	.healthedia-auth-input:focus {
		border-color: #000000;
		box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.04);
	}

	/* Password Visibility eye icon */
	.healthedia-password-toggle {
		position: absolute;
		right: 18px;
		top: 50%;
		transform: translateY(-50%);
		background: none;
		border: none;
		color: #94a3b8;
		cursor: pointer;
		padding: 0;
		outline: none;
		display: flex;
		align-items: center;
	}

	.healthedia-password-toggle:hover {
		color: #000000;
	}

	.healthedia-password-toggle svg {
		width: 18px;
		height: 18px;
	}

	/* Links inside Auth Page */
	.healthedia-forgot-link-wrapper {
		text-align: right;
		margin-bottom: 24px;
	}

	.healthedia-forgot-link {
		font-size: 12px;
		font-weight: 500;
		color: #64748b !important;
		text-decoration: none !important;
		transition: color 0.2s;
	}

	.healthedia-forgot-link:hover {
		color: #000000 !important;
	}

	.healthedia-back-link-wrapper {
		text-align: center;
		margin-top: 24px;
	}

	.healthedia-back-to-login {
		font-size: 13px;
		font-weight: 600;
		color: #000000 !important;
		text-decoration: none !important;
		transition: opacity 0.2s;
	}

	.healthedia-back-to-login:hover {
		opacity: 0.7;
	}

	/* Large CTA Button */
	.healthedia-auth-btn {
		width: 100%;
		background-color: #000000;
		color: #ffffff;
		border: none;
		border-radius: 12px;
		padding: 16px;
		font-family: var(--healthedia-font-sans);
		font-size: 13px;
		font-weight: 700;
		cursor: pointer;
		text-transform: uppercase;
		letter-spacing: 0.8px;
		transition: all 0.2s ease;
		outline: none;
	}

	.healthedia-auth-btn:hover {
		background-color: #1e1e1e;
		transform: translateY(-1px);
	}

	.healthedia-auth-btn:active {
		transform: translateY(0);
	}

	/* Message Alert styling */
	.healthedia-msg {
		padding: 14px 18px;
		border-radius: 12px;
		font-size: 13px;
		font-weight: 500;
		margin-bottom: 24px;
		display: none;
		line-height: 1.4;
	}

	.healthedia-msg.healthedia-msg-success {
		background-color: #ecfdf5;
		color: #065f46;
		border: 1px solid #a7f3d0;
		display: block;
	}

	.healthedia-msg.healthedia-msg-error {
		background-color: #fef2f2;
		color: #991b1b;
		border: 1px solid #fca5a5;
		display: block;
	}

	/* Responsive */
	@media (max-width: 640px) {
		.healthedia-auth-wrapper {
			padding: 40px 16px;
		}
		.healthedia-auth-container {
			padding: 24px;
			border-radius: 16px;
		}
	}
</style>

<div class="healthedia-auth-wrapper">
	<div class="healthedia-auth-container">
		<!-- Switcher Tab Bar -->
		<div class="healthedia-tab-bar" id="healthedia-auth-switcher">
			<button type="button" class="healthedia-tab-btn active" data-target="healthedia-form-login">
				Login
			</button>
			<button type="button" class="healthedia-tab-btn" data-target="healthedia-form-register">
				Create Account
			</button>
		</div>

		<!-- Unified Msg Display -->
		<div class="healthedia-msg" id="healthedia-auth-msg-box"></div>

		<!-- FORM 1: LOGIN -->
		<div class="healthedia-auth-card-body active" id="healthedia-form-login">
			<h2 class="healthedia-auth-heading">Login to Archive</h2>
			<p class="healthedia-auth-subheading">Access global health, physiology, and sports biomechanics indices.</p>

			<form id="healthedia-login-inner-form" action="" method="post">
				<div class="healthedia-auth-group">
					<input type="text" name="log" class="healthedia-auth-input" placeholder="Username or Email Address" required autocomplete="username">
				</div>

				<div class="healthedia-auth-group">
					<input type="password" name="pwd" class="healthedia-auth-input" placeholder="Password" required autocomplete="current-password">
					<button type="button" class="healthedia-password-toggle">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
						</svg>
					</button>
				</div>

				<div class="healthedia-forgot-link-wrapper">
					<a href="#forgot" class="healthedia-forgot-link">Forgot Password?</a>
				</div>

				<input type="hidden" name="action" value="healthedia_ajax_login">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">
				<?php wp_nonce_field( 'healthedia_login_nonce', 'healthedia_login_security' ); ?>

				<button type="submit" class="healthedia-auth-btn">
					Sign In to Archive
				</button>
			</form>
		</div>

		<!-- FORM 2: CREATE ACCOUNT (REGISTRATION) -->
		<div class="healthedia-auth-card-body" id="healthedia-form-register">
			<h2 class="healthedia-auth-heading">Create Account</h2>
			<p class="healthedia-auth-subheading">Join the leading open-access database for global health studies.</p>

			<form id="healthedia-register-inner-form" action="" method="post">
				<div class="healthedia-auth-group">
					<input type="text" name="user_login" class="healthedia-auth-input" placeholder="Desired Username" required autocomplete="username">
				</div>

				<div class="healthedia-auth-group">
					<input type="email" name="user_email" class="healthedia-auth-input" placeholder="Institutional Email Address" required autocomplete="email">
				</div>

				<div class="healthedia-auth-group">
					<input type="password" name="user_pass" class="healthedia-auth-input" placeholder="Password (Minimum 6 characters)" required autocomplete="new-password">
					<button type="button" class="healthedia-password-toggle">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
						</svg>
					</button>
				</div>

				<div class="healthedia-auth-group">
					<input type="password" name="user_pass_confirm" class="healthedia-auth-input" placeholder="Confirm Password" required autocomplete="new-password">
				</div>

				<input type="hidden" name="action" value="healthedia_ajax_register">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">
				<?php wp_nonce_field( 'healthedia_register_nonce', 'healthedia_register_security' ); ?>

				<button type="submit" class="healthedia-auth-btn">
					Create Account
				</button>
			</form>
		</div>

		<!-- FORM 3: FORGOT PASSWORD -->
		<div class="healthedia-auth-card-body" id="healthedia-form-forgot">
			<h2 class="healthedia-auth-heading">Retrieve Password</h2>
			<p class="healthedia-auth-subheading">Enter your institutional email address to retrieve your password.</p>

			<form id="healthedia-forgot-inner-form" action="" method="post">
				<div class="healthedia-auth-group">
					<input type="email" name="user_email" class="healthedia-auth-input" placeholder="Institutional Email Address" required autocomplete="email">
				</div>

				<input type="hidden" name="action" value="healthedia_ajax_forgot">
				<?php wp_nonce_field( 'healthedia_forgot_nonce', 'healthedia_forgot_security' ); ?>

				<button type="submit" class="healthedia-auth-btn">
					Send Reset Link
				</button>

				<div class="healthedia-back-link-wrapper">
					<a href="#login" class="healthedia-back-to-login">Back to Login</a>
				</div>
			</form>
		</div>

	</div>
</div>

<!-- AJAX handling scripts -->
<script>
	jQuery(document).ready(function($) {
		const msgBox = $('#healthedia-auth-msg-box');

		function showMsg(message, isSuccess = false) {
			msgBox.removeClass('healthedia-msg-success healthedia-msg-error').hide().text('');
			if (isSuccess) {
				msgBox.addClass('healthedia-msg-success').text(message).fadeIn();
			} else {
				msgBox.addClass('healthedia-msg-error').text(message).fadeIn();
			}
		}

		// LOGIN Form Submit
		$('#healthedia-login-inner-form').on('submit', function(e) {
			e.preventDefault();
			const form = $(this);
			const btn = form.find('.healthedia-auth-btn');

			msgBox.hide();
			btn.prop('disabled', true).text('Signing In...');

			$.ajax({
				url: healthedia_vars.ajax_url,
				type: 'POST',
				data: form.serialize(),
				success: function(response) {
					if (response.success) {
						showMsg(response.data.message, true);
						setTimeout(function() {
							window.location.href = response.data.redirect_url;
						}, 1000);
					} else {
						btn.prop('disabled', false).text('Sign In to Archive');
						showMsg(response.data.message, false);
					}
				},
				error: function() {
					btn.prop('disabled', false).text('Sign In to Archive');
					showMsg('A connection error occurred. Please try again.');
				}
			});
		});

		// REGISTER Form Submit
		$('#healthedia-register-inner-form').on('submit', function(e) {
			e.preventDefault();
			const form = $(this);
			const btn = form.find('.healthedia-auth-btn');

			msgBox.hide();

			// Validate passwords match
			const pwd = form.find('input[name="user_pass"]').val();
			const confirmPwd = form.find('input[name="user_pass_confirm"]').val();
			if (pwd !== confirmPwd) {
				showMsg('Passwords do not match.');
				return;
			}

			if (pwd.length < 6) {
				showMsg('Password must be at least 6 characters long.');
				return;
			}

			btn.prop('disabled', true).text('Creating Account...');

			$.ajax({
				url: healthedia_vars.ajax_url,
				type: 'POST',
				data: form.serialize(),
				success: function(response) {
					if (response.success) {
						showMsg(response.data.message, true);
						setTimeout(function() {
							window.location.href = response.data.redirect_url;
						}, 1000);
					} else {
						btn.prop('disabled', false).text('Create Account');
						showMsg(response.data.message, false);
					}
				},
				error: function() {
					btn.prop('disabled', false).text('Create Account');
					showMsg('A connection error occurred. Please try again.');
				}
			});
		});

		// FORGOT Form Submit
		$('#healthedia-forgot-inner-form').on('submit', function(e) {
			e.preventDefault();
			const form = $(this);
			const btn = form.find('.healthedia-auth-btn');

			msgBox.hide();
			btn.prop('disabled', true).text('Sending Email...');

			$.ajax({
				url: healthedia_vars.ajax_url,
				type: 'POST',
				data: form.serialize(),
				success: function(response) {
					btn.prop('disabled', false).text('Send Reset Link');
					if (response.success) {
						showMsg(response.data.message, true);
						form.find('input[name="user_email"]').val('');
					} else {
						showMsg(response.data.message, false);
					}
				},
				error: function() {
					btn.prop('disabled', false).text('Send Reset Link');
					showMsg('A connection error occurred. Please try again.');
				}
			});
		});
	});
</script>
