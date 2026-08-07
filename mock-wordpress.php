<?php
/**
 * Mock WordPress Environment for Local Verification & Playwright Testing.
 * Emulates WP 7.0.3 Core API, SQLite Database, AJAX endpoints, and Virtual Routing.
 */

// Start session to persist user login and database options
if ( session_status() === PHP_SESSION_NONE ) {
	session_start();
}

// 1. Core Definitions
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
if ( ! defined( 'HEALTHEDIA_VERSION' ) ) {
	define( 'HEALTHEDIA_VERSION', '1.0.0' );
}

// 2. Mock SQLite Database Initialization
$db_file = __DIR__ . '/mock-wp-db.sqlite';
try {
	$db = new PDO( 'sqlite:' . $db_file );
	$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	// Create options table
	$db->exec( "CREATE TABLE IF NOT EXISTS options (
		option_name TEXT PRIMARY KEY,
		option_value TEXT
	)" );

	// Create users table
	$db->exec( "CREATE TABLE IF NOT EXISTS users (
		ID INTEGER PRIMARY KEY AUTOINCREMENT,
		user_login TEXT UNIQUE,
		user_email TEXT UNIQUE,
		user_pass TEXT,
		first_name TEXT DEFAULT '',
		last_name TEXT DEFAULT '',
		display_name TEXT DEFAULT ''
	)" );

	// Insert mock administrator if not exists
	$admin_exists = $db->query( "SELECT count(*) FROM users WHERE user_login = 'admin'" )->fetchColumn();
	if ( ! $admin_exists ) {
		$stmt = $db->prepare( "INSERT INTO users (user_login, user_email, user_pass, first_name, last_name, display_name) VALUES (?, ?, ?, ?, ?, ?)" );
		$stmt->execute( array( 'admin', 'admin@healthedia.com', 'admin123', 'John', 'Doe', 'Admin John' ) );
	}
} catch ( Exception $e ) {
	die( 'Mock Database Connection Failed: ' . $e->getMessage() );
}

// 3. Mock Actions & Filters System
$wp_actions = array();
$wp_filters = array();

function register_activation_hook( $file, $callback ) {}
function register_deactivation_hook( $file, $callback ) {}
function flush_rewrite_rules() {}

function add_query_arg( $key, $value = false, $url = false ) {
	if ( is_array( $key ) ) {
		$args = $key;
		$url = $value ? $value : ( $_SERVER['REQUEST_URI'] ?? '' );
	} else {
		$args = array( $key => $value );
		$url = $url ? $url : ( $_SERVER['REQUEST_URI'] ?? '' );
	}
	$parts = explode( '?', $url );
	$base = $parts[0];
	$query = isset( $parts[1] ) ? $parts[1] : '';
	parse_str( $query, $existing_args );
	$new_args = array_merge( $existing_args, $args );
	$query_str = http_build_query( $new_args );
	return $base . ( $query_str ? '?' . $query_str : '' );
}

function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	global $wp_actions;
	$wp_actions[ $hook_name ][] = $callback;
}

function do_action( $hook_name, ...$args ) {
	global $wp_actions;
	if ( isset( $wp_actions[ $hook_name ] ) ) {
		foreach ( $wp_actions[ $hook_name ] as $callback ) {
			call_user_func_array( $callback, $args );
		}
	}
}

function add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
	global $wp_filters;
	$wp_filters[ $hook_name ][] = $callback;
}

function apply_filters( $hook_name, $value, ...$args ) {
	global $wp_filters;
	if ( isset( $wp_filters[ $hook_name ] ) ) {
		foreach ( $wp_filters[ $hook_name ] as $callback ) {
			$value = call_user_func_array( $callback, array_merge( array( $value ), $args ) );
		}
	}
	return $value;
}

// 4. Mock Options APIs
function get_option( $option_name, $default_value = false ) {
	global $db;
	$stmt = $db->prepare( "SELECT option_value FROM options WHERE option_name = ?" );
	$stmt->execute( array( $option_name ) );
	$val = $stmt->fetchColumn();
	return ( $val !== false ) ? $val : $default_value;
}

