<?php
/*
Plugin Name: easyPress Console
Text Domain: easypress
Description: The easyPress console.
Author: easyPress.ca
Author URI: http://easypress.ca
Plugin URI: http://easypress.ca
Version: 1.4.1
*/


class Easy_Press_Console {
	
    static function init() {
        if ( current_user_can( 'manage_options' ) )  {
            if ( ( is_multisite() && is_super_admin() ) || !is_multisite() ) {
                add_action( 'admin_menu', array( __CLASS__,	'add_sidebar_menu' ) );
                add_action( 'admin_init', array( __CLASS__, 'load_css_js' ) );
                require_once ( dirname( __FILE__ ) . '/inc/class-curl-request.php' );
                require_once ( dirname( __FILE__ ) . '/inc/easypress-console-text.php' );
            }
        }
    }
	
	/**
	 * Add sidebar menu and submenus
	 *
	 */
	static function add_sidebar_menu() {
		//global $hook_suffix;
		
		add_menu_page( 'easyPress Console', 'easyPress', 'manage_options', 'ep_dashboard', array( __CLASS__, 'easypress_console_mainpage' ), plugins_url( 'img/easypress-bolt.png', __FILE__ ), 1 );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Overview', 'Overview', 'manage_options', 'ep_dashboard' );

		add_submenu_page( 'ep_dashboard', 'easyPress Console - Website Stats', 'Website Stats', 'manage_options', 'ep_webstats', array( __CLASS__, 'easypress_console_webstats' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Manage Cache', 'Manage Cache', 'manage_options', 'ep_cache', array( __CLASS__, 'easypress_console_cache' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Logs', 'View Logs', 'manage_options', 'ep_logs', array( __CLASS__, 'easypress_console_logs' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Permissions', 'File Permissions', 'manage_options', 'ep_perms', array( __CLASS__, 'easypress_console_perms' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Lockdown', 'Security Lockdown', 'manage_options', 'ep_lockdown', array( __CLASS__, 'easypress_console_lockdown' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Reset Password', 'Reset Password', 'manage_options', 'ep_sftp', array( __CLASS__, 'easypress_console_sftp_stuff' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Administration Over SSL', 'Admin Over SSL', 'manage_options', 'ep_ssl', array( __CLASS__, 'easypress_console_wpadmin_ssl' ) );
		
		add_submenu_page( 'ep_dashboard', 'easyPress Console - Plugin and Theme Editor', 'Code Editor', 'manage_options', 'ep_editor', array( __CLASS__, 'easypress_console_editor' ) );
		
		//add_action('admin_print_scripts-' . $hook_suffix, 'my_plugin_admin_scripts');
	}
	
	/**
	 * Load stylesheets and javascript
	 *
	 */
	static function load_css_js() {
		$plugin = get_plugin_data(__FILE__);
		wp_register_style( 'ep_style', plugins_url('css/style.css', __FILE__), array(), $plugin['Version'] );
		wp_enqueue_style('ep_style');
		wp_register_script( 'ep-countdown-script', plugins_url( 'js/countdown.js', __FILE__ ), array(), $plugin['Version'] );
		wp_enqueue_script('ep-countdown-script');
	}
	
	/**
	 * easyPress Console dashboard page
	 *
	 */
	static function easypress_console_mainpage( ) {
		//global $hook_suffix;
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>easyPress Console</h2>';
		echo '<h3>This page provides an overview of the easyPress Console.</h3>';
		//echo "<p><strong>The hook suffix is $hook_suffix</strong></p>";
		ep_console_docs_website_stats();
		ep_console_docs_manage_cache();
		ep_console_docs_view_logs();
		ep_console_docs_file_permissions();
		ep_console_docs_security_lockdown();
		ep_console_docs_sftp_stuff();
		ep_console_docs_wpadmin_ssl();
		ep_console_docs_editor();
		echo '</div>';
	}
	
	/**
	 * easyPress Console Website Stats page
	 *
	 */
	static function easypress_console_webstats() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		$stat_warning = <<<'EOW'
		<p id="stat-warning">The following stats will differ significantly from what you see on Google Analytics. This is normal. <a href="https://support.google.com/analytics/answer/1009616?hl=en" target="_blank">Click here to understand why.</a></p>
EOW;
		require dirname(__FILE__) . '/inc/class-http-log-parser.php';
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>Website Stats</h2>';
		ep_console_docs_website_stats();
		//echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_webstats&stats_today=yes">Show Today\'s Web Stats</a></p>';
		echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_webstats&stats_month=yes">Show Web Stats</a></p>';
		if ( isset($_GET['stats_today']) && $_GET['stats_today'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=stats&api_key=' . EP_API_KEY );
			echo $stat_warning;
			echo $pret['body'];
		}
		if ( isset($_GET['stats_month']) && $_GET['stats_month'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=stats_month&api_key=' . EP_API_KEY );
			echo $stat_warning;
			echo $pret['body'];
		}
		echo '</div>';
	}
	
	/**
	 * easyPress Console cache management page
	 *
	 */
	static function easypress_console_cache() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>Manage Website Cache</h2>'; //cmohr
		ep_console_docs_manage_cache();
		$domain =  $_SERVER['SERVER_NAME'];
		if ( isset($_GET['purge_cache']) && $_GET['purge_cache'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=cache&purge=yes&api_key=' . EP_API_KEY );
			echo $pret['body'];
		} else {
			$pret = self::surf( 'domain=' . $domain . '&do=cache&purge=no&api_key=' . EP_API_KEY );
			echo $pret['body'];
		}
		echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_cache&purge_cache=yes">Delete Cache</a></p>';
		echo '</div>';
	}
	
	/**
	 * easyPress Console website and PHP logs
	 *
	 */
	static function easypress_console_logs() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>View Web and PHP Logs</h2>'; //cmohr
		ep_console_docs_view_logs();
		echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_logs&pl=yes">View PHP Error Log</a></p>';
		echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_logs&wl=yes">View Web Access Log</a></p>';
		echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_logs&we=yes">View Web Error Log</a></p>';
		
		if ( isset($_GET['pl']) && $_GET['pl'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=logs&log=pe&api_key=' . EP_API_KEY );
			echo $pret['body'];
		}
		if ( isset($_GET['wl']) && $_GET['wl'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=logs&log=wa&api_key=' . EP_API_KEY );
			echo $pret['body'];
		}
		if ( isset($_GET['we']) && $_GET['we'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=logs&log=we&api_key=' . EP_API_KEY );
			echo $pret['body'];
		}
		echo '</div>';
	}
	
	/**
	 * Set file and directory ownership and permissions back to default.
	 *
	 */
	static function easypress_console_perms() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>Reset File Permissions and Ownership</h2>';
		ep_console_docs_file_permissions();
		echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_perms&reset=yes">Reset Now</a></p>';
		if ( isset($_GET['reset']) && $_GET['reset'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=perms&api_key=' . EP_API_KEY );
			echo $pret['body'];
		}
		echo '</div>';
	}
	
	/**
	 * Lockdown
	 * 
	 * Set file and directory ownership and permissions so that they're owned by the customer's unix user and
	 * not writable by the web server user.
	 *
	 */
	static function easypress_console_lockdown() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>Security Lockdown</h2>';
		ep_console_docs_security_lockdown();
		echo '<p id="lock-stat">The site is currently ';
		if ( 33 != fileowner( $_SERVER['DOCUMENT_ROOT'] ) ) {
			echo 'LOCKED DOWN</p>';
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_lockdown&lockdown=no">Undo Lockdown</a></p>';
		} else {
			echo 'UNLOCKED</p>';
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_lockdown&lockdown=yes">Lockdown</a></p>';
		}
		if ( isset($_GET['lockdown']) && $_GET['lockdown'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=lockdown&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_lockdown";</script>';
				echo '<strong id="show-time">60</strong> seconds';
			}
		} else if ( isset($_GET['lockdown']) && $_GET['lockdown'] == 'no' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=undo_lockdown&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_lockdown";</script>';
				echo '<strong id="show-time">60</strong> seconds';
			}
		}
		echo '</div>';
	}

	/**
	 * Reset Password
	 *
	 * Reset SFTP password
	 *
	 */
	static function easypress_console_sftp_stuff() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>SFTP Info and Password Reset</h2>';
        echo '<h3>SFTP Login Info</h3>';
		echo '<p class="console-user">Your SFTP username is: <b>' . DB_USER . '</b></p>';
        echo '<p class="console-user">Your SFTP host is: <b>' . $_SERVER['HTTP_HOST'] . '</b></p>';
		if ( isset($_GET['reset_passwd']) && $_GET['reset_passwd'] == 'yes' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=reset_passwd&api_key=' . EP_API_KEY );
            echo $pret['body'];
		} else {
			echo "<p>By clicking the button below your SFTP password will be reset and emailed to the site adminstrator's";
			echo ' email address <b>' . get_option( 'admin_email' ) . '</b>.</p>';
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_sftp&reset_passwd=yes">Reset Password</a></p>';
		}
		echo '</div>';
	}

	/**
	 *  Enable / disable SSL to admin screens
	 *
	 */
	static function easypress_console_wpadmin_ssl() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>Adminstration Over SSL</h2>';
		ep_console_docs_wpadmin_ssl();
		echo '<p id="ssl-stat">The site is currently using ';
		if ( defined( 'FORCE_SSL_ADMIN' ) && true === FORCE_SSL_ADMIN ) {
			echo "<strong>SSL for logins and SSL for admin access.</strong> Use the following buttons to change your settings.</p>";
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_ssl&ssl_state=login">SSL for Logins Only</a></p>';
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_ssl&ssl_state=off">Disable SSL</a></p>';
		} else if ( defined( 'FORCE_SSL_LOGIN' ) && true === FORCE_SSL_LOGIN ) {
			echo "<strong>SSL for logins only.</strong> Use the following buttons to change your settings.</p>";
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_ssl&ssl_state=admin">SSL for Logins and Admin</a></p>';
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_ssl&ssl_state=off">Disable SSL</a></p>';
		} else {
			echo "<strong>no SSL for administration.</strong> Use the following buttons to change your settings.</p>";
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_ssl&ssl_state=login">SSL for Logins Only</a></p>';
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_ssl&ssl_state=admin">SSL for Logins and Admin</a></p>';
		}
		if ( isset($_GET['ssl_state']) && $_GET['ssl_state'] == 'login' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=wpadmin_ssl&ssl_state=login&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_ssl";</script>';
				echo '<p>New settings will take effect in <strong id="show-time">10</strong> seconds. You might need to log in again.</p>';
			}
		} else if ( isset($_GET['ssl_state']) && $_GET['ssl_state'] == 'off' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=wpadmin_ssl&ssl_state=off&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_ssl";</script>';
				echo '<p>New settings will take effect in <strong id="show-time">10</strong> seconds. You might need to log in again.</p>';
			}
		} else if ( isset($_GET['ssl_state']) && $_GET['ssl_state'] == 'admin' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=wpadmin_ssl&ssl_state=admin&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_ssl";</script>';
				echo '<p>New settings will take effect in <strong id="show-time">10</strong> seconds. You might need to log in again.</p>';
			}
		}
		echo '</div>';

	}

	/**
	 *  Enable / disable CSS and PHP code editor.
	 *
	 */
	static function easypress_console_editor() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$domain =  $_SERVER['SERVER_NAME'];
		echo '<div class="wrap">'; //cmohr
		echo '<div id="easy-edit-icon" class="icon32"><img src="' . self::easypress_console_logo_url() . '">';
		echo '<br /></div>';
		echo '<h2>Plugin and Theme Editor</h2>';
		ep_console_docs_editor();
		echo '<p id="ssl-stat">The site is currently ';
		if ( defined( 'DISALLOW_FILE_EDIT' ) && true === DISALLOW_FILE_EDIT ) {
			echo "<strong>NOT allowing</strong> plugin and theme edits via the admin screens.</p>";
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_editor&edits=on">Enable Editing</a></p>';
		} else {
			echo "<strong>ALLOWING</strong> plugin and theme edits via the admin screens.</p>";
			echo '<p><a class="button" href="' . admin_url() . 'admin.php?page=ep_editor&edits=off">Disable Editing</a></p>';
		}
		if ( isset($_GET['edits']) && $_GET['edits'] == 'on' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=editor&edits=on&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_editor";</script>';
				echo '<p>New settings will take effect in <strong id="show-time">10</strong> seconds.</p>';
			}
		} else if ( isset($_GET['edits']) && $_GET['edits'] == 'off' ) {
			$pret = self::surf( 'domain=' . $domain . '&do=editor&edits=off&api_key=' . EP_API_KEY );
			echo $pret['body'];
			if ( '200' == $pret['code'] ) {
				echo '<script>swp_url = "/wp-admin/admin.php?page=ep_editor";</script>';
				echo '<p>New settings will take effect in <strong id="show-time">10</strong> seconds.</p>';
			}
		}
		echo '</div>';
	}

	/**
	 * logo URL
	 *
	 */
	static function easypress_console_logo_url() {
		return plugins_url('img/easyPress-logo.png', __FILE__);
	}

	/**
	 * Connect to the proxy.
	 *
	 * @param string $url is the full URL containing the hostname and request.
	 * @param string $host is the hostname of the new site used in the Host: HTTP header.
	 * @param string $method is the HTTP method (GET | POST).
	 * @param string $post_params are the POST arguments in key=value&key=value format.
	 *
	 * @return array containing HTTP response code, the body of the response, elapsed time.
	 */
	static function surf( $post_params ) {
		try {
			$mych = new CurlRequest;
			$params = array( 'url' => 'http://localhost/proxy/easypress-console-proxy.php',
							'host' => 'console.easypress.ca',
							'header' => '',
							'method' => 'POST',
							'referer' => '',
							'cookie' => '',
							'post_fields' => $post_params,
							'timeout' => 90,
							'verbose' => 0 );
	
			$mych->init( $params );
			$result = $mych->exec();
			if ( $result['curl_error'] ) throw new Exception( $result['curl_error'] );
			if ( $result['http_code'] != '200' ) throw new Exception( "HTTP Code = " . $result['http_code'] . "\nBody: " . $result['body'] );
			if ( NULL === $result['body'] ) throw new Exception( "Body of file is empty" );
			//echo $result['header'];
			//echo 'HTTP return code: ' . $result['http_code'];
		}
		catch ( Exception $e ) {
			error_log( "easypress-console::surf(): " . $e->getMessage() );
		}
		return array( 'code' => $result['http_code'], 'body' => $result['body'], 'etime' => $result['etime'] );
	}


}
	
add_action( 'plugins_loaded', array( 'Easy_Press_Console',	'init' ) );
