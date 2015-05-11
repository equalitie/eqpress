<?php

if( !class_exists( 'BruteProtect_Admin' ) ) {
	class BruteProtect_Admin extends BruteProtect
	{
		
		private $error_reporting_data;
		public $clef;
	
		function __construct()
		{
			
			$ip = $this->brute_get_ip();
			$key = get_site_option( 'bruteprotect_api_key' );
						
			if( ( $ip == '127.0.0.1' || $ip == '::1' ) && !$key ) {
				add_action( 'admin_notices', array( &$this, 'bruteprotect_localhost_warning' ) );
			}
			
			add_action( 'admin_init', array( &$this, 'check_bruteprotect_access' ) );
			//add_action( 'wp_dashboard_setup', array( &$this, 'bruteprotect_dashboard_widgets' ) );
			//add_action( 'wp_network_dashboard_setup', array( &$this, 'bruteprotect_dashboard_widgets' ) );
			
			//add_filter( 'plugin_action_links', array( &$this, 'bruteprotect_plugin_action_links' ) , 10, 2 );

			//add_action( 'admin_menu', array( &$this, 'bruteprotect_admin_menu_non_multisite' )  );
			//add_action( 'network_admin_menu', array( &$this, 'bruteprotect_admin_menu' )  );
			
			//add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_bruteprotect_admin' ) );

			//add_action( 'admin_menu', array( &$this, 'clef_init') , 0 );
		}
		
		function clef_init() {
			include 'admin/clef/clef_installer.php';
			$this->clef = new BP_Clef;
		}
		
		function enqueue_bruteprotect_admin() {
			wp_enqueue_style( 'bruteprotect-css', plugins_url( '/admin/bruteprotect-admin.css', __FILE__ ), array(), BRUTEPROTECT_VERSION );
		}
		
		function bruteprotect_localhost_warning() {
			echo "
			<div id='bruteprotect-warning' class='updated fade'><p><strong>" . __( 'BruteProtect not enabled.' ) . "</strong> You have installed BruteProtect, but we have detected that you are running it on a local installation.   You can leave BruteProtect turned on, we will prompt you to generate a key when you migrate to a live server.</p></div>";
		}
		
		/////////////////////////////////////////////////////////////////////
		// Some servers are locked down on their ability to use 3rd party
		// APIs.  Let's address the situation head-on.
		// 
		// We check on every admin page load until we're successful, then we
		// just re-check once a week
		/////////////////////////////////////////////////////////////////////
		function check_bruteprotect_access() {
			
			$can_access_host = get_site_transient( 'bruteprotect_can_access_host' );
			if( $can_access_host && ( !isset( $_GET[ 'page' ] ) || $_GET[ 'page' ] != 'bruteprotect-api' ) && !isset( $_GET[ 'bpc' ] ) )
				return true;
			
			
			$test = wp_remote_get( 'http://api.bruteprotect.com/api_check.php' );
			if( !is_wp_error( $test ) && $test['body'] == 'ok' ) : 
				set_site_transient( 'bruteprotect_can_access_host', 1, 604800 );
				return true;
			endif;
	
			global $wp_version;
			$report['wp_version'] = $wp_version;
			$report['error'] = $test;
			$report['server'] = $_SERVER;
			$this->error_reporting_data = base64_encode( serialize( $report ) );
	
			return false;
		}
		
		function get_error_reporting_data() {
			return unserialize( base64_decode ( $this->error_reporting_data ) );
		}



		/////////////////////////////////////////////////////////////////////
		// Admin Dashboard Widget
		/////////////////////////////////////////////////////////////////////
		function bruteprotect_dashboard_widgets() {
	
			if(is_multisite() && !is_network_admin()) {
				$brute_dashboard_widget_hide = get_site_option('brute_dashboard_widget_hide');
				if($brute_dashboard_widget_hide == 1) { return; }
			}
	
			$brute_dashboard_widget_admin_only = get_site_option('brute_dashboard_widget_admin_only');
			if($brute_dashboard_widget_admin_only == 1  && !current_user_can('manage_options')) { return; }
	
			global $wp_meta_boxes;
			wp_add_dashboard_widget( 'bruteprotect_dashboard_widget', 'BruteProtect Stats', array(&$this, 'bruteprotect_dashboard_widget') );
		}

		function bruteprotect_dashboard_widget() {
			$key = get_site_option( 'bruteprotect_api_key' );
			$ckval = get_site_option( 'bruteprotect_ckval' );

			if( $key && !$ckval ) {
				$response = brute_call( 'check_key' );

				if( $response['ckval'] )
					update_site_option( 'bruteprotect_ckval', $response['ckval'] );
			}

			$stats = wp_remote_get( $this->get_bruteprotect_host() . "get_stats.php?key=" . $key );

			if( !is_wp_error( $stats ) ) {
				print_r( $stats['body'] );
				return;
			}
			
			echo '<center><strong>Statistics are currently unavailable.</strong></center>';
		}

		function bruteprotect_plugin_action_links( $links, $file ) {
			if ( $file == plugin_basename( dirname(__FILE__) . '/bruteprotect.php' ) )
				$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=bruteprotect-config' ) ) . '">' . __( 'Settings' ) . '</a>';

			return $links;
		}


		function bruteprotect_admin_menu_non_multisite() {
			if(is_multisite()) {
				add_menu_page( __( 'BruteProtect' ), __( 'BruteProtect' ), 'manage_options', 'bruteprotect-config', array( &$this, 'bruteprotect_conf_ms_notice' ), plugins_url( '/images/menu_icon.png', __FILE__ ) );
				return;
			}
			$this->bruteprotect_admin_menu();
		}
		
		function bruteprotect_admin_menu() {
			add_menu_page( __( 'BruteProtect' ), __( 'BruteProtect' ), 'manage_options', 'bruteprotect-config', array( &$this, 'bruteprotect_general_settings' ), plugins_url( '/images/menu_icon.png', __FILE__ ) );
			
			add_submenu_page( 'bruteprotect-config', __( 'General Settings' ), __( 'General Settings' ), 'manage_options', 'bruteprotect-config', array( &$this, 'bruteprotect_general_settings' ) );
			
			add_submenu_page( 'bruteprotect-config', __( 'API Key' ), __( 'API Key' ), 'manage_options', 'bruteprotect-api', array( &$this, 'bruteprotect_api_key_settings' ) );			
			
			add_submenu_page( 'bruteprotect-config', __( 'IP White List' ), __( 'IP White List' ), 'manage_options', 'bruteprotect-whitelist', array( &$this, 'bruteprotect_whitelist_settings' ) );
		
			if ( is_object( $this->clef ) && !$this->clef->clef_active() ) {
				add_submenu_page( 'bruteprotect-config', __( 'Clef' ), __( 'Clef' ), 'manage_options', 'bruteprotect-clef', array( &$this->clef, 'display_settings' ) );
			}
			
			$key = get_site_option( 'bruteprotect_api_key' );
			$error = get_site_option( 'bruteprotect_error' );

			if ( !$key ) {
				add_action( 'admin_notices', array( &$this, 'bruteprotect_warning' ) );
				return;
			} elseif ( $error && isset( $_GET['page'] ) && $_GET['page'] != 'bruteprotect-api' ) {
				add_action( 'admin_notices', array( &$this, 'bruteprotect_invalid_key_warning' ) );
				return;
			}
		}

		function bruteprotect_warning() {
			//Don't trigger the warning on the config page
			if ( isset( $_GET['page'] ) && 'bruteprotect-api' == $_GET['page'] )
				return;
			
			$ip = $this->brute_get_ip();
			//Don't trigger the warning on localhost, since we're not going to let them set up the API yet anyway...
			if( $ip == '127.0.0.1' || $ip == '::1' )
				return;
			
			
			echo "<div id='bruteprotect-warning' class='error fade'><p><strong>" . __( 'BruteProtect is almost ready.' ) . "</strong> " . sprintf( __( 'You must <a href="%1$s">enter your BruteProtect API key</a> for it to work.  <a href="%1$s">Obtain a key for free</a>.' ), esc_url( admin_url( 'admin.php?page=bruteprotect-api' ) ) ) . "</p></div>
			";
		}
		
		function bruteprotect_invalid_key_warning() {
			echo "
			<div id='bruteprotect-warning' class='error fade'><p><strong>" . __( 'There is a problem with your BruteProtect API key' ) . "</strong> " . sprintf( __( ' <a href="%1$s">Please correct the error</a>, your site will not be protected until you do.' ), esc_url( admin_url( 'admin.php?page=bruteprotect-api' ) ) )."</p></div>
			";
		}

		function bruteprotect_general_settings() {
			include 'admin/settings.php';
		}

		function bruteprotect_api_key_settings() {
			include 'admin/api_key_settings.php';
		}

		function bruteprotect_whitelist_settings() {
			include 'admin/whitelist.php';
		}

		function bruteprotect_conf_ms_notice() {
			?>
			<div class="wrap">
				<h2 style="clear: both; margin-bottom: 15px;"><img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; General Settings</h2>
				<p style="font-size: 18px; padding-top: 20px;">
				<?php if (current_user_can('manage_network')): ?>
					<strong>BruteProtect only needs one API key per network.</strong>  <a href="<?php echo network_home_url('/wp-admin/network/admin.php?page=bruteprotect-config') ?>">Manage your key here.</a>
				<?php else: ?>
					<strong>Sorry!</strong> Only super admins can configure BruteProtect.
				<?php endif ?>
				</p>
			</div>
			<?php 
		}


	}
}
