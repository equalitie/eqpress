<?php
$host = $this->brute_get_local_host();
global $current_user;

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'get_api_key' && is_email( $_POST['email_address'] ) ) {
	global $wp_version;

	$post_host = $this->get_bruteprotect_host() . '/get_key.php';
	$brute_ua = "WordPress/{$wp_version} | ";
	$brute_ua .= 'BruteProtect/' . constant( 'BRUTEPROTECT_VERSION' );

	$request['email'] = $_POST['email_address'];
	$request['site'] = $host;

	$args = array(
		'body'        => $request,
		'user-agent'  => $brute_ua,
		'httpversion' => '1.0',
		'timeout'     => 15
	);

	$response_json = wp_remote_post( $post_host, $args );

	?>
	<script type="text/javascript">
	<!--
	window.location = "admin.php?page=bruteprotect-config&get_key=success"
	//-->
	</script>
	<?php
	exit;
}

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_key' )
	update_site_option( 'bruteprotect_api_key', $_POST['brute_api_key'] );

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_brute_dashboard_widget_settings' )
	update_site_option( 'brute_dashboard_widget_hide', $_POST['brute_dashboard_widget_hide'] );

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_brute_dashboard_widget_settings_2' )
	update_site_option( 'brute_dashboard_widget_admin_only', $_POST['brute_dashboard_widget_admin_only'] );


$brute_dashboard_widget_hide = get_site_option('brute_dashboard_widget_hide');
$brute_dashboard_widget_admin_only = get_site_option('brute_dashboard_widget_admin_only');


$key = get_site_option( 'bruteprotect_api_key' );
$invalid_key = false;
delete_site_option( 'bruteprotect_error' );

$response = $this->brute_call( 'check_key' );

if(isset($response['error'])) :
	if( $response['error'] == 'Invalid API Key' || $response['error'] == 'API Key Required' ) :
		$invalid_key = 'invalid';
	endif;
	if( $response['error'] == 'Host match error' ) :
		$invalid_key = 'host';
	endif;
endif;

if( !$this->check_bruteprotect_access() ) : //server cannot access API
	$invalid_key = 'server_access';
endif;

if( isset($response['ckval']) )
	update_site_option( 'bruteprotect_ckval', $response['ckval'] );
?>
<div class="wrap">
<h2 style="clear: both; margin-bottom: 15px;"><img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; General Settings</h2>

<?php if ( false != $key && $invalid_key == 'invalid' ) : ?>
	<div class="error below-h2" id="message"><p><?php _e( '<strong>Invalid API Key!</strong> You have entered an invalid API key. Please copy and paste it from the email you have received, or request a new key.' ); ?></p></div>
<?php endif ?>

<?php if ( false != $key && $invalid_key == 'host' ) : ?>
	<div class="error below-h2" id="message"><p><?php _e( '<strong>Invalid API Key!</strong> You have entered an API key which is not valid for this server.  Every site must have its own API key.' ); ?></p></div>
<?php endif ?>

<?php if ( $invalid_key == 'server_access' ) : 
	include 'inc/api_access_error.php';
	return; 
endif; ?>


<?php if (is_multisite()): ?>
	<br class="clear" />
	<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #ccc; background-color: #e5e5e5; margin-top: 30px;">
		<h3 style="display: block; background-color: #555; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'Dashboard Widget Display' ); ?></h3>
		<form action="" method="post">
			<strong><?php _e( 'Display BruteProtect statistics: ' ); ?></strong><br />
			<select name="brute_dashboard_widget_hide" id="brute_dashboard_widget_hide">
				<option value="0">On network admin dashboard and on all blog dashboards</option>
				<option value="1" <?php if (isset($brute_dashboard_widget_hide) && $brute_dashboard_widget_hide == 1) { echo 'selected="selected"'; } ?>>On network admin dashboard only</option>
			</select>
			<input type="hidden" name="brute_action" value="update_brute_dashboard_widget_settings" /><br />
			<input type="submit" value="Save" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
		</form>
	</div>
<?php endif ?>
<?php if ( current_user_can('manage_options') ) : ?>
	<br class="clear" />
	<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #ccc; background-color: #e5e5e5;">
		<h3 style="display: block; background-color: #555; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'Dashboard Widget Display' ); ?></h3>
		<form action="" method="post">
			<strong><?php _e( 'BruteProtect statistics display to: ' ); ?></strong><br />
			<select name="brute_dashboard_widget_admin_only" id="brute_dashboard_widget_admin_only">
				<option value="0">All users who can see the dashboard</option>
				<option value="1" <?php if (isset($brute_dashboard_widget_admin_only) && $brute_dashboard_widget_admin_only == 1) { echo 'selected="selected"'; } ?>>Admins Only</option>
			</select>
			<input type="hidden" name="brute_action" value="update_brute_dashboard_widget_settings_2" /><br />
			<input type="submit" value="Save" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
		</form>
	</div>
<?php endif ?>
<div style="clear: both;">
	&nbsp;
</div>
<h3>We know it's looking a little empty here right now, but we're working on some cool new stuff that's going to land here, so stay tuned!</h3>

</div>