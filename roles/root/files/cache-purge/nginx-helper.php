<?php
/*
  Plugin Name: Nginx Helper
  Plugin URI: http://rtcamp.com/nginx-helper/
  Description: An nginx helper that serves various functions.
  Version: 1.7.5
  Author: rtCamp
  Author URI: http://rtcamp.com
  Requires at least: 3.0
  Tested up to: 3.6
 */
namespace rtCamp\WP\Nginx {
	define( 'rtCamp\WP\Nginx\RT_WP_NGINX_HELPER_PATH', plugin_dir_path( __FILE__ ) );

	class Helper {

		var $minium_WP = '3.0';
		var $options = null;

		function __construct() {

			$this->load_options();
			add_action( 'init', array( &$this, 'start_helper' ), 15 );
		}

		function start_helper() {

			global $rt_wp_nginx_purger;
			add_action( 'add_init', array( &$this, 'update_map' ) );

			add_action( 'publish_post', array( &$rt_wp_nginx_purger, 'purgePost' ), 200, 1 );
			add_action( 'publish_page', array( &$rt_wp_nginx_purger, 'purgePost' ), 200, 1 );
			add_action( 'wp_insert_comment', array( &$rt_wp_nginx_purger, 'purgePostOnComment' ), 200, 2 );
			add_action( 'transition_comment_status', array( &$rt_wp_nginx_purger, 'purgePostOnCommentChange' ), 200, 3 );

			$args = array( '_builtin' => false );
			$_rt_custom_post_types = get_post_types( $args );
			if ( isset( $post_types ) && ! empty( $post_types ) ) {
				if ( $this->options[ 'rt_wp_custom_post_types' ] == true ) {
					foreach ( $_rt_custom_post_types as $post_type ) {
						add_action( 'publish_' . trim( $post_type ), array( &$rt_wp_nginx_purger, 'purgePost' ), 200, 1 );
					}
				}
			}

			add_action( 'transition_post_status', array( &$this, 'set_future_post_option_on_future_status' ), 20, 3 );
			add_action( 'delete_post', array( &$this, 'unset_future_post_option_on_delete' ), 20, 1 );

			add_action( 'edit_attachment', array( &$rt_wp_nginx_purger, 'purgeImageOnEdit' ), 100, 1 );

			//add_action( 'wpmu_new_blog', array( &$this, 'update_new_blog_options' ), 10, 1 );

			add_action( 'transition_post_status', array( &$rt_wp_nginx_purger, 'purge_on_post_moved_to_trash' ), 20, 3 );

			add_action( 'edit_term', array( &$rt_wp_nginx_purger, 'purge_on_term_taxonomy_edited' ), 20, 3 );
			add_action( 'delete_term', array( &$rt_wp_nginx_purger, 'purge_on_term_taxonomy_edited' ), 20, 3 );

			add_action( 'check_ajax_referer', array( &$rt_wp_nginx_purger, 'purge_on_check_ajax_referer' ), 20, 2 );
		}

		function load_options() {
			//$this->options = get_site_option( 'rt_wp_nginx_helper_options' );
			$this->options = json_decode( EP_CACHE_OPTIONS, true );
		}

		function set_future_post_option_on_future_status( $new_status, $old_status, $post ) {

			global $blog_id, $rt_wp_nginx_purger;
			if ( ! $this->options[ 'enable_purge' ] ) {
				return;
			}
			if ( $old_status != $new_status
					&& $old_status != 'inherit'
					&& $new_status != 'inherit'
					&& $old_status != 'auto-draft'
					&& $new_status != 'auto-draft'
					&& $new_status != 'publish'
					&& ! wp_is_post_revision( $post->ID ) ) {
				$rt_wp_nginx_purger->log( "Purge post on transition post STATUS from " . $old_status . " to " . $new_status );
				$rt_wp_nginx_purger->purgePost( $post->ID );
			}

			if ( $new_status == 'future' ) {
				if ( $post && $post->post_status == 'future' && ( ( $post->post_type == 'post' || $post->post_type == 'page' ) || ( in_array( $post->post_type, $this->options[ 'custom_post_types_recognized' ] ) ) ) ) {
					$rt_wp_nginx_purger->log( "Set/update future_posts option (post id = " . $post->ID . " and blog id = " . $blog_id . ")" );
					$this->options[ 'future_posts' ][ $blog_id ][ $post->ID ] = strtotime( $post->post_date_gmt ) + 60;
					update_site_option( "rt_wp_nginx_helper_global_options", $this->options );
				}
			}
		}

