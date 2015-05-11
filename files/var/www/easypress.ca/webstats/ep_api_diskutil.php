<?php

$domain = $_GET['domain'];
    $f = "/var/www/$domain";
    $io = popen ( '/usr/bin/du -sb ' . $f, 'r' );
    $size = fgets ( $io, 4096);
    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
    pclose ( $io );
    echo $size;
