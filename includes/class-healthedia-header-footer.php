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

    // Load Header Menu from options dynamically
    $header_menu = get_option( 'healthedia_header_menu' );
    if ( ! is_array( $header_menu ) ) {
        $header_menu = [
            ['title' => 'Archive Search', 'url' => home_url('/healthedia-search/')],
            ['title' => 'Researchers', 'url' => home_url('/healthedia-dashboard/')],
            ['title' => 'Institutions', 'url' => '#'],
            ['title' => 'Scientific Journal', 'url' => '#']
        ];
    }

    $nav_html = '';
    foreach ( $header_menu as $item ) {
        $item_url = esc_url( $item['url'] );
        $item_title = esc_html( $item['title'] );

        // Determine active class
        $active_class = '';
        if ( strpos( $current_url, 'healthedia-search' ) !== false && strpos( $item_url, 'healthedia-search' ) !== false ) {
            $active_class = 'active';
        } elseif ( strpos( $current_url, 'healthedia-dashboard' ) !== false && strpos( $item_url, 'healthedia-dashboard' ) !== false ) {
            $active_class = 'active';
        } elseif ( $current_url === '/' && strpos( $item_url, 'healthedia-search' ) !== false ) {
            $active_class = 'active';
        }

        $nav_html .= '<a href="' . $item_url . '" class="healthedia-nav-item ' . $active_class . '">' . $item_title . '</a>';
    }

    $auth_area = '';
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : 'Researcher';

        // Calculate initials for dropdown trigger avatar
        $initials = '';
        if ( ! empty( $display_name ) ) {
            $parts = explode( ' ', $display_name );
            if ( count( $parts ) >= 2 ) {
                $initials = strtoupper( substr( $parts[0], 0, 1 ) . substr( $parts[1], 0, 1 ) );
            } else {
                $initials = strtoupper( substr( $display_name, 0, 2 ) );
            }
        } else {
            $initials = 'RE';
        }

        $auth_area = '
        <div class="healthedia-user-dropdown-container" id="healthedia-header-dropdown">
            <button class="healthedia-dropdown-trigger" id="header-user-dropdown-btn">
                <div class="healthedia-header-avatar">' . esc_html( $initials ) . '</div>
                <span class="healthedia-header-user-name">' . esc_html( $display_name ) . '</span>
                <svg class="healthedia-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div class="healthedia-dropdown-menu" id="header-user-dropdown-menu">
                <div class="healthedia-dropdown-header">
                    <div class="healthedia-dropdown-welcome">Welcome back!</div>
                    <div class="healthedia-dropdown-user-name">' . esc_html( $display_name ) . '</div>
                </div>
                <hr class="healthedia-dropdown-divider">
                <a href="' . $dashboard_url . '" class="healthedia-dropdown-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                    SaaS Dashboard
                </a>
                <a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '" class="healthedia-dropdown-item healthedia-logout-btn" id="header-logout-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Log Out
                </a>
            </div>
        </div>
        ';
    } else {
        $auth_area = '<a href="' . $auth_url . '" class="healthedia-btn-login">LOGIN</a>';
    }

    $html = '
    <header class="healthedia-global-header">
        <div class="healthedia-header-container">
            <!-- Left-aligned Logo & Nav Group -->
            <div class="healthedia-header-left-group">
                <a href="' . $home_url . '" class="healthedia-logo-group">
                    <span class="healthedia-logo-title">Healthedia</span>
                    <span class="healthedia-logo-sub">GLOBAL HEALTH ARCHIVE</span>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="healthedia-nav">
                    ' . $nav_html . '
                </nav>

                <!-- Mobile Dropdown Navigation Trigger -->
                <div class="healthedia-mobile-nav-trigger-container" id="healthedia-mobile-trigger-container">
                    <button class="healthedia-mobile-menu-btn" id="mobile-menu-toggle-btn">
                        <span>MENU</span>
                        <svg class="healthedia-dropdown-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="healthedia-mobile-dropdown-menu" id="mobile-dropdown-menu-list">
                        ' . $nav_html . '
                    </div>
                </div>
            </div>

            <!-- Right-aligned Authentication Area -->
            <div class="healthedia-auth-btn-wrapper">
                ' . $auth_area . '
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
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            box-sizing: border-box;
            height: 60px; /* Reduced from 70px for a more compact and elegant profile */
        }

        .healthedia-header-left-group {
            display: flex;
            align-items: center;
            gap: 40px; /* Positions navigation items directly to the right of the logo with consistent spacing */
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
            gap: 16px;
        }

        .healthedia-nav-item {
            text-decoration: none;
            color: var(--healthedia-grey);
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
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
            padding: 10px 28px;
            border-radius: 30px;
            letter-spacing: 1px;
            transition: transform 0.2s ease, opacity 0.2s ease;
            display: inline-block;
        }

        .healthedia-btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* User Dropdown Premium Styling */
        .healthedia-user-dropdown-container {
            position: relative;
            display: inline-block;
        }

        .healthedia-dropdown-trigger {
            background: none;
            border: 1px solid var(--healthedia-border);
            border-radius: 30px;
            padding: 4px 14px 4px 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .healthedia-dropdown-trigger:hover {
            border-color: var(--healthedia-black);
            background-color: #fafafa;
        }

        .healthedia-header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--healthedia-black);
            color: var(--healthedia-white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        .healthedia-header-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #333333;
        }

        .healthedia-dropdown-chevron {
            color: #888888;
            transition: transform 0.2s ease;
        }

        .healthedia-user-dropdown-container.open .healthedia-dropdown-chevron {
            transform: rotate(180deg);
        }

        .healthedia-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            background: #ffffff !important; /* Robust solid white background */
            border: 1px solid var(--healthedia-border);
            border-radius: 16px;
            width: 220px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 12px;
            box-sizing: border-box;
            z-index: 10000;
            animation: dropdownFadeIn 0.2s ease;
        }

        .healthedia-user-dropdown-container.open .healthedia-dropdown-menu {
            display: block;
        }

        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .healthedia-dropdown-header {
            padding: 4px 8px 10px 8px;
            text-align: left;
        }

        .healthedia-dropdown-welcome {
            font-size: 11px;
            font-weight: 700;
            color: #999999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .healthedia-dropdown-user-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--healthedia-black);
            margin-top: 2px;
        }

        .healthedia-dropdown-divider {
            border: 0;
            border-top: 1px solid var(--healthedia-border);
            margin: 6px 0;
        }

        .healthedia-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #555555;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-align: left;
        }

        .healthedia-dropdown-item:hover {
            background-color: #f5f5f5;
            color: var(--healthedia-black);
        }

        .healthedia-dropdown-item.healthedia-logout-btn {
            color: #bf271b;
        }

        .healthedia-dropdown-item.healthedia-logout-btn:hover {
            background-color: #fbeae9;
            color: #bf271b;
        }

        /* Desktop specific mobile menu display reset */
        .healthedia-mobile-nav-trigger-container {
            display: none;
        }

        /* Mobile layout optimization */
        @media (max-width: 768px) {
            .healthedia-header-container {
                border-radius: 20px;
                height: 60px;
                padding: 12px 16px;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .healthedia-header-left-group {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
            }
            .healthedia-nav {
                display: none !important; /* Hide desktop linear navigation menu completely */
            }

            /* Mobile Navigation Dropdown */
            .healthedia-mobile-nav-trigger-container {
                display: inline-block !important;
                position: relative;
            }
            .healthedia-mobile-menu-btn {
                background: none;
                border: 1px solid var(--healthedia-border);
                border-radius: 20px;
                padding: 6px 14px;
                font-size: 11px;
                font-weight: 700;
                color: var(--healthedia-black);
                display: flex;
                align-items: center;
                gap: 6px;
                cursor: pointer;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .healthedia-mobile-menu-btn:hover {
                border-color: var(--healthedia-black);
            }
            .healthedia-mobile-dropdown-menu {
                display: none;
                position: absolute;
                left: 0;
                top: calc(100% + 8px);
                background-color: #ffffff !important; /* Force solid white background */
                background: #ffffff !important;
                border: 1px solid var(--healthedia-border) !important;
                border-radius: 12px;
                width: 180px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important;
                padding: 8px;
                box-sizing: border-box;
                z-index: 100000 !important; /* Ensure it floats above background content */
                animation: mobileDropdownFadeIn 0.2s ease;
            }
            .healthedia-mobile-nav-trigger-container.open .healthedia-mobile-dropdown-menu {
                display: block !important;
            }
            @keyframes mobileDropdownFadeIn {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .healthedia-mobile-dropdown-menu .healthedia-nav-item {
                display: block !important;
                padding: 10px 14px !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                color: var(--healthedia-grey) !important;
                text-decoration: none !important;
                border-radius: 8px !important;
                transition: all 0.2s ease !important;
                text-align: left !important;
                background: none !important;
            }
            .healthedia-mobile-dropdown-menu .healthedia-nav-item:hover,
            .healthedia-mobile-dropdown-menu .healthedia-nav-item.active {
                background-color: var(--healthedia-light-grey) !important;
                color: var(--healthedia-black) !important;
            }

            .healthedia-btn-login {
                padding: 8px 18px;
                font-size: 12px;
            }
            .healthedia-dropdown-trigger {
                padding: 4px 10px 4px 4px;
                gap: 6px;
            }
            .healthedia-header-avatar {
                width: 28px;
                height: 28px;
                font-size: 10px;
            }
            .healthedia-header-user-name {
                font-size: 11px;
            }
            .healthedia-dropdown-menu {
                width: 180px;
                top: calc(100% + 6px);
            }
        }
    </style>

    <script id="healthedia-header-script">
        document.addEventListener(\'DOMContentLoaded\', function() {
            // Deskop user profile dropdown
            const dropdownContainer = document.getElementById(\'healthedia-header-dropdown\');
            const dropdownBtn = document.getElementById(\'header-user-dropdown-btn\');

            if (dropdownBtn && dropdownContainer) {
                dropdownBtn.addEventListener(\'click\', function(e) {
                    e.stopPropagation();
                    dropdownContainer.classList.toggle(\'open\');
                });

                document.addEventListener(\'click\', function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        dropdownContainer.classList.remove(\'open\');
                    }
                });
            }

            // Mobile menu navigation dropdown
            const mobileTrigger = document.getElementById(\'healthedia-mobile-trigger-container\');
            const mobileBtn = document.getElementById(\'mobile-menu-toggle-btn\');

            if (mobileBtn && mobileTrigger) {
                mobileBtn.addEventListener(\'click\', function(e) {
                    e.stopPropagation();
                    mobileTrigger.classList.toggle(\'open\');
                });

                document.addEventListener(\'click\', function(e) {
                    if (!mobileTrigger.contains(e.target)) {
                        mobileTrigger.classList.remove(\'open\');
                    }
                });
            }
        });
    </script>
    ';

    return $html;
}

/**
 * Output the custom Healthedia global footer.
 */
function healthedia_get_footer() {
    // Load Footer Menu from options dynamically
    $footer_menu = get_option( 'healthedia_footer_menu' );
    if ( ! is_array( $footer_menu ) ) {
        $footer_menu = [
            ['title' => 'Privacy Policy', 'url' => '#'],
            ['title' => 'Terms & Conditions', 'url' => '#'],
            ['title' => 'Publication Policies', 'url' => '#'],
            ['title' => 'Certificate Verification', 'url' => '#'],
            ['title' => 'Support', 'url' => '#']
        ];
    }

    $footer_links_html = '';
    $count = count( $footer_menu );
    for ( $i = 0; $i < $count; $i++ ) {
        $item = $footer_menu[$i];
        $footer_links_html .= '<a href="' . esc_url( $item['url'] ) . '" class="healthedia-footer-link">' . esc_html( $item['title'] ) . '</a>';
        if ( $i < $count - 1 ) {
            $footer_links_html .= '<span class="healthedia-footer-dot"></span>';
        }
    }

    $html = '
    <footer class="healthedia-global-footer">
        <div class="healthedia-footer-container">
            <div class="healthedia-footer-copyright">
                © 2026 Healthedia. All Rights Reserved. Permanent Open-Access Repository.
            </div>
            <div class="healthedia-footer-links">
                ' . $footer_links_html . '
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

        /* Mobile footer styling optimization */
        @media (max-width: 768px) {
            .healthedia-footer-container {
                flex-direction: column;
                text-align: center;
                gap: 16px;
                padding-top: 16px;
            }
            .healthedia-footer-copyright {
                font-size: 12px;
                line-height: 1.4;
            }
            .healthedia-footer-links {
                justify-content: center;
                gap: 10px;
            }
            .healthedia-footer-link {
                font-size: 12px;
                padding: 4px 8px;
                background-color: #f7f7f7;
                border-radius: 6px;
            }
            .healthedia-footer-dot {
                display: none; /* Hide static separators on mobile to prevent layout leakage on wraps */
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
