<?php
/**
 * Site info
 */

require ( "ep_api_functions.php" );
header('Content-Type: application/json');
$domain = $_GET['domain'];

if ( is_valid_domain( $domain ) ) {
    if ( domain_exists( $domain ) ) {
        require_once( "/var/www/$domain/wordpress/" . 'wp-config.php' );

        $table = $table_prefix . 'options';
        $db_query = "SELECT option_value FROM $table WHERE option_name = 'siteurl'";
        $dbh = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        if ( $dbh->connect_error ) {
            send_error( "DB connection failed to DB_NAME for domain $domain: " . $dbh->connect_errno . ' : ' . $dbh->connect_error );
        }
        if ( $db_results = $dbh->query( $db_query ) ) {
            $db_data = $db_results->fetch_all();
        } else {
            send_error( 'WordPress siteurl not found.' );
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
            send_error( 'WordPress user with ID 1 does not exist.' );
        }
        error_log(var_export($user_data, true));
        $wp_user = $user_data[0][0];
        $wp_user_email = $user_data[0][1];

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
    }
}

