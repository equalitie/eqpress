<?php
/**
 * @package BruteProtect
 */
/*
Plugin Name: BruteProtect
Plugin URI: http://bruteprotect.com/
Description: BruteProtect allows the millions of WordPress bloggers to work together to defeat Brute Force attacks. It keeps your site protected from brute force security attacks even while you sleep. To get started: 1) Click the "Activate" link to the left of this description, 2) Sign up for a BruteProtect API key, and 3) Go to your BruteProtect configuration page, and save your API key.
Version: 1.0.0.2b
Author: Hotchkiss Consulting Group
Author URI: http://hotchkissconsulting.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('BRUTEPROTECT_VERSION', '1.0.0.2b');
define('BRUTEPROTECT_PLUGIN_URL', plugin_dir_url( __FILE__ ));

if ( is_admin() ) :
	require_once dirname( __FILE__ ) . '/bruteprotect/admin.php';
	new BruteProtect_Admin;
endif;

require_once dirname( __FILE__ ) . '/bruteprotect/clear_transients.php';
// vmg require_once dirname( __FILE__ ) . '/bruteprotect/math-fallback.php';

class BruteProtect
{
	private $user_ip;
	private $use_https;
	private $api_key;
	private $local_host;
	private $api_endpoint;
	private $admin;
	
	function __construct()
	{
		// vmg add_action( 'login_head', array( &$this, 'brute_check_use_math' ) );
		add_action( 'init', array( &$this, 'brute_access_check_generator' ) );
		add_action( 'wp_authenticate', array( &$this, 'brute_check_preauth' ) , 1);
		add_action( 'wp_login_failed', array( &$this, 'brute_log_failed_attempt' ) );
	}
	
	/////////////////////////////////////////////////////////////////////
	// Checks BEFORE authentication so that bots don't get 
	// to go around the log in form.
	/////////////////////////////////////////////////////////////////////
	function brute_check_preauth( $username = 'Not Used By BruteProtect' ) {
		$this->brute_check_loginability( true );
		// vmg $bum = get_site_transient( 'brute_use_math' );
		$bum = 0; // vmg
		
		if( $bum == 1 && isset( $_POST['log'] ) ) :
			
			BruteProtect_Math_Authenticate::brute_math_authenticate();
			
		endif;
	}
	
	function brute_get_ip() {		
		if( isset( $this->user_ip ) )
			return $this->user_ip;
		
		$this->user_ip = trim( $_SERVER[ 'REMOTE_ADDR' ] );
				
		return $this->user_ip;
	}
	
	function get_privacy_key() {
		return substr(md5( NONCE_SALT ), 5, 10);
	}
	
	function brute_access_check_generator() {
		if( !isset( $_GET['bpc'] ) )
			return;
		
		if( $_GET['bpc'] != $this->get_privacy_key() ) 
			return;
		
		require_once dirname( __FILE__ ) . '/bruteprotect/admin.php';
		
		$this->admin = new BruteProtect_Admin;
		
		$can_access = $this->admin->check_bruteprotect_access();
		
	if( $can_access ) {
			wp_die( '<h2 style="clear: both; margin-bottom: 15px;"><img src="' . BRUTEPROTECT_PLUGIN_URL . 'images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; All Clear</h2>Everything is working perfectly, thanks for getting it fixed!' );
		}
		
		$data = $this->admin->get_error_reporting_data();
		
		echo '<h2 style="clear: both; margin-bottom: 15px;"><img src="' . BRUTEPROTECT_PLUGIN_URL . 'images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; Error Report</h2>';
		echo '<h3 style="margin-top: 20px;">Installation Basics:</h3>';
		echo '<strong>WordPress Version</strong>: ' . $data['wp_version'] . '<br />';
		echo '<strong>BruteProtect Version</strong>: ' . BRUTEPROTECT_VERSION . '<br />';
		echo '<strong>BruteProtect API Server</strong>: https://api.bruteprotect.com/<br />';
		echo '<strong>Current Domain</strong>: ' . $this->brute_get_local_host() . '<br />';
		echo '<strong>Current IP</strong>: ' . $this->brute_get_ip() . '<br />';
		echo '<strong>If you can visit this URL, BruteProtect is currently online</strong>: <a href="http://api.bruteprotect.com/up.php">http://api.bruteprotect.com/up.php</a><br />';
		
		echo '<h3 style="margin-top: 20px;">Connection Errors:</h3>';
		/////////////////////////////////////////////////////////////////////
		echo '<pre>';
		print_r($data['error']);
		echo '</pre>';
		/////////////////////////////////////////////////////////////////////
		
		wp_die();
	}
	
	function is_on_localhost() {
		$ip = $this->brute_get_ip();
		
		//Never block login from localhost
		if( $ip == '127.0.0.1' || $ip == '::1' ) {
			return true;
		}
		
		return false;
	}
	
	/////////////////////////////////////////////////////////////////////
	// This function checks the status for a given IP. API results are
	// cached as transients in the wp_options table
	/////////////////////////////////////////////////////////////////////
	function brute_check_loginability( $preauth = false ) {
		$ip = $this->brute_get_ip();

		//Never block login from localhost
		if( $this->is_on_localhost() ) {
			return true;
		}
		
		$transient_name = 'brute_loginable_'.str_replace( '.', '_', $ip );
		$transient_value = get_site_transient( $transient_name );
	
		//Never block login from whitelisted IPs
		$whitelist = get_site_option( 'brute_ip_whitelist' );
		$wl_items = explode( PHP_EOL, $whitelist );
		$iplong = ip2long( $ip );
	
		if( is_array( $wl_items ) ) :  foreach( $wl_items as $item ) :
			
			$item = trim( $item );
			
			if( $ip == $item ) //exact match
				return true;
		
			if(strpos($item, '*') === false) //no match, no wildcard
				continue;
			
			$ip_low = ip2long( str_replace('*', '0', $item) );
			$ip_high = ip2long( str_replace('*', '255', $item) );
		
			if( $iplong >= $ip_low && $iplong <= $ip_high ) //IP is within wildcard range
				return true;
		
		endforeach; endif;

		
		//Check out our transients
		if( isset( $transient_value ) && $transient_value[ 'status' ] == 'ok' ) { return true; }
	
		if( isset( $transient_value ) && $transient_value[ 'status' ] == 'blocked' ) { 
			if( $transient_value[ 'expire' ] < time() ) {
				//the block is expired but the transient didn't go away naturally, clear it out and allow login.
				delete_site_transient( $transient_name );
				return true;
			}
			//there is a current block-- prevent login
			$this->brute_kill_login();
		}
		
		//If we've reached this point, this means that the IP isn't cached.
		//Now we check with the bruteprotect.com servers to see if we should allow login
		$response = $this->brute_call( $action = 'check_ip' );
	
		if( isset( $response[ 'math' ] ) && !function_exists( 'brute_math_authenticate' ) ) {
			// vmg include 'bruteprotect/math-fallback.php';
			
		}
	
		if( $response['status'] == 'blocked' ) {
			$this->brute_kill_login();
		}
	
		return true;
	}
	function brute_check_use_math() {
		// vmg $bp_use_math = get_site_transient( 'brute_use_math' );
	
		if( $bp_use_math ) {
			include 'bruteprotect/math-fallback.php';
			new BruteProtect_Math_Authenticate;
		}
	}

	function brute_kill_login() {
		do_action( 'brute_kill_login', $this->brute_get_ip() );
		wp_die( 'Your IP (' . $this->brute_get_ip() . ') has been flagged for potential security violations.  Please try again in a little while...' );
	}

	function brute_log_failed_attempt() {
		do_action( 'brute_log_failed_attempt' , $this->brute_get_ip() );
		$this->brute_call( 'failed_attempt' );
	}

	function brute_get_local_host() {
		if( isset( $this->local_host ) )
			return $this->local_host;
		
		$uri = 'http://' . strtolower( $_SERVER['HTTP_HOST'] );
	
		if(is_multisite()) {
			$uri = network_home_url();
		}
	
		$uridata = parse_url( $uri );
	
		$domain = $uridata[ 'host' ];
	
		//if we still don't have it, get the site_url
		if ( !$domain ) {
			$uri = get_site_url( 1 );
			$uridata = parse_url( $uri );
			$domain = $uridata[ 'host' ];
		}
	
		if( strpos( $domain, 'www.' ) === 0 ) {
			$ct = 1;
			$domain = str_replace( 'www.', '', $domain, $ct );
		}

		$this->local_host = $domain;

		return $this->local_host;
	}

	function get_bruteprotect_host() {
		if( isset( $this->api_endpoint ) )
			return $this->api_endpoint;
				
		//Some servers can't access https-- we'll check once a day to see if we can.
		$use_https = get_site_transient( 'bruteprotect_use_https' );
		
		if( !$use_https ) {
			$test = wp_remote_get( 'https://api.bruteprotect.com/https_check.php' );
			$use_https = 'no';
			if( !is_wp_error( $test ) && $test['body'] == 'ok' ) {
				$use_https = 'yes';
			}
			set_site_transient( 'bruteprotect_use_https', $use_https, 86400 );
		}
		
		if($use_https == 'yes') {
			$this->api_endpoint = 'https://api.bruteprotect.com/';
		} else {
			$this->api_endpoint = 'http://api.bruteprotect.com/';
		}
		
		return $this->api_endpoint;
	}

	function brute_call( $action = 'check_ip' ) {
		global $wp_version, $wpdb;
		
		$api_key = get_site_option( 'bruteprotect_api_key' );
	
		$brute_ua = "WordPress/{$wp_version} | ";
		$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );
	
		$request['action'] = $action;
		$request['ip'] = $this->brute_get_ip();
		$request['host'] = $this->brute_get_local_host();
		$request['api_key'] = $api_key;
		$request['multisite'] = 0;
		if(is_multisite()) {
			$request['multisite'] = get_blog_count();
			if(!$request['multisite']) {
				$request['multisite'] = $wpdb->get_var("SELECT COUNT(blog_id) as c FROM $wpdb->blogs WHERE spam = '0' AND deleted = '0' and archived = '0'");
			}
		}
	
		$args = array(
			'body' => $request,
			'user-agent' => $brute_ua,
			'httpversion'	=> '1.0',
			'timeout'		=> 15
		);
	
		$response_json = wp_remote_post( $this->get_bruteprotect_host(), $args );
	
		$ip = $_SERVER['REMOTE_ADDR'];
		$transient_name = 'brute_loginable_'.str_replace('.', '_', $ip);
		delete_site_transient($transient_name);
	
		if(is_array($response_json))
			$response = json_decode($response_json['body'], true);

		if(isset($response['status']) && !isset($response['error'])) :
			$response['expire'] = time() + $response['seconds_remaining'];
			set_site_transient($transient_name, $response, $response['seconds_remaining']);
			// vmg delete_site_transient('brute_use_math');
		else :
			//no response from the API host?  Let's use math!
			// vmg set_site_transient('brute_use_math', 1, 600);
			$response['status'] = 'ok';
			$response['math'] = true;
		endif;
	
		if(isset($response['error'])) :
			update_site_option('bruteprotect_error', $response['error']);
		else :
			delete_site_option('bruteprotect_error');
		endif;
	
		return $response;
	}
}

$bruteProtect = new BruteProtect;

if (isset($pagenow) && $pagenow == 'wp-login.php') {
	$bruteProtect->brute_check_loginability();	
} else {
	//	This is in case the wp-login.php pagenow variable fails
	add_action( 'login_head', array( &$bruteProtect, 'brute_check_loginability' ) );
}
