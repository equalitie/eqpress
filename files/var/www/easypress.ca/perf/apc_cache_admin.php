<?php
if ( "yes" === $_GET['purge'] ) {
	$cache_cleared = apc_clear_cache("user");
	$cache_cleared = apc_clear_cache();
	if ( $cache_cleared === true ) {
		echo "Cache cleared";
	} else {
		echo "Cache NOT cleared";
	}
} else if ( "yes" === $_GET['info'] ) {
	echo "<pre>";
	if ( "yes" === $_GET['user'] ) 
		var_dump(apc_cache_info("user"));
	else
		var_dump(apc_cache_info());
	echo "</pre>";
} else {
	echo "purge=yes to purge the APC cache<br />info=yes to list the cache index<br />info=yes&user=yes to list user cache index only";
}
?>
