<?php
/**
 * API Functions
 */

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
    if ( ( $num_pieces = sizeof( $pieces ) ) < 2) {
        send_error( "Invalid domain name (1): $domain" );
    }
    foreach( $pieces as $piece ) {
        $ctr++;
        if ( !preg_match( '/^[a-z\d][a-z\d-]{0,62}$/i', $piece ) || preg_match( '/-$/', $piece ) ) {
            send_error( "Invalid domain name (2): $domain" );
        }
    }
    return true;
}

/**
 * Check that the domain is hosted on the node.
 *
 * @param string $domain is the domain name of the site to install.
 * @return boolean
 *
 */
function domain_exists( $domain ) {
    if ( ! file_exists( "/var/www/$domain/wordpress/index.php" ) ) {
        send_error( "Domain does not exist: $domain" );
    }
    return true;
}

/**
 * Send an error message.
 *
 * @param string $message is the error message to send.
 *
 */
function send_error( $message ) {
    echo json_encode( array ( 'status' => 'failed', 'output' => $message ) );
    error_log( $message );
    exit;
}
