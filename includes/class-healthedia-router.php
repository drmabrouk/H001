<?php
/**
 * Routing logic for Healthedia custom pages.
 * Intercepts requests for /healthedia-dashboard and /healthedia-auth.
 *
 * @package Healthedia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Healthedia_Router {

	/**
	 * Initialize routing hooks.
	 */
	public function init() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'intercept_requests' ) );
	}

	/**
	 * Add rewrite rules for Pretty Permalinks.
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule(
			'^healthedia-dashboard/?$',
			'index.php?healthedia_page=dashboard',
			'top'
		);
		add_rewrite_rule(
			'^healthedia-auth/?$',
			'index.php?healthedia_page=auth',
			'top'
		);
	}

	/**
	 * Register custom query variables.
	 */
	public function register_query_vars( $vars ) {
		$vars[] = 'healthedia_page';
		return $vars;
	}

	/**
	 * Intercept the page request and load corresponding Healthedia templates.
	 */
	public function intercept_requests() {
		// Try pretty permalinks or fallback to query params
		$page = get_query_var( 'healthedia_page' );

		// Fallback detection using REQUEST_URI (extremely robust!)
		if ( empty( $page ) ) {
			$uri = $_SERVER['REQUEST_URI'] ?? '';
			if ( strpos( $uri, '/healthedia-dashboard' ) !== false ) {
				$page = 'dashboard';
			} elseif ( strpos( $uri, '/healthedia-auth' ) !== false ) {
				$page = 'auth';
			}
		}

		if ( 'dashboard' === $page ) {
			// Require authentication
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( home_url( '/healthedia-auth/?redirect_to=' . urlencode( home_url( '/healthedia-dashboard/' ) ) ) );
				exit;
			}

			// Render Healthedia Dashboard Page
			$this->load_custom_template( 'page-dashboard.php' );
			exit;
		}

		if ( 'auth' === $page ) {
			// If already logged in, redirect to Dashboard
			if ( is_user_logged_in() ) {
				wp_safe_redirect( home_url( '/healthedia-dashboard/' ) );
				exit;
			}

			// Render Healthedia Authentication Page
			$this->load_custom_template( 'page-auth.php' );
			exit;
		}
	}

	/**
	 * Load a plugin template inside a clean standalone HTML template shell.
	 */
	private function load_custom_template( $template_name ) {
		$template_path = HEALTHEDIA_PATH . 'templates/' . $template_name;

		if ( file_exists( $template_path ) ) {
			// Serve a perfectly clean HTML layout
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<?php wp_head(); ?>
				<style>
					/* Clean standalone reset so theme styles don't conflict */
					html, body {
						margin: 0;
						padding: 0;
						background-color: var(--healthedia-bg-light, #f8fafc);
						color: var(--healthedia-text-dark, #0e1e38);
						min-height: 100vh;
						display: flex;
						flex-direction: column;
					}
					.healthedia-standalone-container {
						flex: 1;
						display: flex;
						flex-direction: column;
						width: 100%;
					}
					.healthedia-standalone-content {
						flex: 1;
						width: 100%;
					}
				</style>
			</head>
			<body <?php body_class( 'healthedia-standalone-page' ); ?>>
				<?php
				// Output custom header (since we are in an independent template,
				// wp_body_open runs and bootstrap's inject_header outputs our layout-header.php)
				if ( function_exists( 'wp_body_open' ) ) {
					wp_body_open();
				} else {
					do_action( 'wp_body_open' );
				}
				?>
				<div class="healthedia-standalone-container">
					<main class="healthedia-standalone-content">
						<?php include $template_path; ?>
					</main>
				</div>
				<?php
				// Outputs our layout-footer.php and loads footer scripts
				wp_footer();
				?>
			</body>
			</html>
			<?php
		} else {
			wp_die( 'Template not found: ' . esc_html( $template_name ) );
		}
	}
}
