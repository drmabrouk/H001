<?php
/**
 * Header template for Healthedia - Sports Medicine Journal Edition.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_dashboard = ( strpos( $_SERVER['REQUEST_URI'] ?? '', '/healthedia-dashboard' ) !== false );
$is_auth = ( strpos( $_SERVER['REQUEST_URI'] ?? '', '/healthedia-auth' ) !== false );
$is_home = ( ! $is_dashboard && ! $is_auth && ( ( $_SERVER['REQUEST_URI'] ?? '' ) === '/' || ( $_SERVER['REQUEST_URI'] ?? '' ) === '/index.php' ) );

// Get options
$logo_text = get_option( 'healthedia_logo_text', 'Healthedia' );
$sub_text  = get_option( 'healthedia_sub_text', 'GLOBAL HEALTH ARCHIVE' );
?>
<header class="healthedia-header-new">
	<div class="healthedia-header-container-new">

		<!-- Left Side: Editorial Branding Badge -->
		<div class="healthedia-branding-badge">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-brand-link">
				<div class="healthedia-editorial-emblem">
					<!-- Medical cross in an academic shield representation -->
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
						<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
						<path d="M12 8v8M9 12h6"/>
					</svg>
				</div>
				<div class="healthedia-brand-details">
					<div class="healthedia-brand-journal-title">Healthedia Sports Medicine Journal</div>
					<div class="healthedia-brand-meta">
						<span class="healthedia-brand-issn">ISSN: 2831-7726</span>
						<span class="healthedia-brand-divider">|</span>
						<span class="healthedia-brand-accreditation">
							<svg viewBox="0 0 20 20" fill="currentColor">
								<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
							</svg>
							Accredited Journal
						</span>
					</div>
				</div>
			</a>
		</div>

		<!-- Center: Responsive Primary Navigation Menu -->
		<nav class="healthedia-nav-new">
			<ul class="healthedia-nav-list-new">
				<li>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-link-new <?php echo $is_home ? 'healthedia-active-new' : ''; ?>">
						Archive Search
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( home_url( '/scientific-journal/' ) ); ?>" class="healthedia-link-new">
						Scientific Journal
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( home_url( '/researchers/' ) ); ?>" class="healthedia-link-new">
						Researchers Directory
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( home_url( '/institutions/' ) ); ?>" class="healthedia-link-new">
						Institutions
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>" class="healthedia-link-new">
						Courses
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( home_url( '/research-workspace/' ) ); ?>" class="healthedia-link-new">
						Research Workspace
					</a>
				</li>
			</ul>
		</nav>

		<!-- Right Side: Search, Notifications, Avatar, Mobile Trigger -->
		<div class="healthedia-header-right-new">
			<!-- Integrated global search field -->
			<div class="healthedia-quick-search">
				<svg class="healthedia-search-icon-new" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
				</svg>
				<input type="text" class="healthedia-search-input-new" placeholder="Global Search...">
				<span class="healthedia-search-shortcut">/</span>
			</div>

			<!-- Keyboard shortcuts trigger -->
			<button class="healthedia-icon-btn-new" title="Keyboard Shortcuts" aria-label="Shortcuts">
				<span class="healthedia-shortcut-trigger">?</span>
			</button>

			<!-- Interactive notification bell -->
			<div class="healthedia-notification-wrapper">
				<button class="healthedia-icon-btn-new" title="Notifications" aria-label="Notifications">
					<svg class="healthedia-bell-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
					</svg>
					<span class="healthedia-bell-badge">3</span>
				</button>
			</div>

			<!-- User profile avatar or Login trigger -->
			<div class="healthedia-user-block-new">
				<?php if ( is_user_logged_in() ) : ?>
					<?php
						$current_user = wp_get_current_user();
						$display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : $current_user->user_login;
						$is_admin = current_user_can( 'manage_options' );
						$role_name = $is_admin ? 'ADMINISTRATOR' : 'AUTHOR';
					?>
					<div class="healthedia-avatar-wrapper-new">
						<a href="<?php echo esc_url( home_url( '/healthedia-dashboard/' ) ); ?>" class="healthedia-avatar-trigger-new" title="View Console">
							<div class="healthedia-avatar-img-new">
								<?php echo esc_html( strtoupper( substr( $current_user->user_login, 0, 2 ) ) ); ?>
							</div>
							<span class="healthedia-user-role-badge"><?php echo esc_html( $role_name ); ?></span>
						</a>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="healthedia-header-logout-btn-new" title="Logout">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
							</svg>
						</a>
					</div>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/healthedia-auth/' ) ); ?>" class="healthedia-login-btn-new">
						LOGIN
					</a>
				<?php endif; ?>
			</div>

			<!-- Mobile responsive menu drawer trigger -->
			<button class="healthedia-mobile-toggle-new" aria-label="Toggle Navigation">
				<svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
				</svg>
			</button>
		</div>
	</div>

	<!-- Mobile Responsive Dropdown Menu -->
	<div class="healthedia-mobile-nav-new">
		<ul class="healthedia-mobile-nav-list-new">
			<li>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-mobile-link-new <?php echo $is_home ? 'healthedia-active-new' : ''; ?>">
					Archive Search
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/scientific-journal/' ) ); ?>" class="healthedia-mobile-link-new">
					Scientific Journal
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/researchers/' ) ); ?>" class="healthedia-mobile-link-new">
					Researchers Directory
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/institutions/' ) ); ?>" class="healthedia-mobile-link-new">
					Institutions
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>" class="healthedia-mobile-link-new">
					Courses
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/research-workspace/' ) ); ?>" class="healthedia-mobile-link-new">
					Research Workspace
				</a>
			</li>
			<?php if ( is_user_logged_in() ) : ?>
				<li>
					<a href="<?php echo esc_url( home_url( '/healthedia-dashboard/' ) ); ?>" class="healthedia-mobile-link-new highlight-dashboard">
						Dashboard (<?php echo esc_html( $role_name ); ?>)
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="healthedia-mobile-link-new text-red">
						Logout
					</a>
				</li>
			<?php else : ?>
				<li>
					<a href="<?php echo esc_url( home_url( '/healthedia-auth/' ) ); ?>" class="healthedia-mobile-link-login-new">
						LOGIN
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</header>
