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
window.location = "admin.php?page=bruteprotect-api&get_key=success"
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

if ( isset( $_POST['brute_action'] ) && $_POST['brute_action'] == 'update_brute_whitelist' ) {
	//check the whitelist to make sure that it's clean
	$whitelist = $_POST['brute_ip_whitelist'];

	$wl_items = explode(PHP_EOL, $whitelist);

	if( is_array( $wl_items ) ) :  foreach( $wl_items as $key => $item ) :
		$item = trim( $item );
		$ckitem = str_replace('*', '1', $item);
		$ckval = ip2long( $ckitem );
		if( !$ckval ) { 
			unset( $wl_items[ $key ] );
			continue;
		}
		$exploded_item = explode( '.' , $item);
		if( $exploded_item[0] == '*' )
			unset( $wl_items[ $key ] );

		if( $exploded_item[1] == '*' && !($exploded_item[2] == '*' && $exploded_item[3] == '*') )
			unset( $wl_items[ $key ] );

		if( $exploded_item[2] == '*' && $exploded_item[3] != '*' )
			unset( $wl_items[ $key ] );

	endforeach; endif;

	$whitelist = implode(PHP_EOL, $wl_items);

	update_site_option( 'brute_ip_whitelist', $whitelist );
}



$brute_dashboard_widget_hide = get_site_option('brute_dashboard_widget_hide');
$brute_dashboard_widget_admin_only = get_site_option('brute_dashboard_widget_admin_only');
$brute_ip_whitelist = get_site_option('brute_ip_whitelist');


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
<h2 style="clear: both; margin-bottom: 15px;"><img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; API Key</h2>

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

<?php if ( $this->is_on_localhost() ) : 
	return; 
endif; ?>

<?php if ( false != $invalid_key ) : ?>
	<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid green; background-color: #eaffd6; margin-right: 20px; margin-bottom:20px;">
		<h3 style="display: block; background-color: green; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;">I <em>need</em> an API key for BruteProtect</h3>
		<form action="" method="post">
		<?php if ( isset($_GET['get_key']) && $_GET['get_key'] == 'success' ) : ?>
			<strong style="font-size: 18px;"><?php _e( 'You have successfully requested an API key.  It should be arriving in your email shortly.<br /><br />Once you receive your key, you must enter it on this page to finish activating BruteProtect.' ); ?></strong>

		<?php else : ?>

			<p><?php _e( 'You must obtain an API key for every site or network you wish to protect with BruteProtect.  You will be generating a BruteProtect.com key for use on <strong><?php echo $host ?></strong>.  There is no cost for an BruteProtect key, and we will never sell your email.' ); ?></p>

			<strong><?php _e( 'Email Address' ); ?></strong><br />
			<input type="text" name="email_address" value="<?php echo $current_user->user_email ?>" id="brute_get_api_key" style="font-size: 18px; border: 1px solid #ccc; padding: 4px; width: 450px;" />
			<input type="hidden" name="brute_action" value="get_api_key" />
			<input type="submit" value="Get an API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
		<?php endif; ?>
		</form>
	</div>
<?php else : ?>
	<div class="updated below-h2" id="message" style="border-color: green; color: green; background-color: #eaffd6;"><p><?php _e( '<strong>API key verified!</strong> Your BruteProtect account is active and your site is protected, you don\'t need to do anything else!' ); ?></p></div>
<?php endif; ?>

<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #0649fe; background-color: #cdf0fe;">
	<h3 style="display: block; background-color: #0649fe; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'I <em>have</em> an API key for BruteProtect' ); ?></h3>
	<form action="" method="post">
		<strong><?php _e( 'Enter your key: ' ); ?></strong><br />
		<input type="text" name="brute_api_key" value="<?php echo get_site_option('bruteprotect_api_key') ?>" id="brute_api_key" style="font-size: 18px; border: 1px solid #ccc; padding: 4px; width: 450px;" />
		<input type="hidden" name="brute_action" value="update_key" />
		<input type="submit" value="Save API Key" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
	</form>
</div>


</div>