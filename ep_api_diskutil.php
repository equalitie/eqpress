<?php
/**
 * Disk space utilization
 */

require ( "ep_api_functions.php" );
header('Content-Type: application/json');

$domain = $_GET['domain'];
if ( is_valid_domain( $domain ) ) {
    if ( domain_exists( $domain ) ) {
        $f = "/var/www/$domain";
        $io = popen ( '/usr/bin/du -sb ' . $f, 'r' );
        $size = fgets ( $io, 4096);
        $size = substr ( $size, 0, strpos ( $size, "\t" ) );
        pclose ( $io );
        echo $size;
    }
}

