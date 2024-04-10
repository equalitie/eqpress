<?php
/**
 * wp-cli script to disable wordfence scanning.
 *
 * To use, run it in the docroot of a site:
 * wp eval-file /usr/local/sbin/wpcli_wordfence_no_scan.php
 *
 * To update all sites:
 * for f in /var/www/*/wordpress/wp-content/plugins; do ls -l $f |grep wordfence && cd $f && wp plugin status wordfence|grep Active && wp eval-file /usr/local/sbin/wpcli_wordfence_no_scan.php; done
 *
 */

global $wpdb;
$table = $wpdb->prefix . "wfConfig";
$result = $wpdb->query( "UPDATE $table SET val = 0 WHERE name = 'scheduledScansEnabled'" );
var_dump($result);

