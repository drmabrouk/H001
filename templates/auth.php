<?php
/**
 * Template Name: LOGIN TO ARCHIVE
 */

// If a user opens the Login or Registration page while already logged in, automatically redirect to homepage immediately
if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

$error_message = '';
$success_message = '';

// Load Authentication settings from WP Options
$auth_options = get_option( 'healthedia_auth_options' );
if ( ! is_array( $auth_options ) ) {
    $auth_options = [
        'login' => 'enabled',
        'registration' => 'enabled',
    ];
}

$login_enabled = isset( $auth_options['login'] ) && $auth_options['login'] === 'enabled';
$registration_enabled = isset( $auth_options['registration'] ) && $auth_options['registration'] === 'enabled';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( isset( $_POST['healthedia_action'] ) ) {
        // Security Nonce Verification
        if ( ! isset( $_POST['healthedia_auth_nonce'] ) || ! wp_verify_nonce( $_POST['healthedia_auth_nonce'], 'healthedia_auth_action' ) ) {
            $error_message = 'Security verification failed. Please refresh the page and try again.';
        } elseif ( $_POST['healthedia_action'] === 'login' ) {
            if ( ! $login_enabled ) {
                $error_message = 'Account sign-in services are currently disabled by the administrator.';
            } else {
                $email = sanitize_email( $_POST['email'] );
                $password = sanitize_text_field( $_POST['password'] );

                if ( empty( $email ) || empty( $password ) ) {
                    $error_message = 'Please enter both your email address and password.';
                } else {
                    $creds = [
                        'user_login'    => $email,
                        'user_password' => $password,
                        'remember'      => true,
                    ];
                    $user = wp_signon( $creds, is_ssl() );
                    if ( is_wp_error( $user ) ) {
                        $error_message = $user->get_error_message();
                    } else {
                        // After successful login, redirect user immediately to the homepage!
                        wp_safe_redirect( home_url( '/' ) );
                        exit;
                    }
                }
            }
        } elseif ( $_POST['healthedia_action'] === 'register' ) {
            if ( ! $registration_enabled ) {
                $error_message = 'New researcher registrations are currently disabled by the administrator.';
            } else {
                $email = sanitize_email( $_POST['email'] );
                $password = sanitize_text_field( $_POST['password'] );
                $confirm_password = sanitize_text_field( $_POST['confirm_password'] );
                $first_name = sanitize_text_field( $_POST['first_name'] );
                $last_name = sanitize_text_field( $_POST['last_name'] );
                $otp = sanitize_text_field( $_POST['otp'] );

                if ( empty( $email ) || empty( $password ) || empty( $confirm_password ) || empty( $first_name ) || empty( $last_name ) ) {
                    $error_message = 'All profile fields are required for registration.';
                } elseif ( $password !== $confirm_password ) {
                    $error_message = 'Passwords do not match. Please verify your passwords match.';
                } elseif ( email_exists( $email ) ) {
                    $error_message = 'This email address is already registered.';
                } elseif ( empty( $otp ) || $otp !== '849204' ) {
                    $error_message = 'Invalid verification OTP code. Please enter the correct 6-digit code (849204) sent to your email.';
                } else {
                    // Generate unique username from email
                    $username = strstr($email, '@', true);
                    if ( ! $username || username_exists( $username ) ) {
                        $username = $username . '_' . rand(100, 999);
                    }
                    $user_id = wp_create_user( $username, $password, $email );
                    if ( is_wp_error( $user_id ) ) {
                        $error_message = $user_id->get_error_message();
                    } else {
                        $display_name = $first_name . ' ' . $last_name;
                        wp_update_user([
                            'ID' => $user_id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'display_name' => $display_name,
                        ]);
                        // Auto login after registration
                        $creds = [
                            'user_login'    => $email,
                            'user_password' => $password,
                            'remember'      => true,
                        ];
                        wp_signon( $creds, is_ssl() );
                        // After Registration, automatically redirect to the homepage!
                        wp_safe_redirect( home_url( '/' ) );
                        exit;
                    }
                }
            }
        } elseif ( $_POST['healthedia_action'] === 'forgot' ) {
            if ( ! $login_enabled ) {
                $error_message = 'Password recovery services are currently disabled by the administrator.';
            } else {
                $email = sanitize_email( $_POST['email'] );
                if ( empty( $email ) ) {
                    $error_message = 'Please enter your institutional email address.';
                } else {
                    $user_data = get_user_by( 'email', $email );
                    if ( ! $user_data ) {
                        $error_message = 'No account was found with that email address.';
                    } else {
                        $success_message = 'A password reset link has been successfully simulated and sent to your email!';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN TO ARCHIVE</title>
    <?php wp_head(); ?>
    <style>
        .healthedia-auth-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 120px); /* Fill entire viewport cleanly */
            padding: 20px;
            box-sizing: border-box;
            background-color: #ffffff;
        }

        .healthedia-auth-card {
            background: #ffffff;
            border: 1px solid #e8e8e8;
            border-radius: 24px;
            width: 100%;
            max-width: 520px;
            padding: 30px; /* Reduced to fit viewport without scroll */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.02);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }

        .healthedia-auth-tabs {
            display: flex;
            background-color: #f7f7f7;
            padding: 6px;
            border-radius: 100px;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 25px; /* Compact margin */
        }

        .healthedia-auth-tab {
            flex: 1;
            border: none;
            outline: none;
            background: none;
            padding: 10px 0;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 100px;
            cursor: pointer;
            color: #888888;
            transition: all 0.2s ease;
            text-align: center;
        }

        .healthedia-auth-tab.active {
            background-color: #ffffff;
            color: #000000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .healthedia-auth-form-view {
            width: 100%;
            display: none;
            flex-direction: column;
            align-items: center;
        }

        .healthedia-auth-form-view.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .healthedia-auth-form-title {
            font-size: 22px; /* Sleeker size */
            font-weight: 800;
            color: #000000;
            margin: 0 0 6px 0;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }

        .healthedia-auth-form-subtitle {
            font-size: 13px;
            color: #888888;
            text-align: center;
            margin: 0 0 25px 0;
            line-height: 1.5;
            font-weight: 500;
        }

        .healthedia-input-group {
            width: 100%;
            position: relative;
            margin-bottom: 16px; /* Compacted spacing to ensure absolute 100vh containment */
            text-align: left;
        }

        .healthedia-input-row {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-bottom: 16px;
        }

        .healthedia-input-row-item {
            flex: 1;
            position: relative;
        }

        .healthedia-auth-input {
            width: 100%;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 12px;
            padding: 16px 20px;
            box-sizing: border-box;
            font-size: 15px;
            font-family: inherit;
            color: #333333;
            outline: none;
            height: 52px; /* Fixed robust input height */
            transition: border-color 0.2s ease;
        }

        .healthedia-auth-input:focus {
            border-color: #000000;
        }

        /* Highly Polished Select Dropdown */
        .healthedia-auth-select {
            width: 100%;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 12px;
            padding: 0 20px; /* Reduced vertical padding */
            box-sizing: border-box;
            font-size: 15px;
            font-family: inherit;
            color: #333333;
            outline: none;
            height: 52px; /* Set exact, fixed height matching text inputs */
            line-height: 52px; /* Prevent vertical displacement */
            display: flex;
            align-items: center;
            transition: border-color 0.2s ease;
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23999%22%20stroke-width%3D%222%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E');
            background-repeat: no-repeat;
            background-position: right 20px center;
        }

        .healthedia-auth-select:focus {
            border-color: #000000;
        }

        .healthedia-password-toggle {
            position: absolute;
            right: 20px;
            top: 50%; /* Perfect center vertical alignment */
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999999;
            display: flex;
            align-items: center;
            padding: 0;
            z-index: 10;
        }

        .healthedia-password-toggle:hover {
            color: #000000;
        }

        .healthedia-forgot-link-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            margin-top: -10px;
            margin-bottom: 25px;
        }

        .healthedia-forgot-link {
            font-size: 12px;
            color: #888888;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .healthedia-forgot-link:hover {
            color: #000000;
        }

        .healthedia-auth-submit {
            width: 100%;
            background-color: #000000;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 16px 0;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
            text-align: center;
        }

        .healthedia-auth-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Register Wizard step buttons wrapper */
        .healthedia-wizard-actions {
            display: flex;
            gap: 12px;
            width: 100%;
            margin-top: 10px;
        }

        .healthedia-auth-btn-secondary {
            flex: 1;
            background-color: #f4f4f4;
            color: #333333;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 16px 0;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-align: center;
        }

        .healthedia-auth-btn-secondary:hover {
            background-color: #e5e5e5;
        }

        /* Registration multi-step wizard step navigation indicators */
        .healthedia-wizard-steps-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
            width: 100%;
        }

        .healthedia-wizard-indicator-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #f0f0f0;
            color: #999999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .healthedia-wizard-indicator-dot.active {
            background-color: #000000;
            color: #ffffff;
        }

        .healthedia-wizard-indicator-dot.completed {
            background-color: #e6f6ec;
            color: #1b8a4f;
        }

        .healthedia-wizard-indicator-line {
            height: 2px;
            width: 25px;
            background-color: #e5e5e5;
        }

        .healthedia-wizard-indicator-line.active {
            background-color: #000000;
        }

        .healthedia-auth-feedback {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .healthedia-auth-feedback.success {
            background-color: #e6f6ec;
            color: #1b8a4f;
            border: 1px solid #1b8a4f;
        }

        .healthedia-auth-feedback.error {
            background-color: #fbeae9;
            color: #bf271b;
            border: 1px solid #bf271b;
        }

        /* Fullscreen Schedule Maintenance Notice */
        .healthedia-maintenance-wrapper {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            border: 1px solid #e5e5e5;
            border-radius: 24px;
            box-shadow: 0 10px 45px rgba(0,0,0,0.01);
            font-family: inherit;
        }
        .healthedia-maintenance-title {
            font-size: 28px;
            font-weight: 800;
            color: #000000;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .healthedia-maintenance-desc {
            font-size: 15px;
            color: #666666;
            line-height: 1.6;
        }

        @media (max-width: 480px) {
            .healthedia-auth-card {
                padding: 24px;
                border-radius: 16px;
            }
            .healthedia-auth-form-title {
                font-size: 20px;
            }
            .healthedia-input-row {
                flex-direction: column;
                gap: 16px;
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="healthedia-auth-wrapper">

        <?php if ( ! $login_enabled && ! $registration_enabled ) : ?>
            <!-- Admin Disabled BOTH options -> Scheduled maintenance message -->
            <div class="healthedia-maintenance-wrapper">
                <div class="healthedia-maintenance-title">Service Maintenance</div>
                <p class="healthedia-maintenance-desc">
                    Authentication services are currently undergoing scheduled institutional maintenance. Please contact your administrator or try again later.
                </p>
            </div>
        <?php else : ?>
            <div class="healthedia-auth-card">

                <!-- Tabs Header -->
                <div class="healthedia-auth-tabs" id="auth-tabs-bar" style="<?php echo ( ! $login_enabled || ! $registration_enabled ) ? 'display: none !important;' : ''; ?>">
                    <button class="healthedia-auth-tab <?php echo $login_enabled ? 'active' : ''; ?>" data-tab="login" id="tab-login-btn">Login</button>
                    <button class="healthedia-auth-tab <?php echo ! $login_enabled ? 'active' : ''; ?>" data-tab="register" id="tab-register-btn">Create Account</button>
                </div>

                <!-- Feedback Area (displays actual backend validation states) -->
                <?php if ( ! empty( $error_message ) ) : ?>
                    <div class="healthedia-auth-feedback error" id="auth-feedback"><?php echo $error_message; ?></div>
                <?php elseif ( ! empty( $success_message ) ) : ?>
                    <div class="healthedia-auth-feedback success" id="auth-feedback"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <!-- LOGIN VIEW -->
                <?php if ( $login_enabled ) : ?>
                    <div class="healthedia-auth-form-view <?php echo $login_enabled ? 'active' : ''; ?>" id="view-login">
                        <h2 class="healthedia-auth-form-title">LOGIN TO ARCHIVE</h2>
                        <p class="healthedia-auth-form-subtitle">Access global health, physiology, and sports biomechanics indices.</p>

                        <form id="form-login" method="POST" style="width: 100%;">
                            <?php wp_nonce_field( 'healthedia_auth_action', 'healthedia_auth_nonce' ); ?>
                            <input type="hidden" name="healthedia_action" value="login">

                            <div class="healthedia-input-group">
                                <input type="email" name="email" class="healthedia-auth-input" id="login-email" placeholder="Institutional Email Address" required value="<?php echo isset($_POST['email']) && isset($_POST['healthedia_action']) && $_POST['healthedia_action'] === 'login' ? esc_attr($_POST['email']) : ''; ?>">
                            </div>

                            <div class="healthedia-input-group">
                                <input type="password" name="password" class="healthedia-auth-input" id="login-password" placeholder="Password" required>
                                <button type="button" class="healthedia-password-toggle" onclick="togglePassword('login-password')">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>

                            <div class="healthedia-forgot-link-wrapper">
                                <a href="#" class="healthedia-forgot-link" id="goto-forgot">Forgot Password?</a>
                            </div>
                            <button type="submit" class="healthedia-auth-submit">SIGN IN TO ARCHIVE</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- REGISTRATION VIEW (4-Step Multi-Step Flow) -->
                <?php if ( $registration_enabled ) : ?>
                    <div class="healthedia-auth-form-view <?php echo ! $login_enabled ? 'active' : ''; ?>" id="view-register">
                        <h2 class="healthedia-auth-form-title">CREATE AN ACCOUNT</h2>
                        <p class="healthedia-auth-form-subtitle">Join the leading repository for elite sports and clinical health research.</p>

                        <!-- Step navigation dots -->
                        <div class="healthedia-wizard-steps-indicator">
                            <div class="healthedia-wizard-indicator-dot active" id="dot-step-1">1</div>
                            <div class="healthedia-wizard-indicator-line" id="line-step-1"></div>
                            <div class="healthedia-wizard-indicator-dot" id="dot-step-2">2</div>
                            <div class="healthedia-wizard-indicator-line" id="line-step-2"></div>
                            <div class="healthedia-wizard-indicator-dot" id="dot-step-3">3</div>
                            <div class="healthedia-wizard-indicator-line" id="line-step-3"></div>
                            <div class="healthedia-wizard-indicator-dot" id="dot-step-4">4</div>
                        </div>

                        <form id="form-register" method="POST" style="width: 100%;" enctype="multipart/form-data">
                            <?php wp_nonce_field( 'healthedia_auth_action', 'healthedia_auth_nonce' ); ?>
                            <input type="hidden" name="healthedia_action" value="register">

                            <!-- REGISTRATION STEP 1: Institutional Credentials -->
                            <div class="healthedia-wizard-fieldset" id="fieldset-step-1">
                                <div class="healthedia-input-group">
                                    <input type="email" name="email" class="healthedia-auth-input" id="register-email" placeholder="Institutional Email Address" required value="<?php echo isset($_POST['email']) && isset($_POST['healthedia_action']) && $_POST['healthedia_action'] === 'register' ? esc_attr($_POST['email']) : ''; ?>">
                                </div>
                                <div class="healthedia-input-group">
                                    <select class="healthedia-auth-select" id="register-institution" required>
                                        <option value="" disabled selected>Select Affiliated Institution</option>
                                        <option value="Cambridge University">Cambridge University</option>
                                        <option value="Harvard Medical School">Harvard Medical School</option>
                                        <option value="MIT Kinesiology Lab">MIT Kinesiology Lab</option>
                                        <option value="Oxford Health Sciences">Oxford Health Sciences</option>
                                        <option value="Mayo Clinic Research">Mayo Clinic Research</option>
                                    </select>
                                </div>
                                <button type="button" class="healthedia-auth-submit" onclick="nextStep(2)">CONTINUE TO PHOTO</button>
                            </div>

                            <!-- REGISTRATION STEP 2: Profile Picture Guidance Step -->
                            <div class="healthedia-wizard-fieldset" id="fieldset-step-2" style="display: none;">
                                <div class="healthedia-input-group">
                                    <input type="file" name="profile_pic" class="healthedia-auth-input" id="register-profile-pic" accept="image/*" style="padding-top: 14px;">
                                    <p class="healthedia-guidance-note" style="font-size: 11px; color: #666666; margin-top: 8px; line-height: 1.4; font-style: italic; font-weight: 500;">
                                        Guidance Note: We recommend uploading a professional photo with a white background for official institutional indexing.
                                    </p>
                                </div>
                                <div class="healthedia-wizard-actions">
                                    <button type="button" class="healthedia-auth-btn-secondary" onclick="prevStep(1)">BACK</button>
                                    <button type="button" class="healthedia-auth-submit" onclick="nextStep(3)">CONTINUE TO PROFILE</button>
                                </div>
                            </div>

                            <!-- REGISTRATION STEP 3: Professional Credentials (First & Last Name on same row) -->
                            <div class="healthedia-wizard-fieldset" id="fieldset-step-3" style="display: none;">
                                <div class="healthedia-input-row">
                                    <div class="healthedia-input-row-item">
                                        <input type="text" name="first_name" class="healthedia-auth-input" id="register-first-name" placeholder="First Name" required value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''; ?>">
                                    </div>
                                    <div class="healthedia-input-row-item">
                                        <input type="text" name="last_name" class="healthedia-auth-input" id="register-last-name" placeholder="Last Name" required value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="healthedia-input-group">
                                    <select class="healthedia-auth-select" id="register-specialty" required>
                                        <option value="" disabled selected>Select Scientific Specialty</option>
                                        <option value="Sports Physiology">Sports Physiology & Hydration</option>
                                        <option value="Clinical Biomechanics">Clinical Biomechanics & Kinetics</option>
                                        <option value="Neurological Rehabilitation">Neurological Rehabilitation</option>
                                        <option value="Cardiovascular Science">Cardiovascular Performance</option>
                                    </select>
                                </div>
                                <div class="healthedia-wizard-actions">
                                    <button type="button" class="healthedia-auth-btn-secondary" onclick="prevStep(2)">BACK</button>
                                    <button type="button" class="healthedia-auth-submit" onclick="nextStep(4)">CONTINUE TO SECURITY</button>
                                </div>
                            </div>

                            <!-- REGISTRATION STEP 4: Email Verification (OTP & Password matching on same row) -->
                            <div class="healthedia-wizard-fieldset" id="fieldset-step-4" style="display: none;">
                                <div class="healthedia-input-row">
                                    <div class="healthedia-input-row-item">
                                        <input type="password" name="password" class="healthedia-auth-input" id="register-password" placeholder="Create Password" required>
                                        <button type="button" class="healthedia-password-toggle" onclick="togglePassword('register-password')">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </button>
                                    </div>
                                    <div class="healthedia-input-row-item">
                                        <input type="password" name="confirm_password" class="healthedia-auth-input" id="register-confirm-password" placeholder="Confirm Password" required>
                                        <button type="button" class="healthedia-password-toggle" onclick="togglePassword('register-confirm-password')">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="healthedia-input-group" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="text" name="otp" class="healthedia-auth-input" id="register-otp" placeholder="Secure Verification Code (6-digit OTP)" required style="flex: 2;">
                                    <button type="button" class="healthedia-auth-btn-secondary" onclick="sendOtpCode()" style="flex: 1; padding: 16px 0; margin-top: 0; font-size: 11px;">SEND OTP</button>
                                </div>
                                <div class="healthedia-wizard-actions">
                                    <button type="button" class="healthedia-auth-btn-secondary" onclick="prevStep(3)">BACK</button>
                                    <button type="submit" class="healthedia-auth-submit">COMPLETE REGISTRATION</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- FORGOT PASSWORD VIEW -->
                <?php if ( $login_enabled ) : ?>
                    <div class="healthedia-auth-form-view" id="view-forgot">
                        <h2 class="healthedia-auth-form-title">FORGOT PASSWORD</h2>
                        <p class="healthedia-auth-form-subtitle">Enter your institutional email to request a secure password reset link.</p>

                        <form id="form-forgot" method="POST" style="width: 100%;">
                            <?php wp_nonce_field( 'healthedia_auth_action', 'healthedia_auth_nonce' ); ?>
                            <input type="hidden" name="healthedia_action" value="forgot">
                            <div class="healthedia-input-group">
                                <input type="email" name="email" class="healthedia-auth-input" id="forgot-email" placeholder="Institutional Email Address" required value="<?php echo isset($_POST['email']) && isset($_POST['healthedia_action']) && $_POST['healthedia_action'] === 'forgot' ? esc_attr($_POST['email']) : ''; ?>">
                            </div>
                            <div class="healthedia-forgot-link-wrapper">
                                <a href="#" class="healthedia-forgot-link" id="back-to-login">Back to Login</a>
                            </div>
                            <button type="submit" class="healthedia-auth-submit">SEND RESET INSTRUCTIONS</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            if (field.type === "password") {
                field.type = "text";
            } else {
                field.type = "password";
            }
        }

        function sendOtpCode() {
            const emailField = document.getElementById('register-email');
            if (!emailField || !emailField.value || !emailField.checkValidity()) {
                alert('Please enter a valid institutional email address in Step 1 first!');
                return;
            }
            alert('A professional 6-digit OTP code (849204) has been successfully dispatched to your email address: ' + emailField.value);
            // Auto fill for absolute frictionless ease of simulated verification
            const otpField = document.getElementById('register-otp');
            if (otpField) {
                otpField.value = '849204';
            }
        }

        // Multi-Step Wizard Handlers
        function nextStep(stepNum) {
            if (stepNum === 2) {
                // Validate Email in Step 1
                const emailField = document.getElementById('register-email');
                const instSelect = document.getElementById('register-institution');
                if (!emailField.checkValidity() || !emailField.value) {
                    alert('Please enter a valid institutional email address.');
                    return;
                }
                if (!instSelect.value) {
                    alert('Please select your affiliated academic institution.');
                    return;
                }

                document.getElementById('fieldset-step-1').style.display = 'none';
                document.getElementById('fieldset-step-2').style.display = 'block';

                document.getElementById('dot-step-1').className = 'healthedia-wizard-indicator-dot completed';
                document.getElementById('line-step-1').className = 'healthedia-wizard-indicator-line active';
                document.getElementById('dot-step-2').className = 'healthedia-wizard-indicator-dot active';
            } else if (stepNum === 3) {
                document.getElementById('fieldset-step-2').style.display = 'none';
                document.getElementById('fieldset-step-3').style.display = 'block';

                document.getElementById('dot-step-2').className = 'healthedia-wizard-indicator-dot completed';
                document.getElementById('line-step-2').className = 'healthedia-wizard-indicator-line active';
                document.getElementById('dot-step-3').className = 'healthedia-wizard-indicator-dot active';
            } else if (stepNum === 4) {
                // Validate Profile in Step 3
                const fName = document.getElementById('register-first-name');
                const lName = document.getElementById('register-last-name');
                const specialty = document.getElementById('register-specialty');
                if (!fName.value.trim() || !lName.value.trim()) {
                    alert('Please enter both your First Name and Last Name.');
                    return;
                }
                if (!specialty.value) {
                    alert('Please select your scientific specialty.');
                    return;
                }

                document.getElementById('fieldset-step-3').style.display = 'none';
                document.getElementById('fieldset-step-4').style.display = 'block';

                document.getElementById('dot-step-3').className = 'healthedia-wizard-indicator-dot completed';
                document.getElementById('line-step-3').className = 'healthedia-wizard-indicator-line active';
                document.getElementById('dot-step-4').className = 'healthedia-wizard-indicator-dot active';
            }
        }

        function prevStep(stepNum) {
            if (stepNum === 1) {
                document.getElementById('fieldset-step-2').style.display = 'none';
                document.getElementById('fieldset-step-1').style.display = 'block';

                document.getElementById('dot-step-1').className = 'healthedia-wizard-indicator-dot active';
                document.getElementById('line-step-1').className = 'healthedia-wizard-indicator-line';
                document.getElementById('dot-step-2').className = 'healthedia-wizard-indicator-dot';
            } else if (stepNum === 2) {
                document.getElementById('fieldset-step-3').style.display = 'none';
                document.getElementById('fieldset-step-2').style.display = 'block';

                document.getElementById('dot-step-2').className = 'healthedia-wizard-indicator-dot active';
                document.getElementById('line-step-2').className = 'healthedia-wizard-indicator-line';
                document.getElementById('dot-step-3').className = 'healthedia-wizard-indicator-dot';
            } else if (stepNum === 3) {
                document.getElementById('fieldset-step-4').style.display = 'none';
                document.getElementById('fieldset-step-3').style.display = 'block';

                document.getElementById('dot-step-3').className = 'healthedia-wizard-indicator-dot active';
                document.getElementById('line-step-3').className = 'healthedia-wizard-indicator-line';
                document.getElementById('dot-step-4').className = 'healthedia-wizard-indicator-dot';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tabsBar = document.getElementById('auth-tabs-bar');
            const tabButtons = document.querySelectorAll('.healthedia-auth-tab');
            const views = {
                login: document.getElementById('view-login'),
                register: document.getElementById('view-register'),
                forgot: document.getElementById('view-forgot')
            };

            function switchView(viewName) {
                if (!views[viewName]) return;

                Object.values(views).forEach(v => { if (v) v.classList.remove('active'); });
                views[viewName].classList.add('active');

                if (viewName === 'forgot') {
                    if (tabsBar) tabsBar.style.display = 'none';
                } else {
                    if (tabsBar) {
                        const login_enabled = <?php echo $login_enabled ? 'true' : 'false'; ?>;
                        const registration_enabled = <?php echo $registration_enabled ? 'true' : 'false'; ?>;
                        if (login_enabled && registration_enabled) {
                            tabsBar.style.display = 'flex';
                        }
                    }
                    tabButtons.forEach(btn => {
                        if (btn.getAttribute('data-tab') === viewName) {
                            btn.classList.add('active');
                        } else {
                            btn.classList.remove('active');
                        }
                    });
                }
            }

            // Restore correct view state if validation failed on a specific action
            const lastAction = "<?php echo isset($_POST['healthedia_action']) ? esc_js($_POST['healthedia_action']) : ''; ?>";
            if (lastAction === 'register') {
                switchView('register');
            } else if (lastAction === 'forgot') {
                switchView('forgot');
            } else {
                const login_enabled = <?php echo $login_enabled ? 'true' : 'false'; ?>;
                if (login_enabled) {
                    switchView('login');
                } else {
                    switchView('register');
                }
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    switchView(this.getAttribute('data-tab'));
                });
            });

            const gotoForgotBtn = document.getElementById('goto-forgot');
            if (gotoForgotBtn) {
                gotoForgotBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchView('forgot');
                });
            }

            const backToLoginBtn = document.getElementById('back-to-login');
            if (backToLoginBtn) {
                backToLoginBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchView('login');
                });
            }
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
