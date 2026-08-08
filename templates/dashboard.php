<?php
/**
 * Template Name: Healthedia SaaS Dashboard
 */

// Secure backend access control integration
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/login/' ) );
    exit;
}

$success_notification = '';

// Handle POST actions for Settings, Authentication Controls, and Header/Footer Links with complete CSRF Nonce Validation
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( ! isset( $_POST['healthedia_dashboard_nonce'] ) || ! wp_verify_nonce( $_POST['healthedia_dashboard_nonce'], 'healthedia_dashboard_action_nonce' ) ) {
        $success_notification = 'Security verification failed. Please refresh the page and try again.';
    } else {
        if ( isset( $_POST['healthedia_dashboard_action'] ) ) {

            // 1. Save Authentication Settings
            if ( $_POST['healthedia_dashboard_action'] === 'save_auth_settings' ) {
                $login_val = isset( $_POST['auth_login'] ) ? sanitize_text_field( $_POST['auth_login'] ) : 'disabled';
                $reg_val = isset( $_POST['auth_registration'] ) ? sanitize_text_field( $_POST['auth_registration'] ) : 'disabled';

                $auth_opts = [
                    'login' => $login_val,
                    'registration' => $reg_val
                ];
                update_option( 'healthedia_auth_options', $auth_opts );
                $success_notification = 'Authentication settings saved successfully!';
            }

            // 2. Manage Header & Footer Links
            elseif ( $_POST['healthedia_dashboard_action'] === 'manage_links' ) {
                $link_type = isset( $_POST['link_type'] ) ? sanitize_text_field( $_POST['link_type'] ) : 'header';
                $option_name = ( $link_type === 'footer' ) ? 'healthedia_footer_menu' : 'healthedia_header_menu';
                $menu = get_option( $option_name );

                if ( ! is_array( $menu ) ) {
                    if ( $link_type === 'footer' ) {
                        $menu = [
                            ['title' => 'Privacy Policy', 'url' => '#'],
                            ['title' => 'Terms & Conditions', 'url' => '#'],
                            ['title' => 'Publication Policies', 'url' => '#'],
                            ['title' => 'Certificate Verification', 'url' => '#'],
                            ['title' => 'Support', 'url' => '#']
                        ];
                    } else {
                        $menu = [
                            ['title' => 'Archive Search', 'url' => home_url('/healthedia-search/')],
                            ['title' => 'Researchers', 'url' => home_url('/healthedia-dashboard/')],
                            ['title' => 'Institutions', 'url' => '#'],
                            ['title' => 'Scientific Journal', 'url' => '#']
                        ];
                    }
                }

                $link_action = isset( $_POST['link_action'] ) ? sanitize_text_field( $_POST['link_action'] ) : '';
                $index = isset( $_POST['link_index'] ) ? intval( $_POST['link_index'] ) : -1;

                if ( $link_action === 'add' ) {
                    $title = isset( $_POST['new_title'] ) ? sanitize_text_field( $_POST['new_title'] ) : '';
                    $url = isset( $_POST['new_url'] ) ? sanitize_text_field( $_POST['new_url'] ) : '';
                    if ( ! empty( $title ) && ! empty( $url ) ) {
                        $menu[] = ['title' => $title, 'url' => $url];
                        update_option( $option_name, $menu );
                        $success_notification = 'New page successfully added to ' . esc_html( ucfirst( $link_type ) ) . ' navigation!';
                    }
                } elseif ( $link_action === 'remove' && $index >= 0 && isset( $menu[$index] ) ) {
                    array_splice( $menu, $index, 1 );
                    update_option( $option_name, $menu );
                    $success_notification = 'Page removed from ' . esc_html( ucfirst( $link_type ) ) . ' navigation!';
                } elseif ( $link_action === 'move_up' && $index > 0 && isset( $menu[$index] ) ) {
                    $temp = $menu[$index];
                    $menu[$index] = $menu[$index - 1];
                    $menu[$index - 1] = $temp;
                    update_option( $option_name, $menu );
                    $success_notification = esc_html( ucfirst( $link_type ) ) . ' display order updated!';
                } elseif ( $link_action === 'move_down' && $index >= 0 && $index < count( $menu ) - 1 && isset( $menu[$index] ) ) {
                    $temp = $menu[$index];
                    $menu[$index] = $menu[$index + 1];
                    $menu[$index + 1] = $temp;
                    update_option( $option_name, $menu );
                    $success_notification = esc_html( ucfirst( $link_type ) ) . ' display order updated!';
                }
            }
        }
    }
}

