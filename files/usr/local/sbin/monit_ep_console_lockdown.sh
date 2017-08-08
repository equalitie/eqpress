#!/bin/bash
export PATH=/bin:/usr/bin:/usr/local/sbin
LOCK_EXIT=0
LOCKS=/var/www/easypress.ca/console/lockdown/lock
UNLOCKS=/var/www/easypress.ca/console/lockdown/unlock
if cd $LOCKS; then
	CF=`/usr/bin/find -type f -printf '%f\n'`
	if [ -n "$CF" ]; then
		for DOMAIN in $CF; do
			if [ -n "$DOMAIN" ]; then
				rm $DOMAIN
				if [ -d /var/www/${DOMAIN} ]; then
					chcustown ${DOMAIN}	# change ownership to customer's username	
					chwebown /var/www/$DOMAIN/wordpress/wp-content/uploads # make uploads writable by web server user
					chwebown /var/www/$DOMAIN/wordpress/.sessions # make PHP sessions dir writable by web server user
					touch /var/www/$DOMAIN/wordpress/php-errors.log
					chwebown /var/www/$DOMAIN/wordpress/php-errors.log

					if [ -d /var/www/${DOMAIN}/wordpress/wp-content/blogs.dir ]; then
						chwebown /var/www/${DOMAIN}/wordpress/wp-content/blogs.dir # make multisite upload dir writable by web server user
					fi
					echo "$DOMAIN has been locked down"
				else
					echo "Error: $DOMAIN is not installed"
				fi
			fi
		done
		LOCK_EXIT=1
	fi
else
	echo "Error: Could not change directory into $LOCKS"
fi

if cd $UNLOCKS; then
	CF=`/usr/bin/find -type f -printf '%f\n'`
	if [ -n "$CF" ]; then
		for DOMAIN in $CF; do
			if [ -n "$DOMAIN" ]; then
				rm $DOMAIN
				if [ -d /var/www/$DOMAIN ]; then
					chwebown /var/www/$DOMAIN/wordpress # make everything writable by web server user
					echo "$DOMAIN has been unlocked"
				else
					echo "Error: $DOMAIN is not installed"
				fi
			fi
		done
		LOCK_EXIT=1
	fi
else
	echo "Error: Could not change directory into $UNLOCKS"
fi

exit $LOCK_EXIT