function update_option( $option_name, $option_value ) {
	global $db;
	$stmt = $db->prepare( "INSERT INTO options (option_name, option_value) VALUES (?, ?) ON CONFLICT(option_name) DO UPDATE SET option_value = excluded.option_value" );
	return $stmt->execute( array( $option_name, $option_value ) );
}

function delete_option( $option_name ) {
	global $db;
	$stmt = $db->prepare( "DELETE FROM options WHERE option_name = ?" );
	return $stmt->execute( array( $option_name ) );
}

// 5. Mock Asset Loading
$enqueued_scripts = array();
$enqueued_styles = array();
$localized_scripts = array();

function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
	global $enqueued_styles;
	$enqueued_styles[ $handle ] = $src;
}

function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	global $enqueued_scripts;
	$enqueued_scripts[ $handle ] = $src;
}

function wp_localize_script( $handle, $object_name, $l10n ) {
	global $localized_scripts;
	$localized_scripts[ $handle ][ $object_name ] = $l10n;
}

function wp_head() {
	global $enqueued_styles;
	// Load JQuery in the head to prevent template reference errors
	echo '<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>' . "\n";
	foreach ( $enqueued_styles as $handle => $src ) {
		echo '<link rel="stylesheet" id="' . esc_attr( $handle ) . '-css" href="' . esc_url( $src ) . '" type="text/css" media="all" />' . "\n";
	}
	do_action( 'wp_head' );
}

function wp_footer() {
	global $enqueued_scripts, $localized_scripts;

	// Print localized variables
	echo '<script type="text/javascript">' . "\n";
	foreach ( $localized_scripts as $handle => $vars ) {
		foreach ( $vars as $obj => $data ) {
			echo 'var ' . esc_attr( $obj ) . ' = ' . json_encode( $data ) . ';' . "\n";
		}
	}
	echo '</script>' . "\n";

	// Enqueue JQuery from a CDN for testing
	echo '<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>' . "\n";

	// Enqueue our scripts
	foreach ( $enqueued_scripts as $handle => $src ) {
		echo '<script type="text/javascript" id="' . esc_attr( $handle ) . '-js" src="' . esc_url( $src ) . '"></script>' . "\n";
	}

	do_action( 'wp_footer' );
}

// 6. Mock Sanitization & Security APIs
function esc_html( $text ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_url( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL );
}

function sanitize_text_field( $str ) {
	return strip_tags( trim( $str ) );
}

function sanitize_email( $email ) {
	return filter_var( trim( $email ), FILTER_SANITIZE_EMAIL );
}

function sanitize_user( $username ) {
	return preg_replace( '/[^a-zA-Z0-9_.-]/', '', $username );
}

function sanitize_url( $url ) {
	return filter_var( $url, FILTER_SANITIZE_URL );
}

function wp_create_nonce( $action ) {
	return md5( $action . 'mock-salt' );
}

function wp_verify_nonce( $nonce, $action ) {
	return $nonce === md5( $action . 'mock-salt' );
}

function wp_nonce_field( $action, $name ) {
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( wp_create_nonce( $action ) ) . '" />';
}

function checked( $val1, $val2, $echo = true ) {
	$result = ( $val1 === $val2 ) ? ' checked="checked"' : '';
	if ( $echo ) {
		echo $result;
	}
	return $result;
}

// 7. Mock User / Authentication APIs
function is_admin() {
	return false;
}

function is_user_logged_in() {
	return isset( $_SESSION['mock_user_id'] );
}

function get_current_user_id() {
	return $_SESSION['mock_user_id'] ?? 0;
}

function wp_get_current_user() {
	global $db;
	$user_id = get_current_user_id();
	$user = new stdClass();
	$user->ID = 0;
	$user->user_login = 'guest';
	$user->display_name = 'Guest';
	$user->user_email = '';
	$user->first_name = '';
	$user->last_name = '';

	if ( $user_id > 0 ) {
		$stmt = $db->prepare( "SELECT * FROM users WHERE ID = ?" );
		$stmt->execute( array( $user_id ) );
		$row = $stmt->fetch( PDO::FETCH_ASSOC );
		if ( $row ) {
			foreach ( $row as $key => $val ) {
				$user->$key = $val;
			}
		}
	}
	return $user;
}

