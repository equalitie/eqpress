<?php
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

$brute_ip_whitelist = get_site_option('brute_ip_whitelist');


?>

<div class="wrap">
<h2 style="clear: both; margin-bottom: 15px;"><img src="<?php echo BRUTEPROTECT_PLUGIN_URL ?>images/BruteProtect-Logo-Text-Only-40.png" alt="BruteProtect" width="250" height="40" style="margin-bottom: -2px;"/> &nbsp; IP White List</h2>

<br class="clear" />
<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #ccc; background-color: #e5e5e5; margin-right: 20px;">
	<h3 style="display: block; background-color: #555; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'IP Whitelist' ); ?></h3>
	<form action="" method="post">
		<strong><?php _e( 'Always allow login attempts from the following IP addresses:' ); ?></strong><br />
		<textarea name="brute_ip_whitelist" rows="15" cols="40"><?php echo $brute_ip_whitelist ?></textarea>
		<br /><small>Enter one IPv4 per line, * for wildcard octet<br />(ie: <code>192.168.0.1</code> and <code>192.168.*.*</code> are valid, <code>192.168.*</code> and <code>192.168.*.1</code> are invalid)</small>
		<input type="hidden" name="brute_action" value="update_brute_whitelist" /><br />
		<input type="submit" value="Save" class="button" style="margin-top: 10px;margin-bottom: 10px;" />
	</form>
</div>

<div style="display: block; width: 500px; float: left; padding: 10px; border: 1px solid #ccc; background-color: #e5e5e5;">
	<h3 style="display: block; background-color: #555; color: #fff; margin: -10px -10px 1em -10px; padding: 10px;"><?php _e( 'Current IP' ); ?></h3>
	Your current IP address is: <strong><?php echo $this->brute_get_ip(); ?></strong>
</div>
</div>