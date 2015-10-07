<?php

require 'flight/Flight.php';
Flight::set('flight.log_errors', true);

// Log the incoming request.
file_put_contents( 'api.log',
    "\n" . date( DATE_RFC850 ) .
    "\n_POST parameters: " . var_export( $_POST, true ) .
    "\n_SERVER parameters: " . var_export( $_SERVER, true ) .
    "\n" . var_export( json_decode ( file_get_contents( 'php://input' ) ), true ) .
    "\n", FILE_APPEND | LOCK_EX );


/**
 * Delete an installed site.
 *
 */
Flight::route( 'DELETE /v1/locations/@location/sites/@domain', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "/usr/bin/sudo /var/www/easypress.ca/api/commands/site_purge.sh $node $domain 2>&1", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'return_val' => $return_val, 'location' => $node, 'domain' => $domain, 'output' => $output ) );
    } else {
        Flight::json( array( 'status' => 'ok', 'location' => $node, 'domain' => $domain, 'output' => $output ) );
    }
});

/**
 * Go Live route - search and replace all occurrances of dev address with live address.
 *
 */
Flight::route( 'POST /v1/locations/@location/sites/@domain/golive', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    $url = add_http( Flight::request()->data['canonical_url'] );
    exec( "/usr/bin/sudo /var/www/easypress.ca/api/commands/golive.sh $domain $url $node", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => $output ) );
    } else {
        Flight::json( array( 'status' => 'ok' ) );
    }
});

/**
 * Reset SFTP password.
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/resetpwd', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_chpass.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } else {
        header('Content-Type: application/json');
        echo ($output[0]);
    }
});

/**
 * Get site info.
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/siteinfo', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_siteinfo.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } else {
        if ( isset( $output[0] ) && strpos( $output[0], 'siteurl' ) ) {
            header('Content-Type: application/json');
            echo ( $output[0] );
            error_log( var_export( $output, true ) );
        } else {
            header("HTTP/1.1 500 Error");
            Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
            error_log( var_export( $output, true ) );
        }
    }
});

/**
 * Reset file and directory permissions and ownerships back to default.
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/fileperm', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_fileperm.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } else {
        header('Content-Type: application/json');
        echo ($output[0]);
    }
});

/**
 * Enable lockdown mode.
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/lockdown', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_lockdown.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } else {
        header('Content-Type: application/json');
        echo ($output[0]);
    }
});

/**
 * Undo lockdown mode.
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/unlock', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_unlock.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } else {
        header('Content-Type: application/json');
        echo ($output[0]);
    }
});

/**
 *  Get disk space utilization.
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/diskutil', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_diskutil.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } elseif ( $output != NULL ) {
        Flight::json( array( 'status' => 'ok', 'disk_usage' => $output[0] ) );
    } else {
        Flight::json( array( 'status' => 'failed', 'output' => "$domain does not exist on this node" ) );
    }
});

/**
 * Get webstats
 *
 */
Flight::route( 'GET /v1/locations/@location/sites/@domain/sitestats', function( $location, $domain ) {
    if( ! $node = get_node( $location ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid location' ) );
    }

    if( ! is_valid_domain( $domain ) ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => 'Invalid domain name' ) );
    }

    exec( "curl -k -E /etc/ssl/ep_client.pem https://$node/webstats/ep_api_webstats.php?domain=$domain", $output, $return_val );
    if ( $return_val != 0 ) {
        header("HTTP/1.1 500 Error");
        Flight::json( array( 'status' => 'failed', 'output' => var_export( $output, true ) ) );
    } else {
        header('Content-Type: application/json');
        echo ($output[0]);
    }
});
/**
 *  Route not found.
 *
 */
Flight::map('notFound', function(){
    echo 'Custom 404';
});

Flight::start();

/**
 * Ensure the canonical URL has http:// prepended.
 *
 * @param $url
 * @return string
 */
function add_http( $url ) {
    if ( !preg_match("~^(?:f|ht)tps?://~i", $url ) ) {
        $url = "http://" . $url;
    }
    return $url;
}

/**
 * Get the name of the node to connect to.
 *
 * @param $location
 * @return bool
 */
function get_node( $location ) {
    if ( $location != 'ca' && $location != 'us' && $location != 'uk' && $location != 'ca1' && $location != 'jester' ) {
        return false;
    }

    if ( $location == 'ca' || $location == 'us' || $location == 'ca1' ) {
        if ( isset( $_GET['vmgauth'] ) && $_GET['vmgauth'] == 'boreal321istheforestoftruth' ) {
        } else {
            exit;
        }
    }

    $servers = array(
        'ca'       => array( 'hostname' => 'indigo.easypress.ca', 'ip' => '64.68.201.251', 'cname' => 'ips3.ca.easypress.ca' ),
        'us'       => array( 'hostname' => 'rogue.easypress.ca', 'ip' => '45.56.111.125', 'cname' => 'ips2.us.easypress.ca' ),
        'uk'       => array( 'hostname' => 'igloo.easypress.ca', 'ip' => '194.1.166.186', 'cname' => 'ips1.uk.easypress.ca' ),
        'ca1'      => array( 'hostname' => 'anchor.easypress.ca', 'ip' => '199.27.180.172', 'cname'=> 'ips1.ca.easypress.ca' ),
        'us1'      => array( 'hostname' => 'gong.easypress.ca', 'ip' => '198.74.61.42', 'cname' => 'ips1.us.easypress.ca' ),
        'jester'   => array( 'hostname' => 'jester.easypress.ca', 'ip' => '198.211.115.196', 'cname' => 'jester.easypress.ca' ) );
    return $servers[$location]['hostname'];
}

/**
 * Validate a domain name.
 *
 * @param string $domain is the domain name of the site to install.
 * @return boolean
 *
 */
function is_valid_domain( $domain ) {
    $ctr = 0;
    $pieces = explode( '.', $domain );
    if ( ( $num_pieces = sizeof( $pieces ) ) < 2)
        return false;
    foreach( $pieces as $piece ) {
        $ctr++;
        if ( !preg_match( '/^[a-z\d][a-z\d-]{0,62}$/i', $piece ) || preg_match( '/-$/', $piece ) )
            return false;
    }
    return true;
}
