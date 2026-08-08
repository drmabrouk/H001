<?php
/**
 * Page creation/cleanup on activation/deactivation.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function healthedia_activate_plugin() {
    $pages = [
        'healthedia-search' => [
            'title' => 'Archive Search',
            'content' => '[healthedia_search]',
        ],
        'healthedia-auth' => [
            'title' => 'Authentication',
            'content' => '[healthedia_auth]',
        ],
        'healthedia-dashboard' => [
            'title' => 'Dashboard',
            'content' => '[healthedia_dashboard]',
        ],
    ];

    foreach ( $pages as $slug => $page_info ) {
        $existing_page = get_page_by_path( $slug );
        if ( ! $existing_page ) {
            $post_id = wp_insert_post( [
                'post_title'     => $page_info['title'],
                'post_name'      => $slug,
                'post_content'   => $page_info['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ] );
            if ( $slug === 'healthedia-search' ) {
                update_option( 'page_on_front', $post_id );
                update_option( 'show_on_front', 'page' );
            }
        }
    }

    // Initialize Menu Options
    $default_header = [
        ['title' => 'Archive Search', 'url' => home_url('/healthedia-search/')],
        ['title' => 'Researchers', 'url' => home_url('/healthedia-dashboard/')],
        ['title' => 'Institutions', 'url' => '#'],
        ['title' => 'Scientific Journal', 'url' => '#']
    ];
    if ( ! get_option( 'healthedia_header_menu' ) ) {
        update_option( 'healthedia_header_menu', $default_header );
    }

    $default_footer = [
        ['title' => 'Privacy Policy', 'url' => '#'],
        ['title' => 'Terms & Conditions', 'url' => '#'],
        ['title' => 'Publication Policies', 'url' => '#'],
        ['title' => 'Certificate Verification', 'url' => '#'],
        ['title' => 'Support', 'url' => '#']
    ];
    if ( ! get_option( 'healthedia_footer_menu' ) ) {
        update_option( 'healthedia_footer_menu', $default_footer );
    }

    // Initialize Authentication Settings Options
    $default_auth = [
        'login' => 'enabled',
        'registration' => 'enabled'
    ];
    if ( ! get_option( 'healthedia_auth_options' ) ) {
        update_option( 'healthedia_auth_options', $default_auth );
    }
}

function healthedia_deactivate_plugin() {
    $pages = [ 'healthedia-search', 'healthedia-auth', 'healthedia-dashboard' ];
    foreach ( $pages as $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            wp_delete_post( $page->ID, true );
        }
    }
}
