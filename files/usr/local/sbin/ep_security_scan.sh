#!/bin/sh

export PATH=/bin:/usr/bin

# scan web server error logs
for f in `grep -l "access forbidden by rule" /var/log/nginx/*error.log`; do
	echo $f
	echo
	grep "access forbidden by rule" $f
	echo
	echo "------------------------------------------------------"
	echo
done

# scan for crap wordpress plugins
for s in `find /var/www -maxdepth 1 -type d`; do
	php /usr/local/sbin/wp_check_plugins.php ${s}/wordpress/wp-content/plugins
done
