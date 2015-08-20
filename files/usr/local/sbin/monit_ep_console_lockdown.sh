#!/bin/bash
export PATH=/bin:/usr/bin:/usr/local/sbin
LOCK_EXIT=0
LOCKS=/var/www/easypress.ca/console/lockdown/lock
UNLOCKS=/var/www/easypress.ca/console/lockdown/unlock
if cd $LOCKS; then
	CF=`/usr/bin/find . -type f -exec /usr/bin/basename {} \;`
	if [ "$CF" != "" ]; then
		for DOMAIN in $CF; do
			if [ "$DOMAIN" != "" ]; then
				rm $DOMAIN
				if [ -d /var/www/${DOMAIN} ]; then
					chcustown ${DOMAIN}	# change ownership to customer's username	
					chwebown /var/www/$DOMAIN/wordpress/wp-content/uploads # make uploads writable by web server user
					chwebown /var/www/$DOMAIN/wordpress/.sessions # make PHP sessions dir writable by web server user
					chwebown /var/www/$DOMAIN/wordpress/php-errors.log
					echo "$DOMAIN has been locked down"
				else
					echo "Error: $DOMAIN is not installed"
				fi
			fi
		done
		LOCK_EXIT=1
	fi
else
	echo "Error: Could not change into $LOCKS"
fi

if cd $UNLOCKS; then
	CF=`/usr/bin/find . -type f -exec /usr/bin/basename {} \;`
	if [ "$CF" != "" ]; then
		for DOMAIN in $CF; do
			if [ "$DOMAIN" != "" ]; then
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
	echo "Error: Could not change into $UNLOCKS"
fi

exit $LOCK_EXIT
