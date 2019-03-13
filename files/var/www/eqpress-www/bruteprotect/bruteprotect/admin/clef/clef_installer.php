<?php
/**
* 
*/
class BP_Clef extends BruteProtect
{
	
	function __construct()
	{
		add_action( 'admin_init', array( &$this, 'admin_page_load') , 0 );
	}
	
	function clef_active() {
		return is_plugin_active( 'wpclef/wpclef.php' );
	}
	
	function admin_page_load() {
		
		// If they don't want to install, we can go away.  Doing this before the active check because it's lighter weight
		if ( !isset( $_GET[ 'bruteprotect-clef-action' ] ) || $_GET[ 'bruteprotect-clef-action' ] !== 'install' ) 
			return;
		
		// Clef is already active.  Yay!
		if ( $this->clef_active() )
			return;
		
		// Bad nonce
		if ( !wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'bruteprotect-clef-install' ) )
		     wp_die( 'Unauthorized' );
		
		// Okay, let's install
		$this->install_and_activate();
		
	}
	
	function display_settings() {
        $url = wp_nonce_url(
            add_query_arg(
                array(
                    'page'          => 'bruteprotect-clef',
                    'bruteprotect-clef-action' => 'install',
                ),
                admin_url( 'admin.php' )
            ),
            'bruteprotect-clef-install'
        );

		include 'clef_settings.php';
	}

	function install_and_activate() {

		$clef_path = 'wpclef/wpclef.php';

		$plugins = get_plugins();

		if ( !isset( $plugins[ $clef_path ] ) ) :
			$this->install();
		endif; //end install process

		$activate = activate_plugin( $clef_path );
		
		if ( is_wp_error( $activate ) ) :
			$this->clef_install_errors = array( $activate->get_error_message() );
			add_action( 'admin_notices', array( &$this, 'clef_install_errors' ) );
			return;
		else :
			return wp_redirect( 'plugins.php' );
		endif;
	}
	
	function install() {
		 
		$plugin = array(
			'name' => 'Clef',
			'slug' => 'wpclef',
		);

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // Need for plugins_api
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'; // Need for upgrade classes
		require_once 'plugin-install.php';

		$api = plugins_api( 'plugin_information', array( 'slug' => $plugin['slug'], 'fields' => array( 'sections' => false ) ) );

        if ( is_wp_error( $api ) ) :
            $this->clef_install_errors = array( $api->get_error_message() );
			add_action( 'admin_notices', array( &$this, 'clef_install_errors' ) );
            return;
		elseif ( isset( $api->download_link ) ) :
            $plugin['source'] = $api->download_link;
		else :
			$this->clef_install_errors = array( 'Error trying to download Clef' );
			add_action( 'admin_notices', array( &$this, 'clef_install_errors' ) );
            return;
		endif;

        /** Pass all necessary information via URL if WP_Filesystem is needed */
        $url = wp_nonce_url(
            add_query_arg(
                array(
                    'page' => 'bruteprotect-clef',
                    'bruteprotect-clef-action' => 'install',
                ),
                admin_url( 'admin.php' )
            ),
            'bruteprotect-clef-install'
        );
		
        $method = ''; // Leave blank so WP_Filesystem can populate it as necessary
        $fields = array( sanitize_key( 'bruteprotect-clef-install' ) ); // Extra fields to pass to WP_Filesystem

        if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $fields ) ) ) {
            return;
        }

        if ( ! WP_Filesystem( $creds ) ) {
            request_filesystem_credentials( $url, $method, true, false, $fields ); // Setup WP_Filesystem
            return;
        }

        /** Set type, based on whether the source starts with http:// or https:// */
        $type = preg_match( '|^http(s)?://|', $plugin['source'] ) ? 'web' : 'upload';

        /** Prep variables for Plugin_Installer_Skin class */
        $title = sprintf( 'Installing %s', $plugin['name'] );
        $url   = add_query_arg( array( 'action' => 'install-plugin', 'plugin' => $plugin['slug'] ), 'update.php' );
        if ( isset( $_GET['from'] ) )
            $url .= add_query_arg( 'from', urlencode( stripslashes( $_GET['from'] ) ), $url );

        $nonce = 'install-plugin_' . $plugin['slug'];

        $source = $plugin['source'];

        /** Create a new instance of Plugin_Upgrader */
        $upgrader = new Plugin_Upgrader( $skin = new Silent_Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce', 'plugin', 'api' ) ) );

        /** Perform the action and install the plugin from the $source urldecode() */
        $upgrader->install( $source );

        if (!empty($skin->errors)) {
        	$this->clef_install_errors = $skin->errors;
			add_action( 'admin_notices', array( &$this, 'clef_install_errors' ) );
			return;
        }

        /** Flush plugins cache so we can make sure that the installed plugins list is always up to date */
        wp_cache_flush();
	}

	function clef_install_errors() {
		foreach ($this->clef_install_errors as $error) {
			echo '<div id="bruteprotect-warning" class="error fade"><p>Something went wrong activating Clef: <strong>' . __( $error ) . '</strong></p></div>';
		}
	}
}