function current_user_can( $capability ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	// Admin user has manage_options capability
	$current_user = wp_get_current_user();
	return ( $current_user->user_login === 'admin' );
}

function username_exists( $username ) {
	global $db;
	$stmt = $db->prepare( "SELECT count(*) FROM users WHERE user_login = ?" );
	$stmt->execute( array( $username ) );
	return $stmt->fetchColumn() > 0;
}

function email_exists( $email ) {
	global $db;
	$stmt = $db->prepare( "SELECT count(*) FROM users WHERE user_email = ?" );
	$stmt->execute( array( $email ) );
	return $stmt->fetchColumn() > 0;
}

function wp_create_user( $username, $password, $email ) {
	global $db;
	try {
		$stmt = $db->prepare( "INSERT INTO users (user_login, user_pass, user_email, display_name) VALUES (?, ?, ?, ?)" );
		$stmt->execute( array( $username, $password, $email, $username ) );
		return $db->lastInsertId();
	} catch ( Exception $e ) {
		return new WP_Error( 'registration_failed', $e->getMessage() );
	}
}

function wp_update_user( $userdata ) {
	global $db;
	try {
		$user_id = $userdata['ID'];
		$first_name = $userdata['first_name'];
		$last_name = $userdata['last_name'];
		$email = $userdata['user_email'];
		$display_name = $first_name . ' ' . $last_name;

		if ( isset( $userdata['user_pass'] ) ) {
			$stmt = $db->prepare( "UPDATE users SET first_name = ?, last_name = ?, user_email = ?, display_name = ?, user_pass = ? WHERE ID = ?" );
			$stmt->execute( array( $first_name, $last_name, $email, $display_name, $userdata['user_pass'], $user_id ) );
		} else {
			$stmt = $db->prepare( "UPDATE users SET first_name = ?, last_name = ?, user_email = ?, display_name = ? WHERE ID = ?" );
			$stmt->execute( array( $first_name, $last_name, $email, $display_name, $user_id ) );
		}
		return $user_id;
	} catch ( Exception $e ) {
		return new WP_Error( 'update_failed', $e->getMessage() );
	}
}

function wp_signon( $info, $secure_cookie ) {
	global $db;
	$username = $info['user_login'];
	$password = $info['user_password'];

	$stmt = $db->prepare( "SELECT * FROM users WHERE user_login = ? OR user_email = ?" );
	$stmt->execute( array( $username, $username ) );
	$row = $stmt->fetch( PDO::FETCH_ASSOC );

	if ( $row && $row['user_pass'] === $password ) {
		$_SESSION['mock_user_id'] = $row['ID'];

		$user = new stdClass();
		foreach ( $row as $key => $val ) {
			$user->$key = $val;
		}
		return $user;
	}
	return new WP_Error( 'invalid_credentials', 'Invalid credentials.' );
}

function wp_logout_url( $redirect ) {
	return home_url( '/?action=logout&redirect=' . urlencode( $redirect ) );
}

function wp_safe_redirect( $location ) {
	header( "Location: " . $location );
	exit;
}

function wp_die( $message ) {
	die( $message );
}

// WP_Error helper class
class WP_Error {
	public $code;
	public $message;
	public function __construct( $code, $message ) {
		$this->code = $code;
		$this->message = $message;
	}
	public function get_error_message() {
		return $this->message;
	}
}
function is_wp_error( $thing ) {
	return ( $thing instanceof WP_Error );
}

// AJAX Helper
function wp_send_json_success( $data ) {
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'success' => true, 'data' => $data ) );
	exit;
}

function wp_send_json_error( $data ) {
	header( 'Content-Type: application/json' );
	echo json_encode( array( 'success' => false, 'data' => $data ) );
	exit;
}

// 8. Routing Helpers & Environment Simulation
function plugin_dir_path( $file ) {
	return __DIR__ . '/';
}

function plugin_dir_url( $file ) {
	return '/';
}

function plugin_basename( $file ) {
	return 'healthedia/healthedia.php';
}

function home_url( $path = '' ) {
	return 'http://127.0.0.1:8000' . $path;
}

function site_url( $path = '' ) {
	return home_url( $path );
}

function admin_url( $path = '' ) {
	return home_url( '/' . $path );
}

function language_attributes() {
	echo 'lang="en-US"';
}

