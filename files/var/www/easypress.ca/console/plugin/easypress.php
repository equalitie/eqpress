<?php
/*
Plugin Name: The Console
Text Domain: Console
Description: The Console.
Author: eQualit.ie
Author URI: http://equalit.ie
Plugin URI: http://equalit.ie
Version: 1.3
*/

// required for nginx to let WordPress know it's doing pretty permalinks
add_filter( 'got_rewrite', '__return_true' );

// Set error reporting level
error_reporting( E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR );

// Console plugin
require dirname(__FILE__) . '/easypress-console/easypress-console.php';

/**
 * Don't send the welcome e-mail
 * @param type $args
 * @return array
 *
 */
add_filter( 'wp_mail', 'ep_cancel_new_site_email' );
function ep_cancel_new_site_email( $args ) {
	if ( __( 'New WordPress Site' ) === $args['subject'] ) {
		$args = array(
			'to'          => '',
			'subject'     => '',
			'message'     => '',
			'headers'     => '',
			'attachments' => array()
		);
	}
	return $args;
}

/**
 * Show "Lock Down" in the admin bar when site is locked down
 *
 */
add_action( 'admin_bar_menu', 'ep_lockdown_on_admin_bar', 999 );
function ep_lockdown_on_admin_bar( $wp_admin_bar ) {
	if ( current_user_can( 'manage_options' ) )  {
		if ( 33 != fileowner( $_SERVER['DOCUMENT_ROOT'] . '/wp-login.php' ) ) {
			$args = array(
				'id' => 'ep_lockdown',
				'title' => 'Site Locked Down',
				'href' => admin_url() . 'admin.php?page=ep_lockdown'
			);
			$wp_admin_bar->add_node( $args );
		}
	}
}

/**
 * Show a warning message when the database is in read-only mode.
 *
 */
add_action( 'admin_notices', 'ep_db_read_only_message' );
function ep_db_read_only_message() {
    global $wpdb;
    $is_read_only = $wpdb->get_var( "show variables like 'read_only'", 1, 0 );
    if ( $is_read_only === "ON" ) {
        echo '<div class="error">
                 <p>*****************************************************************************************************</p>
                 <p><h3>We are performing server maintenance. All edit capabilities are currently disabled.</h3></p>
                 <p>Sorry for the inconvenience.</p>
                 <p>*****************************************************************************************************</p>
               </div>';
    }
}

/**
 *  Block known vulnerabilities in plugins and themes.
 *
 */
add_action( 'muplugins_loaded', 'ep_security_countermeasures' );
function ep_security_countermeasures() {
    // block access to Slider Revolution hack
    if ( preg_match( '/\/wp\-admin\/admin\-ajax\.php/', $_SERVER['REQUEST_URI'] ) ) {
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'revslider_show_image' && isset( $_REQUEST['img'] ) && preg_match( '/\.php$/i', $_REQUEST['img'] ) ) {
            error_log( "Slider Revolution Hack attempt detected from " . $_SERVER['REMOTE_ADDR'] );
            exit();
        }
    }
}

/**
 *  Block XML RPC pingbacks
 *
 */
add_filter( 'xmlrpc_methods', 'ep_remove_pingbacks' );
function ep_remove_pingbacks( $methods ) {
    unset( $methods['pingback.ping'] );
    unset( $methods['pingback.extensions.getPingbacks'] );
    return $methods;
}

/**
 * Make sure we have a decent password
 *
 */
add_action( 'admin_enqueue_scripts', 'pass_strength' );
function pass_strength() {
	wp_add_inline_style( 'admin-menu', '.pw-weak {display: none !important;}' );
}
