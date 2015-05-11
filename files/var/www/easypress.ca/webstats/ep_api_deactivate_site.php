<?php
$domain = $_GET['domain'];
$nginx_config = "/etc/nginx/sites-enabled/$domain";

if ( ! file_exists( $nginx_config ) ) {
    header('Content-Type: application/json');
    echo json_encode( array( 'error' => "$domain does not exist on this node" ) );
    process_errors( 'WordPress siteurl not found.', true );
}

header('Content-Type: application/json');
echo json_encode( array ( 'siteurl' => $siteurl,
                          'dev_mode' => $dev_mode,
                          'wp_user' => $wp_user,
                          'wp_user_email' => $wp_user_email,
                          'mysql_admin_url' => $https_siteurl . '/adminer/?db=' . DB_NAME,
                          'mysql_admin_user' => DB_USER,
                          'sftp_user' => DB_USER,
                          'sftp_host' => $_SERVER['SERVER_ADDR'],
                          'sftp_port' => '22' ) );


/**
 * Process errors
 *
 * @param string $message is the error message to write to the log and/or screen.
 * @param boolean $exit_now will determine whether or not to terminate the process.
 * 
 */
function process_errors( $message, $exit_now = false ) {
	global $all_errors;
	$all_errors[] = $message;
	error_log( $message );
	if ( $exit_now ) {
		var_dump( $all_errors );
		error_log( var_export( $all_errors, true ) );
		exit;
	}
}

