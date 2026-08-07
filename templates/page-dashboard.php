<?php
/**
 * SaaS Dashboard Template for Healthedia.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Security checks
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/healthedia-auth/' ) );
	exit;
}

$current_user = wp_get_current_user();
$is_admin = current_user_can( 'manage_options' );

// Get options
$logo_text = get_option( 'healthedia_logo_text', 'Healthedia' );
$sub_text  = get_option( 'healthedia_sub_text', 'GLOBAL HEALTH ARCHIVE' );
$copyright_text = get_option( 'healthedia_copyright_text', '© 2026 Healthedia. All Rights Reserved. Permanent Open-Access Repository.' );
$clinic_status = get_option( 'healthedia_clinic_status', 'open' );
?>
<style>
	/* Dashboard Specific Styles (Highly Polished & SaaS-centric) */
	.healthedia-dash-wrapper {
		display: flex;
		min-height: calc(100vh - 160px); /* Adjust for header & footer */
		background-color: #f8fafc;
	}

	/* Fixed/Sleek Left Sidebar Navigation */
	.healthedia-sidebar {
		width: 260px;
		background: #ffffff;
		border-right: 1px solid #e2e8f0;
		padding: 24px 16px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		flex-shrink: 0;
	}

	.healthedia-sidebar-menu {
		display: flex;
		flex-direction: column;
		gap: 6px;
	}

	.healthedia-sidebar-link {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 12px 16px;
		font-family: var(--healthedia-font-sans);
		font-size: 14px;
		font-weight: 500;
		color: #64748b;
		text-decoration: none !important;
		border-radius: 8px;
		transition: all 0.2s ease;
	}

	.healthedia-sidebar-link:hover {
		color: #000000;
		background-color: #f1f5f9;
	}

	.healthedia-sidebar-link.active {
		background-color: #000000;
		color: #ffffff;
		font-weight: 600;
	}

	.healthedia-sidebar-icon {
		width: 18px;
		height: 18px;
	}

	.healthedia-sidebar-footer {
		padding-top: 16px;
		border-top: 1px solid #f1f5f9;
	}

	/* Content Area */
	.healthedia-dash-content {
		flex: 1;
		display: flex;
		flex-direction: column;
		min-width: 0; /* Prevents flex items from overflowing */
	}

	/* Slim Top Nav Bar */
	.healthedia-topbar {
		height: 70px;
		background: #ffffff;
		border-bottom: 1px solid #e2e8f0;
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 0 32px;
	}

	.healthedia-topbar-title {
		font-family: var(--healthedia-font-display);
		font-size: 18px;
		font-weight: 700;
		color: #0f172a;
	}

	.healthedia-topbar-right {
		display: flex;
		align-items: center;
		gap: 16px;
	}

	.healthedia-user-profile-badge {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.healthedia-avatar-circle {
		width: 36px;
		height: 36px;
		border-radius: 50%;
		background-color: #e2e8f0;
		display: flex;
		align-items: center;
		justify-content: center;
		font-weight: 700;
		font-size: 14px;
		color: #0f172a;
		border: 1px solid #cbd5e1;
	}

	.healthedia-user-name {
		font-size: 14px;
		font-weight: 600;
		color: #0f172a;
	}

	.healthedia-user-role {
		font-size: 11px;
		color: #64748b;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	/* Tab Content Panes */
	.healthedia-dash-main {
		padding: 32px;
		flex: 1;
		overflow-y: auto;
	}

	.healthedia-dash-tab {
		display: none;
	}

	.healthedia-dash-tab.active {
		display: block;
		animation: fadeIn 0.3s ease-in-out;
	}

	/* Modern Grid and Widgets */
	.healthedia-stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 24px;
		margin-bottom: 32px;
	}

	.healthedia-stat-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 24px;
		box-shadow: var(--healthedia-shadow-sm);
		transition: transform 0.2s, box-shadow 0.2s;
	}

	.healthedia-stat-card:hover {
		transform: translateY(-2px);
		box-shadow: var(--healthedia-shadow-md);
	}

	.healthedia-stat-label {
		font-size: 13px;
		font-weight: 500;
		color: #64748b;
		margin-bottom: 6px;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.healthedia-stat-value {
		font-family: var(--healthedia-font-display);
		font-size: 28px;
		font-weight: 800;
		color: #0f172a;
		line-height: 1.1;
	}

	.healthedia-stat-trend {
		display: inline-flex;
		align-items: center;
		font-size: 12px;
		font-weight: 600;
		margin-top: 8px;
		padding: 2px 8px;
		border-radius: 9999px;
	}

	.healthedia-trend-up {
		background-color: #ecfdf5;
		color: #059669;
	}

	.healthedia-trend-down {
		background-color: #fef2f2;
		color: #dc2626;
	}

	/* SaaS Visual Card with Embedded Chart */
	.healthedia-chart-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 16px;
		padding: 28px;
		box-shadow: var(--healthedia-shadow-sm);
		margin-bottom: 32px;
	}

	.healthedia-card-title {
		font-family: var(--healthedia-font-display);
		font-size: 16px;
		font-weight: 700;
		color: #0f172a;
		margin: 0 0 20px 0;
	}

	.healthedia-chart-container {
		height: 200px;
		display: flex;
		align-items: flex-end;
		justify-content: space-between;
		padding-top: 10px;
		border-bottom: 1px solid #e2e8f0;
		gap: 16px;
	}

	.healthedia-chart-bar-wrapper {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 8px;
	}

	.healthedia-chart-bar {
		width: 100%;
		max-width: 45px;
		background-color: #000000;
		border-radius: 6px 6px 0 0;
		transition: height 0.5s ease-out;
	}

	.healthedia-chart-label {
		font-size: 11px;
		color: #64748b;
		font-weight: 500;
	}

	/* Modern Beautiful Table */
	.healthedia-table-wrapper {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 16px;
		box-shadow: var(--healthedia-shadow-sm);
		overflow: hidden;
	}

	.healthedia-table {
		width: 100%;
		border-collapse: collapse;
		text-align: left;
	}

	.healthedia-table th {
		background-color: #f8fafc;
		padding: 16px 24px;
		font-size: 12px;
		font-weight: 600;
		color: #475569;
		border-bottom: 1px solid #e2e8f0;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.healthedia-table td {
		padding: 16px 24px;
		font-size: 14px;
		color: #0f172a;
		border-bottom: 1px solid #f1f5f9;
	}

	.healthedia-table tr:last-child td {
		border-bottom: none;
	}

	.healthedia-badge {
		display: inline-flex;
		align-items: center;
		font-size: 11px;
		font-weight: 600;
		padding: 4px 10px;
		border-radius: 9999px;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.healthedia-badge-success {
		background-color: #ecfdf5;
		color: #047857;
	}

	.healthedia-badge-warning {
		background-color: #fffbeb;
		color: #b45309;
	}

	.healthedia-badge-info {
		background-color: #f0f9ff;
		color: #0369a1;
	}

	/* Form Elements */
	.healthedia-form {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 16px;
		padding: 32px;
		box-shadow: var(--healthedia-shadow-sm);
		max-width: 600px;
	}

	.healthedia-form-row {
		display: flex;
		gap: 20px;
		margin-bottom: 20px;
	}

	.healthedia-form-row .healthedia-form-group {
		flex: 1;
	}

	.healthedia-form-group {
		margin-bottom: 20px;
		position: relative;
	}

	.healthedia-form-label {
		display: block;
		font-size: 13px;
		font-weight: 600;
		color: #334155;
		margin-bottom: 8px;
	}

	.healthedia-form-input {
		width: 100%;
		padding: 12px 16px;
		border: 1px solid #cbd5e1;
		border-radius: 8px;
		font-size: 14px;
		font-family: var(--healthedia-font-sans);
		color: #0f172a;
		transition: all 0.2s;
		outline: none;
	}

	.healthedia-form-input:focus {
		border-color: #000000;
		box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
	}

	/* Eye button for pass toggle */
	.healthedia-password-toggle {
		position: absolute;
		right: 14px;
		top: 38px;
		background: none;
		border: none;
		color: #94a3b8;
		cursor: pointer;
		padding: 0;
		outline: none;
	}

	.healthedia-password-toggle:hover {
		color: #0f172a;
	}

	.healthedia-password-toggle.healthedia-visible {
		color: #000000;
	}

	.healthedia-password-toggle svg {
		width: 18px;
		height: 18px;
	}

	.healthedia-submit-btn {
		background-color: #000000;
		color: #ffffff;
		border: none;
		padding: 12px 28px;
		font-size: 14px;
		font-weight: 600;
		border-radius: 8px;
		cursor: pointer;
		font-family: var(--healthedia-font-sans);
		transition: background-color 0.2s, transform 0.1s;
	}

	.healthedia-submit-btn:hover {
		background-color: #1e1e1e;
	}

	.healthedia-submit-btn:active {
		transform: scale(0.98);
	}

	/* Alert messages */
	.healthedia-msg {
		padding: 12px 16px;
		border-radius: 8px;
		font-size: 14px;
		font-weight: 500;
		margin-bottom: 24px;
		display: none;
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

	/* Settings specific items */
	.healthedia-toggle-wrapper {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 16px;
		background-color: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		margin-bottom: 20px;
	}

	.healthedia-toggle-text {
		display: flex;
		flex-direction: column;
	}

	.healthedia-toggle-title {
		font-size: 14px;
		font-weight: 600;
		color: #0f172a;
	}

	.healthedia-toggle-desc {
		font-size: 12px;
		color: #64748b;
		margin-top: 2px;
	}

	.healthedia-switch {
		position: relative;
		display: inline-block;
		width: 48px;
		height: 24px;
	}

	.healthedia-switch input {
		opacity: 0;
		width: 0;
		height: 0;
	}

	.healthedia-slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #cbd5e1;
		transition: .3s;
		border-radius: 24px;
	}

	.healthedia-slider:before {
		position: absolute;
		content: "";
		height: 18px;
		width: 18px;
		left: 3px;
		bottom: 3px;
		background-color: white;
		transition: .3s;
		border-radius: 50%;
	}

	input:checked + .healthedia-slider {
		background-color: #000000;
	}

	input:checked + .healthedia-slider:before {
		transform: translateX(24px);
	}

	/* Help Docs Section */
	.healthedia-docs-wrapper {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
		gap: 24px;
	}

	.healthedia-doc-card {
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 24px;
		box-shadow: var(--healthedia-shadow-sm);
	}

	.healthedia-doc-title {
		font-family: var(--healthedia-font-display);
		font-size: 15px;
		font-weight: 700;
		color: #0f172a;
		margin-bottom: 10px;
	}

	.healthedia-doc-text {
		font-size: 13px;
		color: #64748b;
		line-height: 1.5;
	}

	/* CSS Animations */
	@keyframes fadeIn {
		from { opacity: 0; transform: translateY(8px); }
		to { opacity: 1; transform: translateY(0); }
	}

	/* Responsive for Dashboard */
	@media (max-width: 1024px) {
		.healthedia-dash-wrapper {
			flex-direction: column;
		}
		.healthedia-sidebar {
			width: 100%;
			border-right: none;
			border-bottom: 1px solid #e2e8f0;
			padding: 16px 24px;
		}
		.healthedia-sidebar-menu {
			flex-direction: row;
			overflow-x: auto;
			gap: 8px;
			padding-bottom: 6px;
		}
		.healthedia-sidebar-link {
			white-space: nowrap;
			padding: 8px 12px;
		}
		.healthedia-sidebar-footer {
			display: none;
		}
		.healthedia-topbar {
			padding: 0 24px;
		}
		.healthedia-dash-main {
			padding: 24px;
		}
	}
</style>

<div class="healthedia-dash-wrapper">
	<!-- Left Sidebar -->
	<aside class="healthedia-sidebar">
		<div class="healthedia-sidebar-menu">
			<a href="#overview" class="healthedia-sidebar-link active">
				<svg class="healthedia-sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
				</svg>
				Overview / Stats
			</a>
			<a href="#profile" class="healthedia-sidebar-link">
				<svg class="healthedia-sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
				</svg>
				User Profile
			</a>
			<a href="#settings" class="healthedia-sidebar-link">
				<svg class="healthedia-sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
				</svg>
				Plugin Settings
			</a>
			<a href="#help" class="healthedia-sidebar-link">
				<svg class="healthedia-sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
				</svg>
				Help & Support
			</a>
		</div>

		<div class="healthedia-sidebar-footer">
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="healthedia-sidebar-link healthedia-external text-red">
				<svg class="healthedia-sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
				</svg>
				Sign Out
			</a>
		</div>
	</aside>

	<!-- Right Content Area -->
	<section class="healthedia-dash-content">
		<!-- Top Bar -->
		<div class="healthedia-topbar">
			<div class="healthedia-topbar-title">Healthedia Management Console</div>
			<div class="healthedia-topbar-right">
				<!-- Current User Profile Badge -->
				<div class="healthedia-user-profile-badge">
					<div class="healthedia-avatar-circle">
						<?php echo esc_html( strtoupper( substr( $current_user->user_login, 0, 2 ) ) ); ?>
					</div>
					<div class="healthedia-user-info-text">
						<div class="healthedia-user-name"><?php echo esc_html( $current_user->display_name ); ?></div>
						<div class="healthedia-user-role"><?php echo $is_admin ? 'Administrator' : 'User'; ?></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Dashboard Tab Panes -->
		<div class="healthedia-dash-main">

			<!-- TABS OVERVIEW / STATS -->
			<div id="tab-overview" class="healthedia-dash-tab active">
				<!-- Metrics Grid -->
				<div class="healthedia-stats-grid">
					<div class="healthedia-stat-card">
						<div class="healthedia-stat-label">Active Patients</div>
						<div class="healthedia-stat-value">1,248</div>
						<div class="healthedia-stat-trend healthedia-trend-up">
							+12% this month
						</div>
					</div>
					<div class="healthedia-stat-card">
						<div class="healthedia-stat-label">Appointments</div>
						<div class="healthedia-stat-value">342</div>
						<div class="healthedia-stat-trend healthedia-trend-up">
							+5% this week
						</div>
					</div>
					<div class="healthedia-stat-card">
						<div class="healthedia-stat-label">Active Prescriptions</div>
						<div class="healthedia-stat-value">89</div>
						<div class="healthedia-stat-trend healthedia-trend-down">
							-3% this month
						</div>
					</div>
					<div class="healthedia-stat-card">
						<div class="healthedia-stat-label">Annual Revenue</div>
						<div class="healthedia-stat-value">$45,210</div>
						<div class="healthedia-stat-trend healthedia-trend-up">
							+18% growth
						</div>
					</div>
				</div>

				<!-- SaaS SVG Visualizations -->
				<div class="healthedia-chart-card">
					<h3 class="healthedia-card-title">Patient Registrations (Jan-Jun 2026)</h3>
					<div class="healthedia-chart-container">
						<div class="healthedia-chart-bar-wrapper">
							<div class="healthedia-chart-bar" style="height: 60px;"></div>
							<span class="healthedia-chart-label">Jan</span>
						</div>
						<div class="healthedia-chart-bar-wrapper">
							<div class="healthedia-chart-bar" style="height: 90px;"></div>
							<span class="healthedia-chart-label">Feb</span>
						</div>
						<div class="healthedia-chart-bar-wrapper">
							<div class="healthedia-chart-bar" style="height: 140px;"></div>
							<span class="healthedia-chart-label">Mar</span>
						</div>
						<div class="healthedia-chart-bar-wrapper">
							<div class="healthedia-chart-bar" style="height: 120px;"></div>
							<span class="healthedia-chart-label">Apr</span>
						</div>
						<div class="healthedia-chart-bar-wrapper">
							<div class="healthedia-chart-bar" style="height: 165px; background-color: var(--healthedia-accent-blue);"></div>
							<span class="healthedia-chart-label">May</span>
						</div>
						<div class="healthedia-chart-bar-wrapper">
							<div class="healthedia-chart-bar" style="height: 190px; background-color: var(--healthedia-accent-teal);"></div>
							<span class="healthedia-chart-label">Jun</span>
						</div>
					</div>
				</div>

				<!-- Table listing Clinical Sessions -->
				<div class="healthedia-table-wrapper">
					<table class="healthedia-table">
						<thead>
							<tr>
								<th>Patient Name</th>
								<th>Appointment Date</th>
								<th>Department</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="font-weight: 600;">Dr. Sarah Jenkins</td>
								<td>June 12, 2026 - 10:30 AM</td>
								<td>Cardiology</td>
								<td><span class="healthedia-badge healthedia-badge-success">Confirmed</span></td>
							</tr>
							<tr>
								<td style="font-weight: 600;">Michael Corvin</td>
								<td>June 12, 2026 - 11:15 AM</td>
								<td>Sports Biomechanics</td>
								<td><span class="healthedia-badge healthedia-badge-success">Confirmed</span></td>
							</tr>
							<tr>
								<td style="font-weight: 600;">Elena Rostova</td>
								<td>June 12, 2026 - 02:00 PM</td>
								<td>Physiology Research</td>
								<td><span class="healthedia-badge healthedia-badge-warning">Pending</span></td>
							</tr>
							<tr>
								<td style="font-weight: 600;">David Beckham</td>
								<td>June 11, 2026 - 09:00 AM</td>
								<td>Kinesiology</td>
								<td><span class="healthedia-badge healthedia-badge-info">Completed</span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>


			<!-- TABS USER PROFILE -->
			<div id="tab-profile" class="healthedia-dash-tab">
				<div class="healthedia-form-container">
					<form id="healthedia-profile-form" class="healthedia-form" action="" method="post">
						<h3 class="healthedia-card-title">Manage Your Profile</h3>
						<div class="healthedia-profile-msg healthedia-msg"></div>

						<div class="healthedia-form-row">
							<div class="healthedia-form-group">
								<label class="healthedia-form-label">First Name</label>
								<input type="text" name="first_name" class="healthedia-form-input" value="<?php echo esc_attr( $current_user->first_name ); ?>" required>
							</div>
							<div class="healthedia-form-group">
								<label class="healthedia-form-label">Last Name</label>
								<input type="text" name="last_name" class="healthedia-form-input" value="<?php echo esc_attr( $current_user->last_name ); ?>" required>
							</div>
						</div>

						<div class="healthedia-form-group">
							<label class="healthedia-form-label">Email Address</label>
							<input type="email" name="user_email" class="healthedia-form-input" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
						</div>

						<div class="healthedia-form-group">
							<label class="healthedia-form-label">New Password (Leave blank to keep current)</label>
							<input type="password" name="user_pass" class="healthedia-form-input" placeholder="••••••••">
							<button type="button" class="healthedia-password-toggle">
								<svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
								</svg>
							</button>
						</div>

						<?php wp_nonce_field( 'healthedia_profile_nonce', 'healthedia_profile_security' ); ?>
						<input type="hidden" name="action" value="healthedia_update_profile">
						<button type="submit" class="healthedia-submit-btn">Save Changes</button>
					</form>
				</div>
			</div>


			<!-- TABS SETTINGS (ADMIN ONLY) -->
			<div id="tab-settings" class="healthedia-dash-tab">
				<?php if ( $is_admin ) : ?>
					<form id="healthedia-settings-form" class="healthedia-form" action="" method="post">
						<h3 class="healthedia-card-title">Plugin Global Configuration</h3>
						<div class="healthedia-settings-msg healthedia-msg"></div>

						<div class="healthedia-form-group">
							<label class="healthedia-form-label">Header Logo Brand Text</label>
							<input type="text" name="healthedia_logo_text" class="healthedia-form-input" value="<?php echo esc_attr( $logo_text ); ?>" required>
						</div>

						<div class="healthedia-form-group">
							<label class="healthedia-form-label">Header Tagline Sub-text</label>
							<input type="text" name="healthedia_sub_text" class="healthedia-form-input" value="<?php echo esc_attr( $sub_text ); ?>" required>
						</div>

						<div class="healthedia-form-group">
							<label class="healthedia-form-label">Footer Copyright Brand Text</label>
							<input type="text" name="healthedia_copyright_text" class="healthedia-form-input" value="<?php echo esc_attr( $copyright_text ); ?>" required>
						</div>

						<!-- Clinic Open/Closed Toggle -->
						<div class="healthedia-toggle-wrapper">
							<div class="healthedia-toggle-text">
								<span class="healthedia-toggle-title">Emergency Clinic Status</span>
								<span class="healthedia-toggle-desc">Toggle the status of clinical departments to Open or Closed.</span>
							</div>
							<label class="healthedia-switch">
								<input type="checkbox" name="healthedia_clinic_status" value="open" <?php checked( $clinic_status, 'open' ); ?>>
								<span class="healthedia-slider"></span>
							</label>
						</div>

						<?php wp_nonce_field( 'healthedia_settings_nonce', 'healthedia_settings_security' ); ?>
						<input type="hidden" name="action" value="healthedia_save_settings">
						<button type="submit" class="healthedia-submit-btn">Save Settings</button>
					</form>
				<?php else : ?>
					<div class="healthedia-msg healthedia-msg-error" style="display: block; max-width: 600px;">
						Access Denied: Administrator permissions are required to modify global plugin settings.
					</div>
				<?php endif; ?>
			</div>


			<!-- TABS HELP & DOCUMENTATION -->
			<div id="tab-help" class="healthedia-dash-tab">
				<h3 class="healthedia-card-title" style="margin-bottom: 24px;">Platform Documentation & Integration Guides</h3>
				<div class="healthedia-docs-wrapper">
					<div class="healthedia-doc-card">
						<h4 class="healthedia-doc-title">Virtual Page Routing</h4>
						<p class="healthedia-doc-text">
							Healthedia generates fully dynamic frontend views. The routes <strong>/healthedia-dashboard/</strong> and <strong>/healthedia-auth/</strong> operate securely outside standard theme layout files to provide high-performance, SaaS-style rendering.
						</p>
					</div>
					<div class="healthedia-doc-card">
						<h4 class="healthedia-doc-title">Global Header & Footer Injection</h4>
						<p class="healthedia-doc-text">
							The plugin enqueues a standard CSS overriding layer that automatically disables theme headers/footers (specifically for Astra and standard themes) and replaces them with a modern, high-conversion visual block instantly on installation.
						</p>
					</div>
					<div class="healthedia-doc-card">
						<h4 class="healthedia-doc-title">Permissions & Extensibility</h4>
						<p class="healthedia-doc-text">
							Our database structure respects standard WordPress authentication cookies. Security nonces protect the updating and creation endpoints against CSRF and injection attacks.
						</p>
					</div>
				</div>
			</div>

		</div>
	</section>
</div>

<!-- Add dashboard form submission handling via AJAX -->
<script>
	jQuery(document).ready(function($) {
		// User Profile update AJAX
		$('#healthedia-profile-form').on('submit', function(e) {
			e.preventDefault();
			const form = $(this);
			const msgBox = form.find('.healthedia-profile-msg');
			const submitBtn = form.find('.healthedia-submit-btn');

			msgBox.removeClass('healthedia-msg-success healthedia-msg-error').hide().text('');
			submitBtn.prop('disabled', true).text('Saving...');

			$.ajax({
				url: healthedia_vars.ajax_url,
				type: 'POST',
				data: form.serialize(),
				success: function(response) {
					submitBtn.prop('disabled', false).text('Save Changes');
					if (response.success) {
						msgBox.addClass('healthedia-msg-success').text(response.data.message).fadeIn();
					} else {
						msgBox.addClass('healthedia-msg-error').text(response.data.message).fadeIn();
					}
				},
				error: function() {
					submitBtn.prop('disabled', false).text('Save Changes');
					msgBox.addClass('healthedia-msg-error').text('An error occurred. Please try again.').fadeIn();
				}
			});
		});

		// Plugin Settings update AJAX
		$('#healthedia-settings-form').on('submit', function(e) {
			e.preventDefault();
			const form = $(this);
			const msgBox = form.find('.healthedia-settings-msg');
			const submitBtn = form.find('.healthedia-submit-btn');

			msgBox.removeClass('healthedia-msg-success healthedia-msg-error').hide().text('');
			submitBtn.prop('disabled', true).text('Saving Settings...');

			$.ajax({
				url: healthedia_vars.ajax_url,
				type: 'POST',
				data: form.serialize(),
				success: function(response) {
					submitBtn.prop('disabled', false).text('Save Settings');
					if (response.success) {
						msgBox.addClass('healthedia-msg-success').text(response.data.message).fadeIn();
						// Reload page after a delay to show updated brand logo text in the header
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						msgBox.addClass('healthedia-msg-error').text(response.data.message).fadeIn();
					}
				},
				error: function() {
					submitBtn.prop('disabled', false).text('Save Settings');
					msgBox.addClass('healthedia-msg-error').text('An error occurred. Please try again.').fadeIn();
				}
			});
		});
	});
</script>
