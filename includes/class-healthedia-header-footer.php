<?php
/**
 * Global Header and Footer Layout and Custom Injector
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output the custom Healthedia global header.
 */
function healthedia_get_header() {
    $current_url = $_SERVER['REQUEST_URI'];
    $is_search = ( strpos( $current_url, 'healthedia-search' ) !== false || $current_url === '/' || $current_url === '/index.php' || is_front_page() );
    $is_auth = ( strpos( $current_url, 'healthedia-auth' ) !== false );
    $is_dashboard = ( strpos( $current_url, 'healthedia-dashboard' ) !== false );

    $home_url = esc_url( home_url( '/' ) );
    $auth_url = esc_url( home_url( '/healthedia-auth/' ) );
    $search_url = esc_url( home_url( '/healthedia-search/' ) );
    $dashboard_url = esc_url( home_url( '/healthedia-dashboard/' ) );

    $active_search = $is_search ? 'active' : '';
    $active_dashboard = $is_dashboard ? 'active' : '';

    $auth_btn = '';
    if ( is_user_logged_in() ) {
        $auth_btn = '<a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '" class="healthedia-btn-login">LOGOUT</a>';
    } else {
        $auth_btn = '<a href="' . $auth_url . '" class="healthedia-btn-login">LOGIN</a>';
    }

    $html = '
    <header class="healthedia-global-header">
        <div class="healthedia-header-container">
            <!-- Logo Section -->
            <a href="' . $home_url . '" class="healthedia-logo-group">
                <span class="healthedia-logo-title">Healthedia</span>
                <span class="healthedia-logo-sub">GLOBAL HEALTH ARCHIVE</span>
            </a>

            <!-- Navigation Links -->
            <nav class="healthedia-nav">
                <a href="' . $search_url . '" class="healthedia-nav-item ' . $active_search . '">
                    Archive Search
                </a>
                <a href="' . $dashboard_url . '" class="healthedia-nav-item ' . $active_dashboard . '">
                    Researchers
                </a>
                <a href="#" class="healthedia-nav-item">
                    Institutions
                </a>
                <a href="#" class="healthedia-nav-item">
                    Scientific Journal
                </a>
            </nav>

            <!-- Authentication Button -->
            <div class="healthedia-auth-btn-wrapper">
                ' . $auth_btn . '
            </div>
        </div>
    </header>

    <style>
        @import url(\'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap\');

        :root {
            --healthedia-font: \'Inter\', system-ui, -apple-system, sans-serif;
            --healthedia-black: #000000;
            --healthedia-white: #ffffff;
            --healthedia-grey: #666666;
            --healthedia-light-grey: #f7f7f7;
            --healthedia-border: #e5e5e5;
        }

        .healthedia-global-header {
            width: 100%;
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
            box-sizing: border-box;
            font-family: var(--healthedia-font);
        }

        .healthedia-header-container {
            background: var(--healthedia-white);
            border: 1px solid var(--healthedia-border);
            border-radius: 50px;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            box-sizing: border-box;
            height: 70px;
        }

        .healthedia-logo-group {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: var(--healthedia-black);
            line-height: 1.1;
        }

        .healthedia-logo-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .healthedia-logo-sub {
            font-size: 8px;
            font-weight: 700;
            color: #999999;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        .healthedia-nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .healthedia-nav-item {
            text-decoration: none;
            color: var(--healthedia-grey);
            font-size: 14px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 30px;
            transition: all 0.2s ease;
        }

        .healthedia-nav-item:hover {
            color: var(--healthedia-black);
        }

        .healthedia-nav-item.active {
            background: var(--healthedia-black);
            color: var(--healthedia-white) !important;
        }

        .healthedia-btn-login {
            background: var(--healthedia-black);
            color: var(--healthedia-white);
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 32px;
            border-radius: 30px;
            letter-spacing: 1px;
            transition: transform 0.2s ease, opacity 0.2s ease;
            display: inline-block;
        }

        .healthedia-btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Mobile layout */
        @media (max-width: 768px) {
            .healthedia-header-container {
                border-radius: 25px;
                height: auto;
                padding: 12px 16px;
                flex-direction: column;
                gap: 12px;
            }
            .healthedia-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
            }
            .healthedia-nav-item {
                font-size: 12px;
                padding: 6px 12px;
            }
            .healthedia-btn-login {
                padding: 8px 20px;
                font-size: 12px;
            }
        }
    </style>
    ';

    return $html;
}

