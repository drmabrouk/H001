<?php
/**
 * Header template for Healthedia.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
$is_dashboard = ( strpos( $_SERVER['REQUEST_URI'], '/healthedia-dashboard' ) !== false );
$is_auth = ( strpos( $_SERVER['REQUEST_URI'], '/healthedia-auth' ) !== false );
$is_home = ( ! $is_dashboard && ! $is_auth && ( $_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php' ) );

// Get options
$logo_text = get_option( 'healthedia_logo_text', 'Healthedia' );
$sub_text  = get_option( 'healthedia_sub_text', 'GLOBAL HEALTH ARCHIVE' );
?>
<header class="healthedia-header">
	<div class="healthedia-header-container">
		<!-- Logo Section -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-logo-block">
			<span class="healthedia-logo-title"><?php echo esc_html( $logo_text ); ?></span>
			<span class="healthedia-logo-sub"><?php echo esc_html( $sub_text ); ?></span>
		</a>

		<!-- Mobile Menu Button -->
		<button type="button" class="healthedia-mobile-toggle" aria-label="Toggle Navigation">
			<svg class="healthedia-menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
			</svg>
		</button>

		<!-- Navigation Menu -->
		<nav class="healthedia-nav">
			<ul class="healthedia-nav-list">
				<li class="healthedia-nav-item">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-nav-link <?php echo $is_home ? 'healthedia-active' : ''; ?>">
						Archive Search
					</a>
				</li>
				<li class="healthedia-nav-item">
					<a href="<?php echo esc_url( home_url( '/researchers/' ) ); ?>" class="healthedia-nav-link">
						Researchers
					</a>
				</li>
				<li class="healthedia-nav-item">
					<a href="<?php echo esc_url( home_url( '/institutions/' ) ); ?>" class="healthedia-nav-link">
						Institutions
					</a>
				</li>
				<li class="healthedia-nav-item">
					<a href="<?php echo esc_url( home_url( '/scientific-journal/' ) ); ?>" class="healthedia-nav-link">
						Scientific Journal
					</a>
				</li>
			</ul>
		</nav>

		<!-- Auth Entry Point -->
		<div class="healthedia-auth-action">
			<?php if ( is_user_logged_in() ) : ?>
				<?php
					$current_user = wp_get_current_user();
					$display_name = ! empty( $current_user->display_name ) ? $current_user->display_name : $current_user->user_login;
				?>
				<div class="healthedia-user-menu-wrapper">
					<a href="<?php echo esc_url( home_url( '/healthedia-dashboard/' ) ); ?>" class="healthedia-nav-link healthedia-dashboard-badge <?php echo $is_dashboard ? 'healthedia-active' : ''; ?>">
						Dashboard
					</a>
					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="healthedia-logout-btn" title="Logout">
						<svg class="healthedia-logout-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
						</svg>
					</a>
				</div>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/healthedia-auth/' ) ); ?>" class="healthedia-login-btn">
					LOGIN
				</a>
			<?php endif; ?>
		</div>
	</div>

	<!-- Mobile Dropdown Menu -->
	<div class="healthedia-mobile-nav">
		<ul class="healthedia-mobile-nav-list">
			<li>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="healthedia-mobile-link <?php echo $is_home ? 'healthedia-active' : ''; ?>">
					Archive Search
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/researchers/' ) ); ?>" class="healthedia-mobile-link">
					Researchers
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/institutions/' ) ); ?>" class="healthedia-mobile-link">
					Institutions
				</a>
			</li>
			<li>
				<a href="<?php echo esc_url( home_url( '/scientific-journal/' ) ); ?>" class="healthedia-mobile-link">
					Scientific Journal
				</a>
			</li>
			<?php if ( is_user_logged_in() ) : ?>
				<li>
					<a href="<?php echo esc_url( home_url( '/healthedia-dashboard/' ) ); ?>" class="healthedia-mobile-link <?php echo $is_dashboard ? 'healthedia-active' : ''; ?>">
						Dashboard (<?php echo esc_html( $display_name ); ?>)
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="healthedia-mobile-link text-red">
						Logout
					</a>
				</li>
			<?php else : ?>
				<li>
					<a href="<?php echo esc_url( home_url( '/healthedia-auth/' ) ); ?>" class="healthedia-mobile-link-login">
						LOGIN
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</header>