// Read current values for rendering
$auth_options = get_option( 'healthedia_auth_options', ['login' => 'enabled', 'registration' => 'enabled'] );
$login_val = isset( $auth_options['login'] ) ? $auth_options['login'] : 'enabled';
$reg_val = isset( $auth_options['registration'] ) ? $auth_options['registration'] : 'enabled';

$header_menu = get_option( 'healthedia_header_menu' );
if ( ! is_array( $header_menu ) ) {
    $header_menu = [
        ['title' => 'Archive Search', 'url' => home_url('/healthedia-search/')],
        ['title' => 'Researchers', 'url' => home_url('/healthedia-dashboard/')],
        ['title' => 'Institutions', 'url' => '#'],
        ['title' => 'Scientific Journal', 'url' => '#']
    ];
}

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

$current_user = wp_get_current_user();
$display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : 'Researcher';

// Calculate initials for the avatar
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
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #fafafa !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #111111;
        }

        .healthedia-dashboard-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Fixed Left Sidebar */
        .healthedia-sidebar {
            width: 260px;
            background-color: #ffffff;
            border-right: 1px solid #e5e5e5;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-sizing: border-box;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .healthedia-sidebar-logo {
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: #000000;
            line-height: 1.1;
            margin-bottom: 40px;
        }

        .healthedia-sidebar-logo-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .healthedia-sidebar-logo-sub {
            font-size: 8px;
            font-weight: 700;
            color: #999999;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        .healthedia-sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .healthedia-sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #666666;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.2s ease;
            background: none;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: left;
        }

        .healthedia-sidebar-item:hover,
        .healthedia-sidebar-item.active {
            background-color: #f3f3f3;
            color: #000000;
        }

        .healthedia-sidebar-item svg {
            width: 18px;
            height: 18px;
        }

        .healthedia-sidebar-footer {
            border-top: 1px solid #e5e5e5;
            padding-top: 16px;
        }

        /* Slim Top Navigation Bar */
        .healthedia-main-content {
            flex-grow: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .healthedia-topbar {
            height: 70px;
            background-color: #ffffff;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            box-sizing: border-box;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .healthedia-topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: #000000;
        }

        .healthedia-topbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .healthedia-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #000000;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .healthedia-user-name {
            font-size: 14px;
            font-weight: 600;
            color: #333333;
        }

        /* Dashboard Main Area Grid */
        .healthedia-dashboard-viewport {
            padding: 32px;
            flex-grow: 1;
            box-sizing: border-box;
        }

        .healthedia-dashboard-section-view {
            display: none;
            animation: sectionFadeIn 0.3s ease;
        }

        .healthedia-dashboard-section-view.active {
            display: block;
        }

        @keyframes sectionFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .healthedia-grid-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .healthedia-metric-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 24px;
            box-sizing: border-box;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
        }

        .healthedia-metric-label {
            font-size: 12px;
            font-weight: 700;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .healthedia-metric-value {
            font-size: 32px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 6px;
            line-height: 1;
        }

        .healthedia-metric-trend {
            font-size: 12px;
            font-weight: 600;
            color: #1b8a4f;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Table Card */
        .healthedia-table-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 24px;
            box-sizing: border-box;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
            margin-bottom: 32px;
        }

        .healthedia-table-header {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #000000;
        }

        .healthedia-table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .healthedia-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .healthedia-table th {
            font-size: 12px;
            font-weight: 700;
            color: #888888;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e5e5;
        }

        .healthedia-table td {
            font-size: 14px;
            color: #333333;
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .healthedia-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .healthedia-badge.success {
            background-color: #e6f6ec;
            color: #1b8a4f;
        }

        .healthedia-badge.warning {
            background-color: #fef5e6;
            color: #c47f17;
        }

        /* Config Card Forms & settings items */
        .healthedia-settings-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            padding: 32px;
            box-sizing: border-box;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
            margin-bottom: 32px;
        }

        .healthedia-settings-title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            color: #000000;
        }

        .healthedia-settings-desc {
            font-size: 13px;
            color: #777777;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .healthedia-form-row {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .healthedia-form-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #444444;
            letter-spacing: 0.5px;
        }

        .healthedia-select-input {
            width: 100%;
            max-width: 400px;
            background-color: #ffffff;
            border: 1px solid #cccccc;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            font-family: inherit;
            color: #333333;
            outline: none;
        }

        .healthedia-btn-save {
            background-color: #000000;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 28px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .healthedia-btn-save:hover {
            opacity: 0.9;
        }

        /* Link items listing container */
        .healthedia-links-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .healthedia-link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fafafa;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 14px 20px;
        }

        .healthedia-link-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .healthedia-link-title {
            font-size: 14px;
            font-weight: 700;
            color: #000000;
        }

        .healthedia-link-url {
            font-size: 12px;
            color: #777777;
            font-family: monospace;
        }

        .healthedia-link-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .healthedia-btn-action {
            background: #ffffff;
            border: 1px solid #cccccc;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #333333;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .healthedia-btn-action:hover {
            background-color: #f0f0f0;
            border-color: #888888;
        }

        .healthedia-btn-action.delete {
            color: #bf271b;
            border-color: #fbeae9;
            background-color: #fbeae9;
        }

        .healthedia-btn-action.delete:hover {
            background-color: #bf271b;
            color: #ffffff;
        }

        /* Inline add link form */
        .healthedia-inline-add-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            background-color: #fafafa;
            border: 1px dashed #cccccc;
            border-radius: 12px;
            padding: 20px;
        }

        .healthedia-input-field {
            background-color: #ffffff;
            border: 1px solid #cccccc;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: inherit;
            width: 100%;
            box-sizing: border-box;
        }

        /* Success Notifications banner */
        .healthedia-notification-banner {
            background-color: #e6f6ec;
            color: #1b8a4f;
            border: 1px solid #1b8a4f;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Mobile Hamburger & Overlay */
        .healthedia-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            color: #000000;
            padding: 6px;
        }

        @media (max-width: 991px) {
            .healthedia-sidebar {
                transform: translateX(-100%);
            }
            .healthedia-sidebar.open {
                transform: translateX(0);
            }
            .healthedia-main-content {
                margin-left: 0;
            }
            .healthedia-menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="healthedia-dashboard-layout">

        <!-- Sidebar Navigation -->
        <aside class="healthedia-sidebar" id="dashboard-sidebar">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-sidebar-logo">
                <span class="healthedia-sidebar-logo-title">Healthedia</span>
                <span class="healthedia-sidebar-logo-sub">GLOBAL HEALTH ARCHIVE</span>
            </a>

            <nav class="healthedia-sidebar-menu">
                <button class="healthedia-sidebar-item active" data-section="overview">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                    Overview
                </button>
                <button class="healthedia-sidebar-item" data-section="analytics">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Analytics
                </button>
                <button class="healthedia-sidebar-item" data-section="archive-records">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Archive Records
                </button>
                <button class="healthedia-sidebar-item" data-section="researchers-profile">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Researchers Profile
                </button>
                <button class="healthedia-sidebar-item" data-section="settings" id="sidebar-tab-settings">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Settings
                </button>
            </nav>

            <div class="healthedia-sidebar-footer">
                <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="healthedia-sidebar-item">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="healthedia-main-content">

            <!-- Slim Top Navigation Bar -->
            <header class="healthedia-topbar">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button class="healthedia-menu-toggle" id="toggle-sidebar-btn" aria-label="Toggle Sidebar">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="healthedia-topbar-title" id="dashboard-title-label">Researchers Dashboard</div>
                </div>

                <div class="healthedia-topbar-user">
                    <div class="healthedia-user-avatar"><?php echo esc_html( $initials ); ?></div>
                    <span class="healthedia-user-name"><?php echo esc_html( $display_name ); ?></span>
                </div>
            </header>

            <!-- Dashboard Contents -->
            <main class="healthedia-dashboard-viewport">

                <!-- Sleek Notification banner for instant configuration feedback -->
                <?php if ( ! empty( $success_notification ) ) : ?>
                    <div class="healthedia-notification-banner" id="dashboard-success-banner">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span><?php echo $success_notification; ?></span>
                    </div>
                <?php endif; ?>

                <!-- SECTION 1: OVERVIEW -->
                <div class="healthedia-dashboard-section-view active" id="sec-overview">

                    <!-- Grid of metrics -->
                    <div class="healthedia-grid-metrics">
                        <div class="healthedia-metric-card">
                            <div class="healthedia-metric-label">Total Citations</div>
                            <div class="healthedia-metric-value">1,482</div>
                            <div class="healthedia-metric-trend">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                +12% this month
                            </div>
                        </div>
                        <div class="healthedia-metric-card">
                            <div class="healthedia-metric-label">Indexed Documents</div>
                            <div class="healthedia-metric-value">248</div>
                            <div class="healthedia-metric-trend">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                +5%
                            </div>
                        </div>
                        <div class="healthedia-metric-card">
                            <div class="healthedia-metric-label">Global Co-authors</div>
                            <div class="healthedia-metric-value">37</div>
                            <div class="healthedia-metric-trend">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                +3 new
                            </div>
                        </div>
                        <div class="healthedia-metric-card">
                            <div class="healthedia-metric-label">Impact Factor Score</div>
                            <div class="healthedia-metric-value">8.4</div>
                            <div class="healthedia-metric-trend">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                High Impact
                            </div>
                        </div>
                    </div>

                    <!-- Main Data Table Card -->
                    <div class="healthedia-table-card">
                        <div class="healthedia-table-header">Recent Indexing & Publication Submissions</div>
                        <div class="healthedia-table-responsive">
                            <table class="healthedia-table">
                                <thead>
                                    <tr>
                                        <th>Document Title</th>
                                        <th>Journal Category</th>
                                        <th>Submission Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Effects of HIIT vs. Continuous Aerobic Training on Biomechanical Gait Adaptations</td>
                                        <td>Clinical Kinesiology</td>
                                        <td>Aug 5, 2026</td>
                                        <td><span class="healthedia-badge success">Approved</span></td>
                                    </tr>
                                    <tr>
                                        <td>Achilles Tendinopathy Rehabilitation Pathways: A Metasummary of Force Distribution</td>
                                        <td>Rehabilitation Sciences</td>
                                        <td>Jul 28, 2026</td>
                                        <td><span class="healthedia-badge success">Approved</span></td>
                                    </tr>
                                    <tr>
                                        <td>Role of Myokines in Regulating Muscle Aging and Sarcopenia Dynamics</td>
                                        <td>Geriatric Sports Medicine</td>
                                        <td>Jul 15, 2026</td>
                                        <td><span class="healthedia-badge warning">In Review</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: ANALYTICS -->
                <div class="healthedia-dashboard-section-view" id="sec-analytics">
                    <div class="healthedia-settings-card">
                        <h3 class="healthedia-settings-title">Simulated Real-Time Analytics</h3>
                        <p class="healthedia-settings-desc">Review global open-access access indexes, reader demography, and citation velocity.</p>

                        <div class="healthedia-table-card" style="box-shadow: none; border: none; padding: 0;">
                            <table class="healthedia-table">
                                <thead>
                                    <tr>
                                        <th>Specialty Area</th>
                                        <th>Active Readers</th>
                                        <th>Monthly Downloads</th>
                                        <th>Citation Trend</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Sports Physiology</td>
                                        <td>12,482</td>
                                        <td>4,842</td>
                                        <td style="color: #1b8a4f; font-weight: 700;">+14%</td>
                                    </tr>
                                    <tr>
                                        <td>Clinical Biomechanics</td>
                                        <td>9,842</td>
                                        <td>3,124</td>
                                        <td style="color: #1b8a4f; font-weight: 700;">+8%</td>
                                    </tr>
                                    <tr>
                                        <td>Neurological Rehab</td>
                                        <td>6,104</td>
                                        <td>2,094</td>
                                        <td style="color: #1b8a4f; font-weight: 700;">+24%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: ARCHIVE RECORDS -->
                <div class="healthedia-dashboard-section-view" id="sec-archive-records">
                    <div class="healthedia-settings-card">
                        <h3 class="healthedia-settings-title">Global Paper Submissions</h3>
                        <p class="healthedia-settings-desc">Search and access indexed documents directly from your institution.</p>

                        <div class="healthedia-form-row">
                            <input type="text" class="healthedia-input-field" placeholder="Search by paper ID, DOI or specialty..." style="max-width: 400px;">
                        </div>
                        <button type="button" class="healthedia-btn-save">QUERY ARCHIVE</button>
                    </div>
                </div>

                <!-- SECTION 4: RESEARCHERS PROFILE -->
                <div class="healthedia-dashboard-section-view" id="sec-researchers-profile">
                    <div class="healthedia-settings-card">
                        <h3 class="healthedia-settings-title">Researcher Profile Metadata</h3>
                        <p class="healthedia-settings-desc">Manage your academic credentials, verified affiliations, and bibliography.</p>

                        <div class="healthedia-form-row">
                            <label class="healthedia-form-label">Full Academic Name</label>
                            <input type="text" class="healthedia-input-field" value="<?php echo esc_attr( $display_name ); ?>" readonly style="max-width: 400px; background-color: #fafafa;">
                        </div>
                        <div class="healthedia-form-row">
                            <label class="healthedia-form-label">Affiliated Institution</label>
                            <input type="text" class="healthedia-input-field" value="Cambridge University" readonly style="max-width: 400px; background-color: #fafafa;">
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: SETTINGS (ADMINISTRATION & CONFIGURATION PANEL) -->
                <div class="healthedia-dashboard-section-view" id="sec-settings">

                    <!-- Settings Area A: Authentication Options -->
                    <div class="healthedia-settings-card" id="settings-auth-card">
                        <h3 class="healthedia-settings-title">Authentication Services Control</h3>
                        <p class="healthedia-settings-desc">Toggle the status of login forms and registration wizards globally across the Healthedia plugin.</p>

                        <form method="POST">
                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                            <input type="hidden" name="healthedia_dashboard_action" value="save_auth_settings">

                            <div class="healthedia-form-row">
                                <label class="healthedia-form-label" for="auth_login">Sign-in Status</label>
                                <select name="auth_login" id="auth_login" class="healthedia-select-input">
                                    <option value="enabled" <?php selected( $login_val, 'enabled' ); ?>>Enabled (Active sign-in permitted)</option>
                                    <option value="disabled" <?php selected( $login_val, 'disabled' ); ?>>Disabled (Lock sign-in portal)</option>
                                </select>
                            </div>

                            <div class="healthedia-form-row">
                                <label class="healthedia-form-label" for="auth_registration">Registration Wizard Status</label>
                                <select name="auth_registration" id="auth_registration" class="healthedia-select-input">
                                    <option value="enabled" <?php selected( $reg_val, 'enabled' ); ?>>Enabled (Allow new accounts to register)</option>
                                    <option value="disabled" <?php selected( $reg_val, 'disabled' ); ?>>Disabled (Lock registration wizard)</option>
                                </select>
                            </div>

                            <button type="submit" class="healthedia-btn-save" id="btn-save-auth-settings">SAVE AUTHENTICATION OPTIONS</button>
                        </form>
                    </div>

                    <!-- Settings Area B: Header Menu Links Manager -->
                    <div class="healthedia-settings-card" id="settings-header-links-card">
                        <h3 class="healthedia-settings-title">Header Navigation Manager</h3>
                        <p class="healthedia-settings-desc">Control, re-order, add, or remove page links shown inside the global header navigation bar.</p>

                        <div class="healthedia-links-list" id="header-links-manager-list">
                            <?php foreach ( $header_menu as $idx => $item ) : ?>
                                <div class="healthedia-link-item" data-idx="<?php echo $idx; ?>">
                                    <div class="healthedia-link-details">
                                        <span class="healthedia-link-title"><?php echo esc_html( $item['title'] ); ?></span>
                                        <span class="healthedia-link-url"><?php echo esc_html( $item['url'] ); ?></span>
                                    </div>
                                    <div class="healthedia-link-actions">
                                        <!-- Move Up button -->
                                        <form method="POST" style="display:inline;">
                                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                                            <input type="hidden" name="link_type" value="header">
                                            <input type="hidden" name="link_action" value="move_up">
                                            <input type="hidden" name="link_index" value="<?php echo $idx; ?>">
                                            <button type="submit" class="healthedia-btn-action" title="Move Up" <?php echo ($idx === 0) ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''; ?>>▲</button>
                                        </form>

                                        <!-- Move Down button -->
                                        <form method="POST" style="display:inline;">
                                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                                            <input type="hidden" name="link_type" value="header">
                                            <input type="hidden" name="link_action" value="move_down">
                                            <input type="hidden" name="link_index" value="<?php echo $idx; ?>">
                                            <button type="submit" class="healthedia-btn-action" title="Move Down" <?php echo ($idx === count($header_menu) - 1) ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''; ?>>▼</button>
                                        </form>

                                        <!-- Delete item button -->
                                        <form method="POST" style="display:inline;">
                                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                                            <input type="hidden" name="link_type" value="header">
                                            <input type="hidden" name="link_action" value="remove">
                                            <input type="hidden" name="link_index" value="<?php echo $idx; ?>">
                                            <button type="submit" class="healthedia-btn-action delete" title="Delete Link">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Add new Header page inline form -->
                        <h4 style="font-size:13px; font-weight:700; margin-bottom:10px;">ADD NEW HEADER LINK</h4>
                        <form method="POST" class="healthedia-inline-add-form" id="form-add-header-link">
                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                            <input type="hidden" name="link_type" value="header">
                            <input type="hidden" name="link_action" value="add">

                            <div style="flex: 2; min-width: 150px; text-align: left;">
                                <label class="healthedia-form-label" style="display:block; margin-bottom:4px;">Page Display Title</label>
                                <input type="text" name="new_title" class="healthedia-input-field" placeholder="e.g. Publications" required>
                            </div>
                            <div style="flex: 3; min-width: 180px; text-align: left;">
                                <label class="healthedia-form-label" style="display:block; margin-bottom:4px;">Page Target URL</label>
                                <input type="text" name="new_url" class="healthedia-input-field" placeholder="e.g. /publications/ or http://..." required value="/">
                            </div>
                            <button type="submit" class="healthedia-btn-save" style="padding: 10px 24px;">ADD LINK</button>
                        </form>
                    </div>

                    <!-- Settings Area C: Footer Menu Links Manager -->
                    <div class="healthedia-settings-card" id="settings-footer-links-card">
                        <h3 class="healthedia-settings-title">Footer Navigation Manager</h3>
                        <p class="healthedia-settings-desc">Control, re-order, add, or remove page links shown inside the global footer copyright bar.</p>

                        <div class="healthedia-links-list" id="footer-links-manager-list">
                            <?php foreach ( $footer_menu as $idx => $item ) : ?>
                                <div class="healthedia-link-item" data-idx="<?php echo $idx; ?>">
                                    <div class="healthedia-link-details">
                                        <span class="healthedia-link-title"><?php echo esc_html( $item['title'] ); ?></span>
                                        <span class="healthedia-link-url"><?php echo esc_html( $item['url'] ); ?></span>
                                    </div>
                                    <div class="healthedia-link-actions">
                                        <!-- Move Up button -->
                                        <form method="POST" style="display:inline;">
                                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                                            <input type="hidden" name="link_type" value="footer">
                                            <input type="hidden" name="link_action" value="move_up">
                                            <input type="hidden" name="link_index" value="<?php echo $idx; ?>">
                                            <button type="submit" class="healthedia-btn-action" title="Move Up" <?php echo ($idx === 0) ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''; ?>>▲</button>
                                        </form>

                                        <!-- Move Down button -->
                                        <form method="POST" style="display:inline;">
                                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                                            <input type="hidden" name="link_type" value="footer">
                                            <input type="hidden" name="link_action" value="move_down">
                                            <input type="hidden" name="link_index" value="<?php echo $idx; ?>">
                                            <button type="submit" class="healthedia-btn-action" title="Move Down" <?php echo ($idx === count($footer_menu) - 1) ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''; ?>>▼</button>
                                        </form>

                                        <!-- Delete item button -->
                                        <form method="POST" style="display:inline;">
                                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                                            <input type="hidden" name="link_type" value="footer">
                                            <input type="hidden" name="link_action" value="remove">
                                            <input type="hidden" name="link_index" value="<?php echo $idx; ?>">
                                            <button type="submit" class="healthedia-btn-action delete" title="Delete Link">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Add new Footer page inline form -->
                        <h4 style="font-size:13px; font-weight:700; margin-bottom:10px;">ADD NEW FOOTER LINK</h4>
                        <form method="POST" class="healthedia-inline-add-form" id="form-add-footer-link">
                            <?php wp_nonce_field( 'healthedia_dashboard_action_nonce', 'healthedia_dashboard_nonce' ); ?>
                            <input type="hidden" name="healthedia_dashboard_action" value="manage_links">
                            <input type="hidden" name="link_type" value="footer">
                            <input type="hidden" name="link_action" value="add">

                            <div style="flex: 2; min-width: 150px; text-align: left;">
                                <label class="healthedia-form-label" style="display:block; margin-bottom:4px;">Page Display Title</label>
                                <input type="text" name="new_title" class="healthedia-input-field" placeholder="e.g. Verified Certs" required>
                            </div>
                            <div style="flex: 3; min-width: 180px; text-align: left;">
                                <label class="healthedia-form-label" style="display:block; margin-bottom:4px;">Page Target URL</label>
                                <input type="text" name="new_url" class="healthedia-input-field" placeholder="e.g. /verification/ or http://..." required value="/">
                            </div>
                            <button type="submit" class="healthedia-btn-save" style="padding: 10px 24px;">ADD LINK</button>
                        </form>
                    </div>

                </div>

            </main>

        </div>
    </div>

    <script>
        // Ensure standard toggle selected for select dropdown helper
        function selected(val1, val2) {
            return val1 === val2 ? 'selected' : '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar hamburger click handler on mobile
            const toggleBtn = document.getElementById('toggle-sidebar-btn');
            const sidebar = document.getElementById('dashboard-sidebar');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                });

                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 991 && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                        sidebar.classList.remove('open');
                    }
                });
            }

            // Client-side Dashboard Tab transitions
            const sidebarButtons = document.querySelectorAll('.healthedia-sidebar-item[data-section]');
            const sections = document.querySelectorAll('.healthedia-dashboard-section-view');
            const headerTitleLabel = document.getElementById('dashboard-title-label');

            sidebarButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Update active sidebar item
                    sidebarButtons.forEach(item => item.classList.remove('active'));
                    this.classList.add('active');

                    // Show selected section view
                    const targetSecId = this.getAttribute('data-section');
                    sections.forEach(sec => {
                        sec.classList.remove('active');
                        if (sec.id === 'sec-' + targetSecId) {
                            sec.classList.add('active');
                        }
                    });

                    // Update Topbar Title text
                    if (headerTitleLabel) {
                        headerTitleLabel.textContent = this.textContent.trim() + ' Section';
                    }

                    // On Mobile, close the sidebar after selection
                    if (window.innerWidth <= 991 && sidebar) {
                        sidebar.classList.remove('open');
                    }

                    // Store active tab section in local storage to keep state across forms postback redirection
                    localStorage.setItem('healthedia_dashboard_active_tab', targetSecId);
                });
            });

            // Restore active tab section state after form submissions postbacks
            const restoredTab = localStorage.getItem('healthedia_dashboard_active_tab');
            if (restoredTab) {
                const targetBtn = document.querySelector(`.healthedia-sidebar-item[data-section="${restoredTab}"]`);
                if (targetBtn) {
                    targetBtn.click();
                }
            }
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
