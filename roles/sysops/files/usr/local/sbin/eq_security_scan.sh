#!/bin/bash
#
# eq_security_scan.sh
#
# Manual check:
#   1. nginx error logs: requests blocked by access rules
#   2. every WordPress site: plugins on the list in wp_check_plugins.php
#
export PATH=/bin:/usr/bin
shopt -s nullglob

# scan web server error logs
for f in /var/log/nginx/*error.log; do
	grep -q "access forbidden by rule" "$f" || continue
	echo "$f"
	echo
	grep "access forbidden by rule" "$f"
	echo
	echo "------------------------------------------------------"
	echo
done

# scan for unwanted wordpress plugins
for plugins in /var/www/*/wordpress/wp-content/plugins; do
	[ -d "$plugins" ] || continue
	php /usr/local/sbin/wp_check_plugins.php "$plugins"
done
