#!/bin/sh
#
# ep_purge_site.sh
#
# remove all the parts of a provisioned website
#
export PATH=/bin:/usr/bin:/usr/local/sbin:/usr/sbin

purge() {
    if [ -d /var/www/$1 ]; then
        DB=`grep DB_NAME /var/www/$1/wordpress/wp-config.php | sed -e "s/^.*, '//" | sed -e "s/'.*$//"`
        USER=`grep DB_USER /var/www/$1/wordpress/wp-config.php | sed -e "s/^.*, '//" | sed -e "s/'.*$//"`
        mysqladmin --force -u root drop $DB > /dev/null 2>&1
        mysql -u root -e "drop user ${USER}@localhost"
        userdel ${USER}
        rm -r /var/www/$1
        rm /etc/nginx/sites-available/$1
        rm /etc/nginx/sites-enabled/$1
        cp /etc/nginx/cache.conf /etc/nginx/cache.conf-`date +%Y%M%d%H%M%S`
        cat /etc/nginx/cache.conf | grep -v "$1" > /etc/nginx/cache.conf.new
        mv /etc/nginx/cache.conf.new /etc/nginx/cache.conf
        nginx -s reload
    else
        echo "${1} does not exist."
        exit 1;
    fi
}

if [ -z $1 ]; then
    echo "Error: No domain name provided."
    exit 1
else
    purge $1
fi