		function unset_future_post_option_on_delete( $post_id ) {

			global $blog_id, $rt_wp_nginx_purger;
			if ( ! $this->options[ 'enable_purge' ] ) {
				return;
			}
			if ( $post_id && ! wp_is_post_revision( $post_id ) ) {

				if ( isset( $this->options[ 'future_posts' ][ $blog_id ][ $post_id ] ) && count( $this->options[ 'future_posts' ][ $blog_id ][ $post_id ] ) ) {
					$rt_wp_nginx_purger->log( "Unset future_posts option (post id = " . $post_id . " and blog id = " . $blog_id . ")" );
					unset( $this->options[ 'future_posts' ][ $blog_id ][ $post_id ] );
					update_site_option( "rt_wp_nginx_helper_global_options", $this->options );

					if ( ! count( $this->options[ 'future_posts' ][ $blog_id ] ) ) {
						unset( $this->options[ 'future_posts' ][ $blog_id ] );
						update_site_option( "rt_wp_nginx_helper_global_options", $this->options );
					}
				}
			}
		}

		function update_new_blog_options( $blog_id ) {

			global $rt_wp_nginx_purger;

			include_once (RT_WP_NGINX_HELPER_PATH . 'admin/install.php');

			$rt_wp_nginx_purger->log( "New site added (id $blog_id)" );

			$this->update_map();

			$rt_wp_nginx_purger->log( "New site added to nginx map (id $blog_id)" );

			$helper_options = rt_wp_nginx_helper_get_options();

			update_blog_option( $blog_id, "rt_wp_nginx_helper_options", $helper_options );

			$rt_wp_nginx_purger->log( "Default options updated for the new blog (id $blog_id)" );
		}

		function get_map() {
			if ( ! $this->options[ 'enable_map' ] ) {
				return;
			}

			if ( is_multisite() ) {

				global $wpdb;

				$rt_all_blogs = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id, domain, path FROM " . $wpdb->blogs . " WHERE site_id = %d AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'", $wpdb->siteid ) );
				$wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';
				if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->dmtable}'" ) == $wpdb->dmtable ) {
					$rt_domain_map_sites = $wpdb->get_results( "SELECT blog_id, domain FROM {$wpdb->dmtable} ORDER BY id DESC" );
				}
				$rt_nginx_map = "";
				$rt_nginx_map_array = array( );


				if ( $rt_all_blogs )
					foreach ( $rt_all_blogs as $blog ) {
						if ( SUBDOMAIN_INSTALL == "yes" ) {
							$rt_nginx_map_array[ $blog->domain ] = $blog->blog_id;
						} else {
							if ( $blog->blog_id != 1 ) {
								$rt_nginx_map_array[ $blog->path ] = $blog->blog_id;
							}
						}
					}

				if ( $rt_domain_map_sites ) {
					foreach ( $rt_domain_map_sites as $site ) {
						$rt_nginx_map_array[ $site->domain ] = $site->blog_id;
					}
				}

				foreach ( $rt_nginx_map_array as $domain => $domain_id ) {
					$rt_nginx_map .= "\t" . $domain . "\t" . $domain_id . ";\n";
				}

				return $rt_nginx_map;
			}
		}

		function functional_asset_path(){
			$dir = wp_upload_dir();
			$path = $dir['basedir'].'/nginx-helper/';
			return $path;
		}

		function functional_asset_url(){
			$dir = wp_upload_dir();
			$url = $dir['baseurl'].'/nginx-helper/';

			return $url;
		}

		function update_map() {
			if ( is_multisite() ) {
				$rt_nginx_map = $this->get_map();

				if ( $fp = fopen( $this->functional_asset_path() . 'map.conf', 'w+' ) ) {
					fwrite( $fp, $rt_nginx_map );
					fclose( $fp );
					return true;
				}
			}
		}
	}

}

namespace {

	global $current_blog;

	require_once (rtCamp\WP\Nginx\RT_WP_NGINX_HELPER_PATH . 'nginx-helper/purger.php');

	global $rt_wp_nginx_helper, $rt_wp_nginx_purger;
	$rt_wp_nginx_helper = new \rtCamp\WP\Nginx\Helper;
	$rt_wp_nginx_purger = new \rtCamp\WP\Nginx\Purger;
	
	// For compatibility with several plugins and nginx HTTPS proxying schemes
	if ( empty( $_SERVER[ 'HTTPS' ] ) || 'off' == $_SERVER[ 'HTTPS' ] ) {
		unset( $_SERVER[ 'HTTPS' ] );
	}
	
	if ( ! function_exists( 'wp_redirect' ) ) {
		function wp_redirect( $location, $status = 302 ) {
			$location = apply_filters( 'wp_redirect', $location, $status );

			if ( empty( $location ) ) {
				return false;
			}

			$status = apply_filters( 'wp_redirect_status', $status, $location );
			if ( $status < 300 || $status > 399 ) {
				$status = 302;
			}

            if (function_exists('wp_sanitize_redirect')) {
                $location = wp_sanitize_redirect($location);
            }

            header( 'Location: ' . $location, true, $status );
		}
	}
}
?>
