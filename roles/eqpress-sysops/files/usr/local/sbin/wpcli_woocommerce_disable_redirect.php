<?php
/**
 * wp-cli script to disable wordfence scanning.
 *
 * To use, run it in the docroot of a site:
 * wp eval-file /usr/local/sbin/wpcli_woocommerce_disable_redirect.php
 *
 * To update all sites:
 * for f in /var/www/*/wordpress/wp-content/plugins; do ls -l $f |grep woocommerce && cd $f && wp plugin status woocommerce|grep Active && wp eval-file /usr/local/sbin/wpcli_woocommerce_disable_redirect.php
 *
 */

global $wpdb;
$table = $wpdb->prefix . "options";
$result = $wpdb->query( "UPDATE $table SET option_value = 'no' WHERE option_name = 'woocommerce_force_ssl_checkout'" );
var_dump($result);

