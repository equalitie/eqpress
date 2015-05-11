#!/bin/bash
export PATH=/bin:/usr/bin:/usr/sbin:/usr/local/sbin
CONFS=/var/www/easypress.ca/provision/nginx_config
CREDS_DIR=/var/www/easypress.ca/provision/creds
if [ ! -d "$CONFS" ]; then
	echo "wp-provision-stage2: Could not cd into ${CONFS}"
	exit 2
fi
cd ${CONFS}
CF=`/usr/bin/find . -type f -exec /usr/bin/basename {} \;`
if [ -n "$CF" ]; then
	SA=/etc/nginx/sites-available
	SE=/etc/nginx/sites-enabled
	NX=/etc/nginx
	for DOMAIN in ${CF}; do
		if [ -n "$DOMAIN" ]; then
			mv ${DOMAIN} ${SA}
			cd ${SE}
			ln -s ../sites-available/${DOMAIN}
			echo "fastcgi_cache_path /var/cache/nginx/${DOMAIN} levels=1:2 keys_zone=${DOMAIN}:10m inactive=15m max_size=24m;" >> ${NX}/cache.conf
			mv /var/www/easypress.ca/provision/${DOMAIN} /var/www
			/usr/local/sbin/ep_install_console.sh ${DOMAIN}
			chwebown /var/www/${DOMAIN}/wordpress/
			if /usr/sbin/nginx -s reload; then
				echo "The following site added to nginx: ${DOMAIN}"
			else
				echo "Config error while loading the following site into nginx: ${DOMAIN}"
			fi
			chepown /var/www/${DOMAIN}
			CREDS=($(<${CREDS_DIR}/${DOMAIN}.creds)) # read credentials from file and add each field to array
			WP_ADMIN_USER=${CREDS[0]}
			WP_ADMIN_PASSWORD=${CREDS[1]}
			SFTP_USER=${CREDS[2]}
			DB_USER=${CREDS[2]}
			DB_PASSWORD=${CREDS[3]}
			EMAIL=${CREDS[4]}
			LASTNAME=${CREDS[5]}
			BLOG_TITLE="Welcome to your easyPress website."
			sleep 5
			curl -s -S -H "Host: ${DOMAIN}" --data "user_name=${WP_ADMIN_USER}&admin_password=${WP_ADMIN_PASSWORD}&admin_password2=${WP_ADMIN_PASSWORD}&admin_email=${EMAIL}&weblog_title=${BLOG_TITLE}&blog_public=1" http://newjersey.easypress.ca/wp-admin/install.php?step=2
			if [ -e ${CREDS_DIR}/${DOMAIN}.creds ]; then
				nohup shred -zun 100 ${CREDS_DIR}/${DOMAIN}.creds & # securely delete credentials from disk
			fi
			useradd -d /var/www/${DOMAIN} -g sftponly -G www-data -M -N -s /bin/sh ${SFTP_USER}
			mkdir /var/www/${DOMAIN}/.ssh
			touch /var/www/${DOMAIN}/.ssh/authorized_keys
			chown -R ${SFTP_USER} /var/www/${DOMAIN}/.ssh
			chmod 0700 /var/www/${DOMAIN}/.ssh
			chmod 0600 /var/www/${DOMAIN}/.ssh/authorized_keys
			SFTP_PASSWORD=`< /dev/urandom tr -dc A-Za-z0-9 | head -c23`
			echo "${SFTP_USER}:${SFTP_PASSWORD}" | chpasswd
			PWPUSH_WPADMIN=($(curl -s --data "cred=${WP_ADMIN_PASSWORD}&time=60&units=days&views=5" http://easypress.ca/pwpush/pwpusher_public/pw.php|grep setText|tr "'" "\n"))
			PWPUSH_DBUSER=($(curl -s --data "cred=${DB_PASSWORD}&time=60&units=days&views=5" http://easypress.ca/pwpush/pwpusher_public/pw.php|grep setText|tr "'" "\n"))
			PWPUSH_SFTP=($(curl -s --data "cred=${SFTP_PASSWORD}&time=60&units=days&views=5" http://easypress.ca/pwpush/pwpusher_public/pw.php|grep setText|tr "'" "\n"))
			mail -s "Your easyPress site for ${DOMAIN} is ready" vmg@easypress.ca <<EOM
Welcome aboard, ${WP_ADMIN_USER} ${LASTNAME}!

This email contains the information you need to access your new WordPress site hosted on easyPress. You will first need to add the following IP address to your DNS A record for ${DOMAIN} and possibly www.${DOMAIN} (call us if you need help):

198.74.61.42

Once you've updated your DNS records you can use the links below to access your site.

WordPress Website Address: http://${DOMAIN}
WordPress Admin Address: http://${DOMAIN}/wp-admin
WordPress Admin Username: ${WP_ADMIN_USER}
WordPress Admin Password: ${PWPUSH_WPADMIN[1]}

SFTP Username: ${SFTP_USER}
SFTP Password: ${PWPUSH_SFTP[1]}
SFTP Server: ${DOMAIN}
SFTP Port: 22

phpMyAdmin Address: https://${DOMAIN}/phpmyadmin
phpMyAdmin Username: ${DB_USER}
phpMyadmin Password: ${PWPUSH_DBUSER[1]}

This is the email address you provided when signing up:

${EMAIL}

If there is another address that's better for keeping you in touch with status updates and system announcements, please contact our support staff by email at support@easypress.ca or call us 416-535-8672 / 1-855-321-EASY (3279).

Thanks for signing up and have fun!

EOM
		else
			echo "Could not locate cache directory in nginx config."
			echo "Domain is: ${DOMAIN}"
			echo "File found in nginx_config directory is: ${DOMAIN}"
		fi
		cd ${CONFS}
	done
	exit 1
else
	echo nothing found
	exit 0
fi
