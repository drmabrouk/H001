<?php
/**
 * Custom template routing and shortcode handlers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function healthedia_template_redirect( $template ) {
    if ( is_page( 'healthedia-search' ) || is_front_page() ) {
        $custom_template = HEALTHEDIA_PATH . 'templates/search.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    if ( is_page( 'login' ) ) {
        $custom_template = HEALTHEDIA_PATH . 'templates/auth.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    if ( is_page( 'healthedia-dashboard' ) ) {
        $custom_template = HEALTHEDIA_PATH . 'templates/dashboard.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    if ( is_page( 'healthedia-sitemap' ) ) {
        $custom_template = HEALTHEDIA_PATH . 'templates/sitemap.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter( 'template_include', 'healthedia_template_redirect', 99 );
