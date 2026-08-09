<?php
/**
 * Global Header and Footer Layout and Custom Injector
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle Profile Update POST action on 'init' hook to prevent redirects during active output buffering.
 */
function healthedia_handle_profile_update() {
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        if ( isset( $_POST['healthedia_action'] ) && $_POST['healthedia_action'] === 'update_profile' ) {
            if ( isset( $_POST['healthedia_auth_nonce'] ) && wp_verify_nonce( $_POST['healthedia_auth_nonce'], 'healthedia_auth_action' ) ) {
                $current_user = wp_get_current_user();
                if ( $current_user->ID > 0 ) {
                    $first_name = sanitize_text_field( $_POST['first_name'] );
                    $last_name = sanitize_text_field( $_POST['last_name'] );
                    $new_name = $first_name . ' ' . $last_name;
                    $new_email = sanitize_email( $_POST['user_email'] );

                    // Update main user details
                    wp_update_user([
                        'ID' => $current_user->ID,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'display_name' => $new_name,
                        'user_email' => $new_email,
                    ]);

                    // Save custom metadata to dedicated user meta database
                    update_user_meta( $current_user->ID, 'healthedia_phone', sanitize_text_field( $_POST['phone'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_workplace', sanitize_text_field( $_POST['workplace'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_degree', sanitize_text_field( $_POST['degree'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_title', sanitize_text_field( $_POST['title'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_nationality', sanitize_text_field( $_POST['nationality'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_country', sanitize_text_field( $_POST['country'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_gender', sanitize_text_field( $_POST['gender'] ) );
                    update_user_meta( $current_user->ID, 'healthedia_dob', sanitize_text_field( $_POST['dob'] ) );

                    // Handle Profile picture File Upload
                    if ( isset( $_FILES['profile_pic'] ) && ! empty( $_FILES['profile_pic']['name'] ) ) {
                        $profile_pic_url = '/uploads/' . sanitize_file_name( $_FILES['profile_pic']['name'] );
                        update_user_meta( $current_user->ID, 'healthedia_profile_pic', $profile_pic_url );
                    }

                    // Force redirect to refresh state!
                    wp_safe_redirect( $_SERVER['REQUEST_URI'] );
                    exit;
                }
            }
        }
    }
}
add_action( 'init', 'healthedia_handle_profile_update' );
add_action( 'template_redirect', 'healthedia_handle_profile_update', 0 ); // Priority 0 to run before output buffering starts!

/**
 * Output the custom Healthedia global header.
 */
function healthedia_get_header() {
    $current_url = $_SERVER['REQUEST_URI'];
    $is_search = ( strpos( $current_url, 'healthedia-search' ) !== false || $current_url === '/' || $current_url === '/index.php' || is_front_page() );
    $is_auth = ( strpos( $current_url, 'healthedia-auth' ) !== false );
    $is_dashboard = ( strpos( $current_url, 'healthedia-dashboard' ) !== false );

    $home_url = esc_url( home_url( '/' ) );
    $auth_url = esc_url( home_url( '/login/' ) );
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
    $modal_html = '';
    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : 'Researcher';
        $first_name = $current_user->first_name;
        if ( empty( $first_name ) ) {
            $first_name = get_user_meta( $current_user->ID, 'first_name', true );
        }
        $last_name = $current_user->last_name;
        if ( empty( $last_name ) ) {
            $last_name = get_user_meta( $current_user->ID, 'last_name', true );
        }

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
                <button class="healthedia-dropdown-item" id="header-edit-account-btn" style="background:none; border:none; width:100%; cursor:pointer; font-family:inherit;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4z"></path></svg>
                    Edit Account Info
                </button>
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

        // Edit Account Info Modal (Multi-step structured step-by-step wizard)
        // Uses dynamically generated security nonces for absolute real-world standard safety
        $modal_html = '
        <div class="healthedia-modal-overlay" id="healthedia-edit-account-modal">
            <div class="healthedia-modal-card">
                <div class="healthedia-modal-header">
                    <h3 class="healthedia-modal-title">Edit Account Information</h3>
                    <button class="healthedia-modal-close" id="healthedia-modal-close-btn" type="button">&times;</button>
                </div>

                <!-- Wizard Step Indicators (4 distinct steps) -->
                <div class="healthedia-wizard-steps-indicator" style="margin-bottom: 24px;">
                    <div class="healthedia-wizard-indicator-dot active" id="modal-dot-1">1</div>
                    <div class="healthedia-wizard-indicator-line" id="modal-line-1"></div>
                    <div class="healthedia-wizard-indicator-dot" id="modal-dot-2">2</div>
                    <div class="healthedia-wizard-indicator-line" id="modal-line-2"></div>
                    <div class="healthedia-wizard-indicator-dot" id="modal-dot-3">3</div>
                    <div class="healthedia-wizard-indicator-line" id="modal-line-3"></div>
                    <div class="healthedia-wizard-indicator-dot" id="modal-dot-4">4</div>
                </div>

                <form id="healthedia-edit-account-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="healthedia_auth_nonce" value="' . esc_attr( wp_create_nonce( 'healthedia_auth_action' ) ) . '">
                    <input type="hidden" name="healthedia_action" value="update_profile">

                    <!-- STEP 1: Personal Profile Info -->
                    <div class="healthedia-modal-fieldset active" id="modal-fieldset-1">
                        <div class="healthedia-modal-form-grid">
                            <div class="healthedia-form-row" style="display: flex !important; flex-direction: row !important; gap: 12px !important;">
                                <div style="flex:1;">
                                    <input type="text" name="first_name" class="healthedia-input-field" value="' . esc_attr( $first_name ) . '" required id="edit-first-name" placeholder="First Name">
                                </div>
                                <div style="flex:1;">
                                    <input type="text" name="last_name" class="healthedia-input-field" value="' . esc_attr( $last_name ) . '" required id="edit-last-name" placeholder="Last Name">
                                </div>
                            </div>
                            <div class="healthedia-form-row" style="display: flex !important; flex-direction: row !important; gap: 12px !important;">
                                <div style="flex:1;">
                                    <input type="text" name="user_nicename" class="healthedia-input-field" value="' . esc_attr( isset($current_user->user_login) ? $current_user->user_login : "" ) . '" required id="edit-user-nicename" placeholder="Username">
                                </div>
                                <div style="flex:1;">
                                    <input type="email" name="user_email" class="healthedia-input-field" value="' . esc_attr( isset($current_user->user_email) ? $current_user->user_email : "" ) . '" required id="edit-user-email" placeholder="Email Address">
                                </div>
                            </div>
                            <div class="healthedia-form-row" style="display: flex !important; flex-direction: row !important; gap: 12px !important;">
                                <div style="flex:1;">
                                    <select name="gender" class="healthedia-select-input" style="width:100%;" id="edit-gender">
                                        <option value="" disabled>Select Gender</option>
                                        <option value="male" ' . selected( get_user_meta($current_user->ID, "healthedia_gender", true), "male", false ) . '>Male</option>
                                        <option value="female" ' . selected( get_user_meta($current_user->ID, "healthedia_gender", true), "female", false ) . '>Female</option>
                                        <option value="other" ' . selected( get_user_meta($current_user->ID, "healthedia_gender", true), "other", false ) . '>Other</option>
                                    </select>
                                </div>
                                <div style="flex:1;">
                                    <input type="date" name="dob" class="healthedia-input-field" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_dob", true ) ) . '" id="edit-dob" placeholder="Date of Birth">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="healthedia-auth-submit" onclick="nextModalStep(2)" style="padding:14px 0; margin-top:20px;" id="modal-next-1">CONTINUE TO PHOTO</button>
                    </div>

                    <!-- STEP 2: Dedicated Profile Picture Upload Step with Guidance Note -->
                    <div class="healthedia-modal-fieldset" id="modal-fieldset-2" style="display:none;">
                        <div class="healthedia-modal-form-grid">
                            <div class="healthedia-form-row">
                                <input type="file" name="profile_pic" class="healthedia-input-field" accept="image/*" id="edit-profile-pic" style="padding-top: 10px;">
                                <p class="healthedia-guidance-note" style="font-size: 11px; color: #666666; margin-top: 8px; line-height: 1.4; font-style: italic; font-weight: 500;">
                                    Guidance Note: We recommend uploading a professional photo with a white background for official institutional indexing.
                                </p>
                            </div>
                        </div>
                        <div class="healthedia-wizard-actions" style="margin-top:20px;">
                            <button type="button" class="healthedia-auth-btn-secondary" onclick="prevModalStep(1)" style="padding:14px 0;">BACK</button>
                            <button type="button" class="healthedia-auth-submit" onclick="nextModalStep(3)" style="padding:14px 0;" id="modal-next-2">CONTINUE TO CONTACT</button>
                        </div>
                    </div>

                    <!-- STEP 3: Contact Information -->
                    <div class="healthedia-modal-fieldset" id="modal-fieldset-3" style="display:none;">
                        <div class="healthedia-modal-form-grid">
                            <div class="healthedia-form-row">
                                <input type="text" name="phone" class="healthedia-input-field" placeholder="Phone Number (e.g. +44 1234 567890)" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_phone", true ) ) . '" id="edit-phone">
                            </div>
                        </div>
                        <div class="healthedia-wizard-actions" style="margin-top:20px;">
                            <button type="button" class="healthedia-auth-btn-secondary" onclick="prevModalStep(2)" style="padding:14px 0;">BACK</button>
                            <button type="button" class="healthedia-auth-submit" onclick="nextModalStep(4)" style="padding:14px 0;" id="modal-next-3">CONTINUE TO PROFESSIONAL</button>
                        </div>
                    </div>

                    <!-- STEP 4: Professional Credentials Details (arranged in two-column layout) -->
                    <div class="healthedia-modal-fieldset" id="modal-fieldset-4" style="display:none;">
                        <div class="healthedia-modal-form-grid">
                            <div class="healthedia-form-row" style="display: flex !important; flex-direction: row !important; gap: 12px !important;">
                                <div style="flex:1;">
                                    <input type="text" name="workplace" class="healthedia-input-field" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_workplace", true ) ) . '" placeholder="Workplace / Institution" required id="edit-workplace">
                                </div>
                                <div style="flex:1;">
                                    <input type="text" name="degree" class="healthedia-input-field" placeholder="Academic Degree (e.g. Ph.D.)" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_degree", true ) ) . '" id="edit-degree">
                                </div>
                            </div>
                            <div class="healthedia-form-row" style="display: flex !important; flex-direction: row !important; gap: 12px !important;">
                                <div style="flex:1;">
                                    <input type="text" name="title" class="healthedia-input-field" placeholder="Professional Title (e.g. Professor)" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_title", true ) ) . '" id="edit-title">
                                </div>
                                <div style="flex:1;">
                                    <input type="text" name="nationality" class="healthedia-input-field" placeholder="Nationality" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_nationality", true ) ) . '" id="edit-nationality">
                                </div>
                            </div>
                            <div class="healthedia-form-row">
                                <input type="text" name="country" class="healthedia-input-field" placeholder="Country of Residence" value="' . esc_attr( get_user_meta( $current_user->ID, "healthedia_country", true ) ) . '" id="edit-country">
                            </div>
                        </div>
                        <div class="healthedia-wizard-actions" style="margin-top:20px;">
                            <button type="button" class="healthedia-auth-btn-secondary" onclick="prevModalStep(3)" style="padding:14px 0;">BACK</button>
                            <button type="submit" class="healthedia-auth-submit" style="padding:14px 0;" id="edit-profile-save-btn">SAVE UPDATES</button>
                        </div>
                    </div>
                </form>
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
            </div>

            <!-- Right-aligned Authentication & Mobile Menu Wrapper -->
            <div class="healthedia-header-right-wrapper">
                <!-- Right-aligned Authentication Area -->
                <div class="healthedia-auth-btn-wrapper">
                    ' . $auth_area . '
                </div>

                <!-- Mobile Dropdown Navigation Trigger - positioned on far right of Login button -->
                <div class="healthedia-mobile-nav-trigger-container" id="healthedia-mobile-trigger-container">
                    <button class="healthedia-mobile-menu-btn" id="mobile-menu-toggle-btn" aria-label="Menu">
                        <svg class="healthedia-mobile-hamburger-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div class="healthedia-mobile-dropdown-menu" id="mobile-dropdown-menu-list">
                        ' . $nav_html . '
                    </div>
                </div>
            </div>
        </div>
    </header>

    ' . $modal_html . '

    <style>
        :root {
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

        /* Header Right Wrapper Style */
        .healthedia-header-right-wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
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
                background: #ffffff !important; /* Solid white circular button */
                border: 1px solid var(--healthedia-border);
                border-radius: 50% !important; /* Perfect circle */
                width: 38px !important;
                height: 38px !important;
                padding: 0 !important;
                cursor: pointer;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                color: #000000 !important; /* Solid black hamburger lines by default */
                transition: color 0.2s ease, border-color 0.2s ease;
            }
            .healthedia-mobile-menu-btn:hover {
                border-color: var(--healthedia-black);
            }
            /* When the button is pressed or activated, the icon changes to light gray */
            .healthedia-mobile-nav-trigger-container.open .healthedia-mobile-menu-btn {
                color: #bbbbbb !important; /* Light gray hamburger icon state */
            }
            .healthedia-mobile-dropdown-menu {
                display: none;
                position: absolute;
                right: 0 !important; /* Align dropdown with right edge of trigger */
                left: auto !important;
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
                padding: 0 !important;
                gap: 0 !important;
                border: none !important;
                background: none !important;
                width: 32px !important;
                height: 32px !important;
                border-radius: 50% !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .healthedia-header-avatar {
                width: 32px !important;
                height: 32px !important;
                font-size: 11px !important;
                margin: 0 !important;
            }
            .healthedia-header-user-name {
                display: none !important;
            }
            .healthedia-dropdown-chevron {
                display: none !important;
            }
            .healthedia-dropdown-menu {
                width: 180px;
                top: calc(100% + 6px);
            }
        }

        /* MODAL DIALOG PRESET STYLING */
        .healthedia-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1000000;
            align-items: center;
            justify-content: center;
            animation: modalOverlayFade 0.25s ease;
        }
        .healthedia-modal-overlay.open {
            display: flex !important;
        }
        @keyframes modalOverlayFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .healthedia-modal-card {
            background-color: #ffffff;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            box-sizing: border-box;
            position: relative;
            animation: modalSlideUp 0.25s ease;
        }
        @keyframes modalSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .healthedia-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .healthedia-modal-title {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            margin: 0;
            color: #000000;
        }
        .healthedia-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #888888;
            line-height: 1;
            padding: 0;
        }
        .healthedia-modal-close:hover {
            color: #000000;
        }
        .healthedia-modal-form-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Complete Premium Styles for Edit Account Modal & Wizard Steps */
        .healthedia-modal-card {
            background-color: #ffffff !important;
            background: #ffffff !important;
            opacity: 1 !important;
            border: 1px solid var(--healthedia-border);
            z-index: 1000002 !important;
        }

        .healthedia-modal-fieldset {
            display: none;
            width: 100%;
        }

        .healthedia-modal-fieldset.active {
            display: block !important;
        }

        /* Step Indicators in Modal */
        .healthedia-modal-card .healthedia-wizard-steps-indicator {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            margin-bottom: 25px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            flex-direction: row !important; /* Force horizontal alignment! */
        }

        .healthedia-modal-card .healthedia-wizard-indicator-dot {
            width: 30px !important;
            height: 30px !important;
            border-radius: 50% !important;
            background-color: #f0f0f0 !important;
            color: #999999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
        }

        .healthedia-modal-card .healthedia-wizard-indicator-dot.active {
            background-color: #000000 !important;
            color: #ffffff !important;
        }

        .healthedia-modal-card .healthedia-wizard-indicator-dot.completed {
            background-color: #e6f6ec !important;
            color: #1b8a4f !important;
        }

        .healthedia-modal-card .healthedia-wizard-indicator-line {
            height: 2px !important;
            width: 25px !important;
            background-color: #e5e5e5 !important;
            flex-grow: 1 !important;
            max-width: 40px !important;
        }

        .healthedia-modal-card .healthedia-wizard-indicator-line.active {
            background-color: #000000 !important;
        }

        /* Modal Forms & Input Fields */
        .healthedia-form-row {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
            text-align: left;
            box-sizing: border-box;
        }

        .healthedia-form-label {
            font-size: 11px;
            font-weight: 700;
            color: #111111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .healthedia-input-field {
            width: 100% !important;
            background-color: #ffffff !important;
            border: 1px solid #dddddd !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            box-sizing: border-box !important;
            font-size: 14px !important;
            font-family: inherit !important;
            color: #333333 !important;
            outline: none !important;
            height: 46px !important;
            transition: border-color 0.2s ease !important;
        }

        .healthedia-input-field:focus {
            border-color: #000000 !important;
        }

        .healthedia-select-input {
            width: 100% !important;
            background-color: #ffffff !important;
            border: 1px solid #dddddd !important;
            border-radius: 12px !important;
            padding: 0 16px !important;
            box-sizing: border-box !important;
            font-size: 14px !important;
            font-family: inherit !important;
            color: #333333 !important;
            outline: none !important;
            height: 46px !important;
            line-height: 46px !important;
            transition: border-color 0.2s ease !important;
            appearance: none !important;
            background-image: url(\"data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23999%22%20stroke-width%3D%222%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E\") !important;
            background-repeat: no-repeat !important;
            background-position: right 16px center !important;
        }

        .healthedia-select-input:focus {
            border-color: #000000 !important;
        }

        /* Modal Actions & Buttons */
        .healthedia-modal-card .healthedia-auth-submit {
            width: 100% !important;
            background-color: #000000 !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 14px 0 !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            cursor: pointer !important;
            transition: opacity 0.2s ease, transform 0.2s ease !important;
            text-align: center !important;
            display: block !important;
        }

        .healthedia-modal-card .healthedia-auth-submit:hover {
            opacity: 0.9 !important;
            transform: translateY(-1px) !important;
        }

        .healthedia-modal-card .healthedia-wizard-actions {
            display: flex !important;
            gap: 12px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .healthedia-modal-card .healthedia-auth-btn-secondary {
            flex: 1 !important;
            background-color: #f4f4f4 !important;
            color: #333333 !important;
            border: 1px solid #e5e5e5 !important;
            border-radius: 12px !important;
            padding: 14px 0 !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            cursor: pointer !important;
            transition: background-color 0.2s ease !important;
            text-align: center !important;
            display: block !important;
        }

        .healthedia-modal-card .healthedia-auth-btn-secondary:hover {
            background-color: #e5e5e5 !important;
        }
    </style>

    <script id="healthedia-header-script">
        // Helper multi-step navigation for modal wizard
        function nextModalStep(step) {
            // Hide all fieldsets by setting style.display to none and removing active class
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById("modal-fieldset-" + i);
                if (el) {
                    el.style.display = "none";
                    el.classList.remove("active");
                }
            }

            // Show current step fieldset
            const currentEl = document.getElementById("modal-fieldset-" + step);
            if (currentEl) {
                currentEl.style.display = "block";
                currentEl.classList.add("active");
            }

            // Update indicators
            document.getElementById("modal-dot-1").className = "healthedia-wizard-indicator-dot " + (step > 1 ? "completed" : "active");
            document.getElementById("modal-line-1").className = "healthedia-wizard-indicator-line " + (step > 1 ? "active" : "");

            document.getElementById("modal-dot-2").className = "healthedia-wizard-indicator-dot " + (step === 2 ? "active" : (step > 2 ? "completed" : ""));
            document.getElementById("modal-line-2").className = "healthedia-wizard-indicator-line " + (step > 2 ? "active" : "");

            document.getElementById("modal-dot-3").className = "healthedia-wizard-indicator-dot " + (step === 3 ? "active" : (step > 3 ? "completed" : ""));
            document.getElementById("modal-line-3").className = "healthedia-wizard-indicator-line " + (step > 3 ? "active" : "");

            document.getElementById("modal-dot-4").className = "healthedia-wizard-indicator-dot " + (step === 4 ? "active" : "");
        }

        function prevModalStep(step) {
            nextModalStep(step);
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Deskop user profile dropdown
            const dropdownContainer = document.getElementById("healthedia-header-dropdown");
            const dropdownBtn = document.getElementById("header-user-dropdown-btn");

            if (dropdownBtn && dropdownContainer) {
                dropdownBtn.addEventListener("click", function(e) {
                    e.stopPropagation();
                    dropdownContainer.classList.toggle("open");
                });

                document.addEventListener("click", function(e) {
                    if (!dropdownContainer.contains(e.target)) {
                        dropdownContainer.classList.remove("open");
                    }
                });
            }

            // Mobile menu navigation dropdown
            const mobileTrigger = document.getElementById("healthedia-mobile-trigger-container");
            const mobileBtn = document.getElementById("mobile-menu-toggle-btn");

            if (mobileBtn && mobileTrigger) {
                mobileBtn.addEventListener("click", function(e) {
                    e.stopPropagation();
                    mobileTrigger.classList.toggle("open");
                });

                document.addEventListener("click", function(e) {
                    if (!mobileTrigger.contains(e.target)) {
                        mobileTrigger.classList.remove("open");
                    }
                });
            }

            // Edit Account Info Modal Toggle
            const modalOverlay = document.getElementById("healthedia-edit-account-modal");
            const openModalBtn = document.getElementById("header-edit-account-btn");
            const closeModalBtn = document.getElementById("healthedia-modal-close-btn");

            if (openModalBtn && modalOverlay) {
                openModalBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    modalOverlay.classList.add("open");
                    nextModalStep(1); // Reset wizard back to first step
                    if (dropdownContainer) {
                        dropdownContainer.classList.remove("open");
                    }
                });
            }

            if (closeModalBtn && modalOverlay) {
                closeModalBtn.addEventListener("click", function() {
                    modalOverlay.classList.remove("open");
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
                © 2026 Healthedia. All Rights Reserved. Global Health Archive.
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
        }

        .healthedia-footer-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        header.site-header,
        #masthead,
        #colophon,
        footer.site-footer,
        .site-header,
        .site-footer,
        .ast-primary-header-bar,
        .ast-theme-transparent-header,
        .ast-footer-builder-area,
        .theme-default-header,
        .theme-default-footer {
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
