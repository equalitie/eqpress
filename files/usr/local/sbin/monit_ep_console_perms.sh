#!/bin/bash
export PATH=/bin:/usr/bin:/usr/local/sbin
CONFS=/var/www/easypress.ca/console/perms
if [ -d $CONFS ]; then
	cd $CONFS
	CF=`/usr/bin/find -type f -printf '%f\n'`
	if [ -n "$CF" ]; then
		for DOMAIN in $CF; do
			if [ -n "$DOMAIN" ]; then
				rm $DOMAIN
				if [ -d /var/www/$DOMAIN ]; then
					chwebown /var/www/$DOMAIN/wordpress/
					chepown /var/www/$DOMAIN
					echo "Changed permissions for: $DOMAIN"
				else
					echo "Error: $DOMAIN is not installed"
				fi
			fi
		done
		exit 1
	else
		exit 0
	fi
else
	echo "Error: Could not change into $CONFS"
	exit 1
fi
