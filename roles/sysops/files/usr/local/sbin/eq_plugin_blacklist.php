<?php
echo "eq_plugin_blacklist.php is deprecated and here as reference only\n";
echo "Unused duplicate of wp_check_plugins.php; run eq_security_scan.sh instead.\n";
exit(1);

// ---------------------------------------------------------------------------
// Reference only, never runs. Original script:
//
// $evil_plugins = array (
// 'adminer',
// 'async-google-analytics',
// 'backupwordpress',
// 'backwpup',
// 'broken-link-checker',
// 'contextual-related-posts',
// 'dw-twitter',
// 'dynamic-related-posts',
// 'ezpz-one-click-backup',
// 'file-commander',
// 'fuzzy-seo-booster',
// 'google-sitemap-generator',
// 'google-xml-sitemaps-with-multisite-support',
// 'hcs.php',
// 'jr-referrer',
// 'missed-schedule',
// 'no-revisions',
// 'nospamnx',
// 'ozh-who-sees-ads',
// 'portable-phpmyadmin',
// 'quick-cache',
// 'seo-alrp',
// 'similar-posts',
// 'superslider',
// 'text-passwords',
// 'the-codetree-backup',
// 'toolspack',
// 'tweet-blender',
// //'w3-total-cache',
// 'wordpress-backup-to-dropbox',
// 'wordpress-gzip-compression',
// 'wp-cache',
// 'wp-database-optimizer',
// 'wp-db-backup',
// 'wp-dbmanager',
// 'wp-engine-snapshot',
// 'wp-file-cache',
// 'wp-mailinglist',
// 'wp-missed-schedule',
// 'wp-phpmyadmin',
// 'wp-postviews',
// 'wp-slimstat',
// 'wp-super-cache',
// 'wp-symposium-alerts',
// 'wponlinebackup',
// 'yet-another-featured-posts-plugin',
// 'yet-another-related-posts-plugin',
// );
//
// $site_plugins = scandir( $argv[1] );
//
// $found = array_intersect( $site_plugins, $evil_plugins );
// if ( !empty( $found ) ) {
// 	echo "$argv[1]\n";
// 	print_r( $found );
// 	echo "--------------------------------------------\n\n";
//     unset ( $found );
// }
