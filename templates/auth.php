<?php
/**
 * Template Name: Authentication Page
 */

$error_message = '';
$success_message = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( isset( $_POST['healthedia_action'] ) ) {
        // Security Nonce Verification
        if ( ! isset( $_POST['healthedia_auth_nonce'] ) || ! wp_verify_nonce( $_POST['healthedia_auth_nonce'], 'healthedia_auth_action' ) ) {
            $error_message = 'Security verification failed. Please refresh the page and try again.';
        } elseif ( $_POST['healthedia_action'] === 'login' ) {
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
                    wp_safe_redirect( home_url( '/healthedia-dashboard/' ) );
                    exit;
                }
            }
        } elseif ( $_POST['healthedia_action'] === 'register' ) {
            $email = sanitize_email( $_POST['email'] );
            $password = sanitize_text_field( $_POST['password'] );
            $name = sanitize_text_field( $_POST['name'] );

            if ( empty( $email ) || empty( $password ) || empty( $name ) ) {
                $error_message = 'All fields are required for registration.';
            } elseif ( email_exists( $email ) ) {
                $error_message = 'This email address is already registered.';
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
                    wp_update_user([
                        'ID' => $user_id,
                        'display_name' => $name,
                    ]);
                    // Auto login after registration
                    $creds = [
                        'user_login'    => $email,
                        'user_password' => $password,
                        'remember'      => true,
                    ];
                    wp_signon( $creds, is_ssl() );
                    wp_safe_redirect( home_url( '/healthedia-dashboard/' ) );
                    exit;
                }
            }
        } elseif ( $_POST['healthedia_action'] === 'forgot' ) {
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
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        .healthedia-auth-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 280px);
            padding: 40px 20px;
            box-sizing: border-box;
            background-color: #ffffff;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .healthedia-auth-card {
            background: #ffffff;
            border: 1px solid #e8e8e8;
            border-radius: 24px;
            width: 100%;
            max-width: 520px;
            padding: 40px;
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
            margin-bottom: 35px;
        }

        .healthedia-auth-tab {
            flex: 1;
            border: none;
            outline: none;
            background: none;
            padding: 12px 0;
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
            font-size: 24px;
            font-weight: 800;
            color: #000000;
            margin: 0 0 8px 0;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }

        .healthedia-auth-form-subtitle {
            font-size: 13px;
            color: #888888;
            text-align: center;
            margin: 0 0 30px 0;
            line-height: 1.5;
            font-weight: 500;
        }

        .healthedia-input-group {
            width: 100%;
            position: relative;
            margin-bottom: 20px;
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
            transition: border-color 0.2s ease;
        }

        .healthedia-auth-input:focus {
            border-color: #000000;
        }

        .healthedia-password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999999;
            display: flex;
            align-items: center;
            padding: 0;
        }

        .healthedia-password-toggle:hover {
            color: #000000;
        }

        .healthedia-forgot-link-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            margin-top: -10px;
            margin-bottom: 30px;
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
            padding: 18px 0;
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

        .healthedia-auth-feedback {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
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

        @media (max-width: 480px) {
            .healthedia-auth-card {
                padding: 24px;
                border-radius: 16px;
            }
            .healthedia-auth-form-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="healthedia-auth-wrapper">
        <div class="healthedia-auth-card">

            <!-- Tabs Header -->
            <div class="healthedia-auth-tabs" id="auth-tabs-bar">
                <button class="healthedia-auth-tab active" data-tab="login" id="tab-login-btn">Login</button>
                <button class="healthedia-auth-tab" data-tab="register" id="tab-register-btn">Create Account</button>
            </div>

            <!-- Feedback Area (displays actual backend validation states) -->
            <?php if ( ! empty( $error_message ) ) : ?>
                <div class="healthedia-auth-feedback error" id="auth-feedback"><?php echo $error_message; ?></div>
            <?php elseif ( ! empty( $success_message ) ) : ?>
                <div class="healthedia-auth-feedback success" id="auth-feedback"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <!-- LOGIN VIEW -->
            <div class="healthedia-auth-form-view active" id="view-login">
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

            <!-- REGISTRATION VIEW -->
            <div class="healthedia-auth-form-view" id="view-register">
                <h2 class="healthedia-auth-form-title">CREATE AN ACCOUNT</h2>
                <p class="healthedia-auth-form-subtitle">Join the leading repository for elite sports and clinical health research.</p>

                <form id="form-register" method="POST" style="width: 100%;">
                    <?php wp_nonce_field( 'healthedia_auth_action', 'healthedia_auth_nonce' ); ?>
                    <input type="hidden" name="healthedia_action" value="register">
                    <div class="healthedia-input-group">
                        <input type="text" name="name" class="healthedia-auth-input" id="register-name" placeholder="Full Name" required value="<?php echo isset($_POST['name']) ? esc_attr($_POST['name']) : ''; ?>">
                    </div>
                    <div class="healthedia-input-group">
                        <input type="email" name="email" class="healthedia-auth-input" id="register-email" placeholder="Institutional Email Address" required value="<?php echo isset($_POST['email']) && isset($_POST['healthedia_action']) && $_POST['healthedia_action'] === 'register' ? esc_attr($_POST['email']) : ''; ?>">
                    </div>
                    <div class="healthedia-input-group">
                        <input type="password" name="password" class="healthedia-auth-input" id="register-password" placeholder="Password" required>
                        <button type="button" class="healthedia-password-toggle" onclick="togglePassword('register-password')">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <button type="submit" class="healthedia-auth-submit">REGISTER TO ARCHIVE</button>
                </form>
            </div>

            <!-- FORGOT PASSWORD VIEW -->
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

        </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const tabsBar = document.getElementById('auth-tabs-bar');
            const tabButtons = document.querySelectorAll('.healthedia-auth-tab');
            const views = {
                login: document.getElementById('view-login'),
                register: document.getElementById('view-register'),
                forgot: document.getElementById('view-forgot')
            };

            function switchView(viewName) {
                Object.values(views).forEach(v => v.classList.remove('active'));
                views[viewName].classList.add('active');

                if (viewName === 'forgot') {
                    tabsBar.style.display = 'none';
                } else {
                    tabsBar.style.display = 'flex';
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
                switchView('login');
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    switchView(this.getAttribute('data-tab'));
                });
            });

            document.getElementById('goto-forgot').addEventListener('click', function(e) {
                e.preventDefault();
                switchView('forgot');
            });

            document.getElementById('back-to-login').addEventListener('click', function(e) {
                e.preventDefault();
                switchView('login');
            });
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
