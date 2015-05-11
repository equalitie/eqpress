#!/bin/bash
#
# wp_upgrade.sh - upgrade WordPress using wp-cli
#
#
export PATH=/usr/local/sbin:/usr/local/bin:/bin:/usr/bin
LATEST_VERSION="3.6.1"

do_upgrade() {
	cd $ROOT
	CURRENT_VERSION=`wp --allow-root core version`
	if [ "$CURRENT_VERSION" = "$LATEST_VERSION" ]; then
		echo "$DOMAIN is already running WordPress $LATEST_VERSION."
		return
	fi
	echo -n "Backing up database for $DOMAIN..."
	SUCCESS=`wp --allow-root db export|grep -i success`
	if [ -n "$SUCCESS" ]; then
		echo "done."
		mv *.sql ../
		echo -n "Updating WordPress for $DOMAIN ..."
		WP_SUCCESS=`wp --allow-root core update /var/www/easypress.ca/provision/wordpress-${LATEST_VERSION}.zip --version=${LATEST_VERSION}|grep -i uccess`
		chwebown /var/www/$DOMAIN/wordpress/
		chepown /var/www/$DOMAIN
		if [ -n "$WP_SUCCESS" ]; then
			echo "done."
			echo -n "Purging the nginx cache for ${DOMAIN} ..."
			rm -r /var/cache/nginx/${DOMAIN}/*
			echo done.
			echo -n "Upgrading the database for $DOMAIN ..."
			DB_SUCCESS=`wp --allow-root core update-db|grep -i success`
			if [ -n "$DB_SUCCESS" ]; then
				echo "done."
			else
				echo "failed!"
			fi
		else
			echo "failed!"
		fi
	else
		echo "failed!"
	fi
}

case "$1" in
	all)
		for ROOT in `find /var/www -maxdepth 2 -name wordpress`; do
			DOMAIN=`echo $ROOT|sed -e 's/\/var\/www\/\(.*\)\/wordpress/\1/'`
			#echo -n "Upgrade ${DOMAIN}? [y/n] "
			#read you_sure
			#if [ "$you_sure" = "y" ]; then
				do_upgrade
			#fi
		done
        ;;

	site)
		if [ -n "$2" ]; then
			for DOMAIN in $@; do
				if [ $DOMAIN != "site" ]; then
					ROOT=/var/www/$DOMAIN/wordpress
					if [ -d $ROOT ]; then
						#echo "$DOMAIN exists"
						do_upgrade
					else
						echo "Error: ${DOMAIN} does not exist. Nothing to upgrade."
					fi
				fi
			done
		else
			echo "Error: Please enter the domain name of the site(s) to upgrade."
			echo "e.g. wp_upgrade.sh site switchwp.com easypress.co"
			exit 1
		fi
	;;

	*)
		echo "Usage: wp_upgrade.sh {all|site domain1 [domain2 ...]}"
		exit 1
esac
echo
echo "******************************************************"
echo
echo "DO NOT FORGET TO CLEAR APC CACHE"
echo
echo https://newjersey.easypress.ca/perf/apc_cache_admin.php
echo
echo "******************************************************"
echo
exit 0
