<?php
/**
 * Template Name: Healthedia SaaS Dashboard
 */

// Secure backend access control integration
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/healthedia-auth/' ) );
    exit;
}

$current_user = wp_get_current_user();
$display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : ( ! empty( $current_user->user_nicename ) ? $current_user->user_nicename : 'Researcher' );

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

// DEBUG USER INFO (Hidden comment)
echo "<!-- DEBUG: Logged In: " . (is_user_logged_in() ? 'YES' : 'NO') . " | User ID: " . (isset($_SESSION['current_user_id']) ? $_SESSION['current_user_id'] : 'NONE') . " | Display Name: " . $display_name . " -->\n";
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
                <a href="#" class="healthedia-sidebar-item active">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>
                    Overview
                </a>
                <a href="#" class="healthedia-sidebar-item">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Analytics
                </a>
                <a href="#" class="healthedia-sidebar-item">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Archive Records
                </a>
                <a href="#" class="healthedia-sidebar-item">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Researchers Profile
                </a>
                <a href="#" class="healthedia-sidebar-item">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Settings
                </a>
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
                    <div class="healthedia-topbar-title">Researchers Dashboard</div>
                </div>

                <div class="healthedia-topbar-user">
                    <div class="healthedia-user-avatar"><?php echo esc_html( $initials ); ?></div>
                    <span class="healthedia-user-name"><?php echo esc_html( $display_name ); ?></span>
                </div>
            </header>

            <!-- Dashboard Contents -->
            <main class="healthedia-dashboard-viewport">

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
                                <tr>
                                    <td>Impact of Sleep Optimization and Chronobiology on Sports Performance Metrics</td>
                                    <td>Sleep & Performance Studies</td>
                                    <td>Jun 30, 2026</td>
                                    <td><span class="healthedia-badge success">Approved</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggle-sidebar-btn');
            const sidebar = document.getElementById('dashboard-sidebar');

            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 991 && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                    sidebar.classList.remove('open');
                }
            });
        });
    </script>
    <?php wp_footer(); ?>
</body>
</html>
