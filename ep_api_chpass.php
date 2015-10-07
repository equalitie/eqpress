<?php
/**
 *  Change SFTP password
 */
require ( "ep_api_functions.php" );
$domain = $_GET['domain'];
header('Content-Type: application/json');

if ( is_valid_domain( $domain ) ) {
    if ( domain_exists( $domain ) ) {
        $ret = exec( "/usr/bin/sudo /usr/local/sbin/ep_chpass.sh $domain 2>&1", $out, $return_var );
        $user_pass = explode( "|", $out[0] );
        if ( $return_var == 0 ) {
            echo json_encode( array ( 'status' => 'ok', 'username' => $user_pass[1], 'password' => $user_pass[2] ) );
        } else {
            echo json_encode( array ( 'status' => 'failed', 'output' => $ret ) );
        }
    }
}

