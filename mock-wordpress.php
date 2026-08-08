<?php
/**
 * Lightweight Mock WordPress 7.0.3 environment backed by PHP Sessions.
 * Simulates WordPress routing, hooks, templating, and native user database.
 */

session_start();

// Initialize mock user DB in session
if ( ! isset( $_SESSION['users'] ) ) {
    $_SESSION['users'] = [
        'researcher' => [
            'ID' => 1,
            'user_login' => 'researcher',
            'user_pass' => 'supersecret123',
            'user_email' => 'researcher@healthedia.org',
            'display_name' => 'Dr. Mabrouk',
        ]
    ];
}

// Define core WP constants
define( 'ABSPATH', __DIR__ . '/' );
define( 'DB_NAME', 'mock_wordpress.db' );

// Mock Sanitization
function sanitize_email( $email ) {
    return filter_var( trim( $email ), FILTER_SANITIZE_EMAIL );
}

function sanitize_text_field( $str ) {
    return htmlspecialchars( trim( $str ), ENT_QUOTES, 'UTF-8' );
}

function esc_url( $url ) {
    return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $text ) {
    return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_attr( $text ) {
    return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function esc_js( $text ) {
    return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
}

function home_url( $path = '' ) {
    return '/' . ltrim( $path, '/' );
}

function bloginfo( $show ) {
    if ( $show === 'charset' ) {
        echo 'UTF-8';
    }
}

function language_attributes() {
    echo 'lang="en-US"';
}

function wp_head() {
    echo "\n<!-- Mock WP Head -->\n";
}

function wp_footer() {
    echo "\n<!-- Mock WP Footer -->\n";
}

function get_header() {
    echo '<header class="theme-default-header"><div class="site-header-container">Default Theme Header (Astra)</div></header>';
}

function get_footer() {
    echo '<footer class="theme-default-footer">Default Theme Footer (Astra)</footer>';
}

// User Authentication and WP Signon Simulations
function is_user_logged_in() {
    return isset( $_SESSION['logged_in'] ) && $_SESSION['logged_in'] === true;
}

function wp_get_current_user() {
    $user_obj = new stdClass();
    if ( is_user_logged_in() && isset( $_SESSION['current_user_id'] ) ) {
        foreach ( $_SESSION['users'] as $user ) {
            if ( $user['ID'] == $_SESSION['current_user_id'] ) {
                $user_obj->ID = $user['ID'];
                $user_obj->user_login = $user['user_login'];
                $user_obj->display_name = $user['display_name'];
                $user_obj->user_email = $user['user_email'];
                return $user_obj;
            }
        }
    }
    $user_obj->ID = 0;
    $user_obj->display_name = 'Guest';
    return $user_obj;
}

function wp_signon( $creds = [], $secure = '' ) {
    $login = $creds['user_login'];
    $pass = $creds['user_password'];

    foreach ( $_SESSION['users'] as $user ) {
        if ( ($user['user_email'] === $login || $user['user_login'] === $login) && $user['user_pass'] === $pass ) {
            $_SESSION['logged_in'] = true;
            $_SESSION['current_user_id'] = $user['ID'];
            return $user;
        }
    }
    return new WP_Error( 'incorrect_password', '<strong>Error</strong>: The password or email you entered is incorrect.' );
}

function email_exists( $email ) {
    foreach ( $_SESSION['users'] as $user ) {
        if ( $user['user_email'] === $email ) {
            return true;
        }
    }
    return false;
}

function username_exists( $username ) {
    return isset( $_SESSION['users'][$username] );
}

function wp_create_user( $username, $password, $email ) {
    $id = count( $_SESSION['users'] ) + 1;
    $_SESSION['users'][$username] = [
        'ID' => $id,
        'user_login' => $username,
        'user_pass' => $password,
        'user_email' => $email,
        'display_name' => $username,
    ];
    return $id;
}

function wp_update_user( $userdata ) {
    $id = $userdata['ID'];
    foreach ( $_SESSION['users'] as $username => &$user ) {
        if ( $user['ID'] == $id ) {
            if ( isset( $userdata['display_name'] ) ) {
                $user['display_name'] = $userdata['display_name'];
            }
            return $id;
        }
    }
    return 0;
}

function get_user_by( $field, $value ) {
    foreach ( $_SESSION['users'] as $user ) {
        if ( $field === 'email' && $user['user_email'] === $value ) {
            $user_obj = new stdClass();
            $user_obj->ID = $user['ID'];
            $user_obj->user_login = $user['user_login'];
            $user_obj->display_name = $user['display_name'];
            return $user_obj;
        }
    }
    return false;
}

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
    return $thing instanceof WP_Error;
}

function wp_redirect( $location, $status = 302 ) {
    header( "Location: " . $location );
    exit;
}

function wp_safe_redirect( $location, $status = 302 ) {
    wp_redirect( $location, $status );
}

function is_ssl() {
    return false;
}

function wp_logout_url( $redirect = '/' ) {
    return '/logout?redirect=' . urlencode( $redirect );
}

function wp_nonce_field( $action = -1, $name = "_wpnonce", $referer = true, $echo = true ) {
    $html = '<input type="hidden" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="mock_nonce_value" />';
    if ( $echo ) {
        echo $html;
    }
    return $html;
}

function wp_verify_nonce( $nonce, $action = -1 ) {
    return $nonce === 'mock_nonce_value';
}

function selected( $selected, $current = true, $echo = true ) {
    $result = '';
    if ( (string) $selected === (string) $current ) {
        $result = ' selected="selected"';
    }
    if ( $echo ) {
        echo $result;
    }
    return $result;
}

// Admin page check
function is_admin() {
    return false;
}

// Loop simulation helpers & mock database
$mock_posts = [];
$loop_index = 0;

function have_posts() {
    global $mock_posts, $loop_index;
    return $loop_index < count( $mock_posts );
}

function the_post() {
    global $loop_index;
    $loop_index++;
}

function the_title() {
    global $mock_posts, $loop_index;
    $current = $mock_posts[$loop_index - 1] ?? null;
    echo $current ? esc_html( $current['title'] ) : 'Page Title';
}

function the_content() {
    global $mock_posts, $loop_index;
    $current = $mock_posts[$loop_index - 1] ?? null;
    echo $current ? $current['content'] : 'Page Content';
}

// Router simulation helpers
$current_route = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

function is_front_page() {
    global $current_route;
    return empty( $current_route ) || $current_route === 'index.php' || $current_route === 'healthedia-search';
}

function is_page( $page = '' ) {
    global $current_route;
    return $current_route === $page;
}

// Initialize loop database if we are visiting standard sample page
if ( $current_route === 'sample-page' ) {
    $mock_posts = [
        [
            'title' => 'Sample Research Page',
            'content' => '<p>This is a standard theme presentation page content demonstrating biomechanical gait adaptations and open-access index values.</p>'
        ]
    ];
}

function wp_insert_post( $args ) {
    return 1;
}

function get_page_by_path( $path ) {
    return null;
}

function wp_delete_post( $id, $force = true ) {
    return true;
}

// Handle metadata options (backed by PHP Session for cross-request persistence)
if ( ! isset( $_SESSION['options'] ) ) {
    $_SESSION['options'] = [];
}

function update_option( $name, $value ) {
    $_SESSION['options'][$name] = $value;
    return true;
}

function get_option( $name, $default = false ) {
    return isset( $_SESSION['options'][$name] ) ? $_SESSION['options'][$name] : $default;
}

function plugin_dir_path( $file ) {
    return __DIR__ . '/';
}

function plugin_dir_url( $file ) {
    return '/';
}

// Hook system simulation
$registered_hooks = [];

function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
    global $registered_hooks;
    $registered_hooks['action'][$tag][] = $callback;
}

