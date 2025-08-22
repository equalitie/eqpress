#!/bin/sh
#
# unprovision.sh
#
# remove all the parts of a provisioned website
#
export PATH=/bin:/usr/bin:/usr/local/sbin:/usr/sbin

if [ -d /var/www/$1 ]; then
	echo "are you sure you want to remove $1? [y/n]  \c"
	read sure
	if [ $sure = "y" ]; then
		echo
		DB=`grep DB_NAME /var/www/$1/wordpress/wp-config.php | sed -e "s/^.*, '//" | sed -e "s/'.*$//"`
		USER=`grep DB_USER /var/www/$1/wordpress/wp-config.php | sed -e "s/^.*, '//" | sed -e "s/'.*$//"`
		#echo "are you sure you want to drop $DB? [yes/no]  \c"
		mysqladmin -u root drop $DB
		echo
		echo "are you sure you want to delete the user $USER? [y/n]  \c"
		read drop_user
		if [ $drop_user = "y" ]; then
			mysql -u root -e "drop user ${USER}@localhost"
			userdel ${USER}
			echo "$USER user deleted"
		else
			echo "$USER user NOT dropped"
		fi
		echo
		echo "are you sure you want to delete the docroot and nginx config [y/n]  \c"
		read drnc
		if [ $drnc = "y" ]; then
			rm -r /var/www/$1
			rm /etc/nginx/sites-available/$1
			rm /etc/nginx/sites-enabled/$1
			echo "docroot and nginx config deleted"
		else
			echo "docroot and nginx NOT deleted"
		fi

		echo -n "Updating nginx cache.conf and reloading config..."
		cp /etc/nginx/cache.conf /etc/nginx/cache.conf-`date +%Y%M%d%H%M%S`
		cat /etc/nginx/cache.conf | grep -v "$1" > /etc/nginx/cache.conf.new
		mv /etc/nginx/cache.conf.new /etc/nginx/cache.conf
		nginx -s reload
		echo done.
	else
		echo you chose $sure and not true
	fi
else
	echo $1 does not exist
fi
