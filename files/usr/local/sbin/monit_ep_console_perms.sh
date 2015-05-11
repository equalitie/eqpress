#!/bin/bash
export PATH=/bin:/usr/bin:/usr/local/sbin
CONFS=/var/www/easypress.ca/console/perms
if cd $CONFS; then
	CF=`/usr/bin/find . -type f -exec /usr/bin/basename {} \;`
	if [ "$CF" != "" ]; then
		for DOMAIN in $CF; do
			if [ "$DOMAIN" != "" ]; then
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
	echo "Error: Could not change into $CONF"
	exit 1
fi