function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
    global $registered_hooks;
    $registered_hooks['filter'][$tag][] = $callback;
}

function register_activation_hook( $file, $callback ) {
    if ( is_callable( $callback ) ) {
        $callback();
    }
}

function register_deactivation_hook( $file, $callback ) {}

// Handle Logout action directly
if ( $current_route === 'logout' ) {
    $_SESSION['logged_in'] = false;
    unset( $_SESSION['current_user_id'] );
    $redirect = isset( $_GET['redirect'] ) ? $_GET['redirect'] : '/';
    header( 'Location: ' . $redirect );
    exit;
}

// Include Main Plugin
require_once __DIR__ . '/healthedia.php';

// Simulate Template Redirection and Render
// Resolve templates using filter
$template = 'index.php';
if ( isset( $registered_hooks['filter']['template_include'] ) ) {
    foreach ( $registered_hooks['filter']['template_include'] as $callback ) {
        if ( is_callable( $callback ) ) {
            $template = $callback( $template );
        }
    }
}

// Start Output Buffering as registered in action
if ( isset( $registered_hooks['action']['template_redirect'] ) ) {
    foreach ( $registered_hooks['action']['template_redirect'] as $callback ) {
        if ( is_callable( $callback ) ) {
            $callback();
        }
    }
}

// Render selected page
if ( file_exists( $template ) ) {
    include $template;
} else {
    include __DIR__ . '/templates/search.php';
}

// Complete and flush buffering callback
ob_end_flush();