function bloginfo( $show ) {
	if ( $show === 'charset' ) {
		echo 'UTF-8';
	}
}

function body_class( $class = '' ) {
	echo 'class="' . esc_attr( $class ) . '"';
}

// 9. Load Plugin and Intercept Request Routing
require_once __DIR__ . '/healthedia.php';

// Simulate Plugins Loaded hook
do_action( 'plugins_loaded' );

// Enqueue styles and scripts (mimicking wp_enqueue_scripts action hook)
$bootstrap = new Healthedia_Bootstrap();
$bootstrap->enqueue_assets();

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';

// Parse query variables
if ( strpos( $request_uri, '/healthedia-dashboard/' ) !== false ) {
	$GLOBALS['wp_query_vars']['healthedia_page'] = 'dashboard';
} elseif ( strpos( $request_uri, '/healthedia-auth/' ) !== false ) {
	$GLOBALS['wp_query_vars']['healthedia_page'] = 'auth';
}

function get_query_var( $var ) {
	return $GLOBALS['wp_query_vars'][ $var ] ?? '';
}

// Intercept logout action
if ( isset( $_GET['action'] ) && $_GET['action'] === 'logout' ) {
	unset( $_SESSION['mock_user_id'] );
	session_destroy();
	$redirect = sanitize_url( $_GET['redirect'] ?? '/' );
	wp_safe_redirect( $redirect );
	exit;
}

// Intercept AJAX Endpoints
if ( strpos( $request_uri, '/admin-ajax.php' ) !== false ) {
	$action = $_POST['action'] ?? $_GET['action'] ?? '';
	if ( ! empty( $action ) ) {
		if ( is_user_logged_in() ) {
			do_action( 'wp_ajax_' . $action );
		} else {
			// No privilege fallback
			global $wp_actions;
			if ( isset( $wp_actions[ 'wp_ajax_nopriv_' . $action ] ) ) {
				do_action( 'wp_ajax_nopriv_' . $action );
			} else {
				do_action( 'wp_ajax_' . $action );
			}
		}
	}
	exit;
}

// Intercept static files directly
if ( preg_match( '/\.(css|js)$/', $request_uri ) ) {
	$file_path = __DIR__ . '/' . ltrim( parse_url( $request_uri, PHP_URL_PATH ), '/' );
	if ( file_exists( $file_path ) ) {
		$mime_type = ( substr( $file_path, -3 ) === '.js' ) ? 'application/javascript' : 'text/css';
		header( "Content-Type: $mime_type" );
		readfile( $file_path );
		exit;
	}
}

// Trigger Custom Pages Virtual Router Routing
do_action( 'template_redirect' );

