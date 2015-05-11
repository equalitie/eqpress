#!/usr/bin/php
<?php  
        $secret_key = $argv[1] . '6kwiyk768g7gy2PVhhzFEUu';
        $ep_api_key = md5( $secret_key );
        echo "define('EP_API_KEY', '$ep_api_key');\n";
?>
