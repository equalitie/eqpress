<?php
/**
 *  Un-Lockdown
 */
require ( "ep_api_functions.php" );
$domain = $_GET['domain'];
header('Content-Type: application/json');

if ( is_valid_domain( $domain ) ) {
    if ( domain_exists( $domain ) ) {
        $ret = exec( "/usr/bin/touch /var/www/easypress.ca/console/lockdown/unlock/$domain 2>&1", $out, $return_var );
        if ( $return_var == 0 ) {
            echo json_encode( array ( 'status' => 'ok' ) );
        } else {
            echo json_encode( array ( 'status' => 'failed', 'output' => $ret ) );
        }
    }
}