/**
 * Output the custom Healthedia global footer.
 */
function healthedia_get_footer() {
    $html = '
    <footer class="healthedia-global-footer">
        <div class="healthedia-footer-container">
            <div class="healthedia-footer-copyright">
                © 2026 Healthedia. All Rights Reserved. Permanent Open-Access Repository.
            </div>
            <div class="healthedia-footer-links">
                <a href="#" class="healthedia-footer-link">Privacy Policy</a>
                <span class="healthedia-footer-dot"></span>
                <a href="#" class="healthedia-footer-link">Terms & Conditions</a>
                <span class="healthedia-footer-dot"></span>
                <a href="#" class="healthedia-footer-link">Publication Policies</a>
                <span class="healthedia-footer-dot"></span>
                <a href="#" class="healthedia-footer-link">Certificate Verification</a>
                <span class="healthedia-footer-dot"></span>
                <a href="#" class="healthedia-footer-link">Support</a>
            </div>
        </div>
    </footer>

    <style>
        .healthedia-global-footer {
            width: 100%;
            max-width: 1400px;
            margin: 40px auto 20px auto;
            padding: 0 20px;
            box-sizing: border-box;
            font-family: var(--healthedia-font);
        }

        .healthedia-footer-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--healthedia-border);
            padding-top: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .healthedia-footer-copyright {
            font-size: 13px;
            color: #999999;
            font-weight: 500;
        }

        .healthedia-footer-links {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .healthedia-footer-link {
            text-decoration: none;
            font-size: 13px;
            color: #666666;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .healthedia-footer-link:hover {
            color: var(--healthedia-black);
        }

        .healthedia-footer-dot {
            width: 4px;
            height: 4px;
            background-color: #cccccc;
            border-radius: 50%;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .healthedia-footer-container {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            .healthedia-footer-links {
                justify-content: center;
                gap: 8px;
            }
        }
    </style>
    ';
    return $html;
}

/**
 * Handle Output Buffering for injecting the Healthedia Header and Footer.
 */
function healthedia_output_buffer_start() {
    ob_start( 'healthedia_output_buffer_callback' );
}
add_action( 'template_redirect', 'healthedia_output_buffer_start', 1 );

function healthedia_output_buffer_callback( $buffer ) {
    // Return early if this is an admin request or an AJAX request
    if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        return $buffer;
    }

    $current_url = $_SERVER['REQUEST_URI'];
    $is_dashboard = ( strpos( $current_url, 'healthedia-dashboard' ) !== false );

    // Injected override CSS to cleanly hide default theme header and footer
    $override_css = '
    <style id="healthedia-override-css">
        /* Hide theme default headers and footers completely, excluding Healthedia custom elements */
        header:not(.healthedia-global-header),
        footer:not(.healthedia-global-footer),
        .site-header, .site-footer,
        #masthead, #colophon,
        .ast-primary-header-bar, .ast-theme-transparent-header,
        .site-footer-width, .main-header-bar, .ast-footer-builder-area,
        .ast-header-bar-wrap, .ast-footer-copyright,
        .theme-default-header, .theme-default-footer {
            display: none !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
        }
    </style>
    ';

    // Inject override CSS inside <head>
    if ( strpos( $buffer, '</head>' ) !== false ) {
        $buffer = str_replace( '</head>', $override_css . '</head>', $buffer );
    } else {
        $buffer = $override_css . $buffer;
    }

    // Exclude the dashboard from Healthedia global header and footer injections
    if ( $is_dashboard ) {
        return $buffer;
    }

    $custom_header = healthedia_get_header();
    $custom_footer = healthedia_get_footer();

    // Prepend custom header immediately after <body ...>
    $body_pattern = '/<(body)(\s[^>]*)?>/i';
    if ( preg_match( $body_pattern, $buffer, $matches ) ) {
        $full_body_tag = $matches[0];
        $buffer = str_replace( $full_body_tag, $full_body_tag . $custom_header, $buffer );
    } else {
        $buffer = $custom_header . $buffer;
    }

    // Append custom footer immediately before </body>
    if ( strpos( $buffer, '</body>' ) !== false ) {
        $buffer = str_replace( '</body>', $custom_footer . '</body>', $buffer );
    } else {
        $buffer = $buffer . $custom_footer;
    }

    return $buffer;
}
