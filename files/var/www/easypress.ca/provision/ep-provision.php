<?php
/**
 * ep-provision.php
 *
 * WordPress Auto Provisioning
 *
 * version 1.1
 *
 */

require( 'includes/class-ep-provision-util.php' );
require( 'includes/class-ep-provision-view.php' );
require( 'includes/Mustache/Autoloader.php' );
require( 'includes/class-curl-request.php' );

$util = new EP_Provision_Util;
$view = new EP_Provision_View;
$params = array(
    'status'            => array( 'errors' => 0, 'messages' => array() ),
    'request_id'        => $util->random( 8 ),
    'auth_token'        => '',
    'bcc_email'         => 'vmg@boreal321.com',
    'alert_email'       => 'vmg@boreal321.com',
    'org_name'          => 'Boreal321 Hosting',
    'org_email'         => 'support@boreal321.com',
    'org_twitter'       => '@boreal321',
    'easydns_user'      => '',
    'is_dev'            => '',
    'add_cname'         => 1,
    'domain'            => '',
    'domain_cname'      => '.wp.boreal321.com',
    'email'             => '',
    'real_first_name'   => '',
    'real_last_name'    => '',
    'first_name'        => '',
    'last_name'         => '',
    'location'          => 'us',
    'multisite'         => false,
    'ep_api_key'        => '',
    'db_host'           => $_SERVER['EP_DB_HOST'],
    'db_name'           => '',
    'db_user'           => '',
    'db_pass'           => '',
    'db_prefix'         => '',
    'db_charset'        => $_SERVER['EP_DB_CHARSET'],
    'db_collate'        => $_SERVER['EP_DB_COLLATE'],
    'wpadmin_pass'      => '',
    'sftp_pass'         => '',
    'web_user'          => 'www-data',
    'web_group'         => 'www-data',
    'pwp'               => array( 'db' => '', 'sftp' => '', 'wp' => '' ),
    'nodes'             => array('us'       => array( 'hostname' => 'rogue.easypress.ca',
                                                      'ip'       => '45.56.111.125',
                                                      'cname'    => 'ips2.us.easypress.ca.' ),

                                 'equalit-test'  => array( 'hostname' => 'eqpress-test1.boreal321.com',
                                                      'ip'       => '199.119.112.135',
                                                      'cname'    => 'eqpress-test1.boreal321.com.' ),

                                 'jester'   => array( 'hostname' => 'jester.easypress.ca',
                                                      'ip'       => '198.211.115.196',
                                                      'cname'    => 'jester.easypress.ca.' ), )

); // close $params

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;
switch($step) {
    case 0: // Step 1

    case 1: // Step 1, direct link.
        $view->display_header();
        $view->display_setup_form();
        $view->display_footer();
        break;

    case 2:
        // Define the constants holding the file system paths
        define( 'EP_PROV_DIR', dirname( __FILE__ ) . '/' );
        define( 'EP_PEND_DIR', EP_PROV_DIR . 'pending/' );

        // Log the incoming request.
        file_put_contents( EP_PROV_DIR . 'ep-provision.log',
            "\n" . date( DATE_RFC850 ) .
            ' (request ' . $params['request_id'] . ' received): ' .
            serialize( $_POST ) . "\n", FILE_APPEND | LOCK_EX );

        // Validate and sanitize the request
        $util->validate_post_parameters();

        // Authenticate the request
        if ( $params['location'] != 'uk' ) {
            $util->authenticate( $_POST['username'], $_POST['password'], $_POST['api_key'] );
        }

        // More constants for paths
        if (!empty($_SERVER['TMP'])) {
            define( 'EP_TMP_DIR', $_SERVER['TMP'] . '/' . $params['request_id'] . '/' . $params['domain'] );
            define( 'EP_TMP', $_SERVER['TMP'] . '/' . $params['request_id'] );
        } else {
            define( 'EP_TMP_DIR', '/var/tmp/' . $params['request_id'] . '/' . $params['domain'] );
            define( 'EP_TMP', '/var/tmp/' . $params['request_id'] );
        }

        // Process the request if there are no errors
        if ( $params['status']['errors'] == 0 ) {

            // Start the templating engine
            Mustache_Autoloader::register();
            $mustache = new Mustache_Engine( array(
                'loader' => new Mustache_Loader_FilesystemLoader( EP_PROV_DIR . 'templates'),
                'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
                'strict_callables' => true,
                'entity_flags' => ENT_NOQUOTES
            ) );

            $util->create_config_dir();
            $util->generate_credentials();
            $util->create_wp_config();
            $util->create_nginx_config();
            $util->create_welcome_email();
            $util->create_ansible_playbook();
            $util->move_config_dir();
            /*
            if ( $params['add_cname'] != 0 ) {
                $util->add_cname( $params['domain'] );
                if ( $params['multisite'] == 'subdomain' ) {
                    sleep( 2 );
                    $util->add_cname( '*.' . $params['domain'] );
                }
            }
            */
        }

        // Let the requester know the status
        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $params['status'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        // Log the results of processing the request
        file_put_contents( EP_PROV_DIR . 'ep-provision.log',
            "\n" . date( DATE_RFC850 ) .
            ' (request ' . $params['request_id'] . ' processed): ' .
            serialize( $params ) . "\n", FILE_APPEND | LOCK_EX );
        break;
}