// 10. Default Page Render: Astra Theme Simulation Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Healthedia - Global Health Archive</title>
	<?php wp_head(); ?>
	<style>
		/* Simulated Astra Theme styling */
		html, body {
			margin: 0;
			padding: 0;
			font-family: 'Inter', sans-serif;
			background-color: #ffffff;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
		}

		/* Core Hero Area matching Screenshot 1 */
		.simulated-hero {
			flex: 1;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			text-align: center;
			padding: 80px 24px;
			max-width: 1000px;
			margin: 0 auto;
		}

		.simulated-hero h1 {
			font-family: 'Plus Jakarta Sans', sans-serif;
			font-size: 56px;
			font-weight: 800;
			color: #000000;
			margin: 0;
			letter-spacing: -1.5px;
			text-transform: uppercase;
		}

		.simulated-hero-sub {
			font-family: 'Plus Jakarta Sans', sans-serif;
			font-size: 11px;
			font-weight: 700;
			color: #64748b;
			letter-spacing: 1.5px;
			margin-top: 10px;
			text-transform: uppercase;
		}

		/* Underline accent */
		.simulated-accent-line {
			width: 48px;
			height: 3px;
			background-color: #000000;
			margin: 24px auto;
		}

		.simulated-hero-desc {
			font-size: 15px;
			color: #64748b;
			line-height: 1.6;
			max-width: 600px;
			margin: 0 auto 40px auto;
		}

		/* Unified Search Bar mimicking Screenshot 1 */
		.simulated-search-container {
			width: 100%;
			max-width: 740px;
			display: flex;
			align-items: center;
			background: #ffffff;
			border: 1px solid #e2e8f0;
			border-radius: 20px;
			padding: 8px 12px;
			box-shadow: 0 4px 20px -2px rgba(0,0,0,0.02);
			margin-bottom: 24px;
		}

		.simulated-search-icon {
			color: #94a3b8;
			margin-left: 12px;
			margin-right: 12px;
			width: 20px;
			height: 20px;
		}

		.simulated-search-input {
			flex: 1;
			border: none;
			outline: none;
			font-size: 15px;
			color: #000000;
			font-family: 'Inter', sans-serif;
		}

		.simulated-search-input::placeholder {
			color: #94a3b8;
		}

		.simulated-search-kbd {
			background-color: #f1f5f9;
			color: #94a3b8;
			padding: 4px 10px;
			border-radius: 6px;
			font-size: 12px;
			font-weight: 600;
			margin-right: 12px;
		}

		.simulated-search-mic, .simulated-search-star {
			background: none;
			border: none;
			color: #64748b;
			cursor: pointer;
			padding: 8px;
			border-radius: 50%;
			transition: background-color 0.2s;
			margin-right: 4px;
		}

		.simulated-search-mic:hover, .simulated-search-star:hover {
			background-color: #f1f5f9;
		}

		.simulated-search-btn {
			background-color: #000000;
			color: #ffffff;
			border: none;
			border-radius: 12px;
			padding: 12px 28px;
			font-size: 13px;
			font-weight: 700;
			cursor: pointer;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			transition: background-color 0.2s;
		}

		.simulated-search-btn:hover {
			background-color: #1e1e1e;
		}

		/* Tags list */
		.simulated-tags-list {
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
			gap: 8px;
			max-width: 650px;
		}

		.simulated-tag {
			background-color: #f8fafc;
			color: #64748b;
			padding: 6px 14px;
			border-radius: 9999px;
			font-size: 10px;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			text-decoration: none;
			border: 1px solid #f1f5f9;
			transition: all 0.2s;
		}

		.simulated-tag:hover {
			background-color: #f1f5f9;
			color: #000000;
			border-color: #cbd5e1;
		}
	</style>
</head>
<body class="simulated-astra-theme">
	<?php
	// Trigger the injected global header at the start of body
	do_action( 'wp_body_open' );
	?>

	<div class="simulated-hero">
		<h1>Healthedia</h1>
		<div class="simulated-hero-sub">Global Health & Performance Archive</div>

		<div class="simulated-accent-line"></div>

		<p class="simulated-hero-desc">
			The leading open-access indexing database for medical, rehabilitation, sports physiology, biomechanics, and human performance studies.
		</p>

		<!-- Simulated Search container mimicking lلقطة شاشة 2026-08-07 174837.png -->
		<div class="simulated-search-container">
			<svg class="simulated-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
			</svg>
			<input type="text" class="simulated-search-input" placeholder="Search by title, author, keyword, journal, specialty or DOI...">
			<span class="simulated-search-kbd">/</span>
			<button type="button" class="simulated-search-mic" title="Voice Search">
				<svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
				</svg>
			</button>
			<button type="button" class="simulated-search-star" title="AI Search Assistant">
				<svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
				</svg>
			</button>
			<button type="button" class="simulated-search-btn">
				Search
			</button>
		</div>

		<!-- Simulated tags beneath search -->
		<div class="simulated-tags-list">
			<a href="#" class="simulated-tag">HIIT VS CONTINUOUS AEROBIC</a>
			<a href="#" class="simulated-tag">ACHILLES TENDINOPATHY</a>
			<a href="#" class="simulated-tag">MYOKINES IN MUSCLE AGING</a>
			<a href="#" class="simulated-tag">SLEEP OPTIMIZATION</a>
			<a href="#" class="simulated-tag">SARCOPENIA</a>
			<a href="#" class="simulated-tag">GAIT BIOMECHANICS</a>
			<a href="#" class="simulated-tag">KETONE MONOESTER SUPPLEMENTATION</a>
		</div>
	</div>

	<?php
	// Trigger the injected global footer at the end of body
	wp_footer();
	?>
</body>
</html>
