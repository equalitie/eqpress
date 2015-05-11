<?php
$domain = $_GET['domain'];
$wpconfig = "/var/www/$domain/wordpress/" . 'wp-config.php';

if ( ! file_exists( $wpconfig ) ) {
    header('Content-Type: application/json');
    echo json_encode( array( 'error' => "$domain does not exist on this node" ) );
    exit;
}

require_once( "/var/www/$domain/wordpress/" . 'wp-config.php' );


$table = $table_prefix . 'options';
$db_query = "SELECT option_value FROM $table WHERE option_name = 'siteurl'";
$dbh = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
if ( $dbh->connect_error ) {
    process_errors( "DB connection failed to DB_NAME for domain $domain: " . $dbh->connect_errno . ' : ' . $dbh->connect_error, true );
}
if ( $db_results = $dbh->query( $db_query ) ) {
    $db_data = $db_results->fetch_all();
} else {
    process_errors( 'WordPress siteurl not found.', true );
}
$siteurl = $db_data[0][0];
$https_siteurl = preg_replace( "/^http:/i", "https:", $siteurl );

if ( ( $pos = strpos ( $siteurl, 'wp.easypress.ca') ) != false ) {
    $dev_mode = 'yes';
} else {
    $dev_mode = 'no';
}

$user_table = $table_prefix . 'users';
$get_user_query = "SELECT user_login, user_email FROM $user_table WHERE ID=1";
if ( $db_user_results = $dbh->query( $get_user_query ) ) {
    $user_data = $db_user_results->fetch_all();
} else {
    process_errors( 'WordPress user with ID 1 does not exist.', true );
}
error_log(var_export($user_data, true));
$wp_user = $user_data[0][0];
$wp_user_email = $user_data[0][1];

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
$dbh->close();


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

