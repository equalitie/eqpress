#!/bin/bash
#
# wp_upgrade.sh - upgrade WordPress using wp-cli
#
# update all sites running 3.9.2 to 3.9.3
# for f in /var/www/*/wordpress; do cd $f; v=`wp --allow-root core version`; echo "${f} ${v}"; if [ $v = "3.9.2" ]; then wp --allow-root core update --version=3.9.3; fi done
#
# update all sites running 4.0 to 4.0.1
# for f in /var/www/*/wordpress; do cd $f; v=`wp --allow-root core version`; echo "${f} ${v}"; if [ $v = "4.0" ]; then wp --allow-root core update; fi done
#
# check that all sites are running the latest versions
# for f in /var/www/*/wordpress; do cd $f; v=`wp --allow-root core version`; echo "${f} ${v}"|grep -v "4.0.1"|grep -v "3.9.3"; done
#
# A few sites failed to update because of either a crap plugin or some redirect back to wp-login.php or maybe some language issue. To fully automate the updates we should check for the return code and if it's not 0 then we can force the update to the latest version by doing:
# wp core download --version=4.0.1 --force
#
#
export PATH=/usr/local/sbin:/usr/local/bin:/bin:/usr/bin
LATEST_VERSION="3.9.2"

do_upgrade() {
	cd $ROOT
	CURRENT_VERSION=`wp --allow-root core version`
	if [ "$CURRENT_VERSION" = "$LATEST_VERSION" ]; then
		echo "$DOMAIN is already running WordPress $LATEST_VERSION."
		return
	fi
	echo "$DOMAIN current version is $CURRENT_VERSION so updating"
	if [ ! -e /home/wordpress/wordpress-${LATEST_VERSION}-no-content.zip ]; then
		cd /home/wordpress
		/usr/bin/wget http://wordpress.org/wordpress-${LATEST_VERSION}-no-content.zip
		cd $ROOT
	fi
	echo -n "Backing up database for $DOMAIN ..."
	SUCCESS=`wp --allow-root db export|grep -i success`
	if [ -n "$SUCCESS" ]; then
		echo "done."
		mv *.sql ../
		echo -n "Updating WordPress for $DOMAIN ..."
		WP_SUCCESS=`wp --allow-root core update /home/wordpress/wordpress-${LATEST_VERSION}-no-content.zip --version=${LATEST_VERSION}|grep -i uccess`
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
			do_upgrade
			wp_version=`wp --allow-root --path=${ROOT} core version`
			echo "${DOMAIN} is now running version ${wp_version}"
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
			echo "e.g. wp_upgrade.sh site switchwp.com eqpress.co"
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
echo https://switchwp.com/perf/apc_cache_admin.php
echo
echo "******************************************************"
echo
exit 0
