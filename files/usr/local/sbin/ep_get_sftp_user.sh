#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin
if [ -z $1 ]; then
	echo "No domain name supplied"
	exit 1
else   
        SITE=$1
fi
DOCROOT=/var/www/${SITE}/wordpress
if [ ! -d ${DOCROOT} ]; then
        echo ${DOCROOT} does not exist on this node.
        exit 1
fi
USER_INFO=($(grep DB_USER ${DOCROOT}/wp-config.php |tr "'" '\n'))
USER=${USER_INFO[3]}
echo ${USER}
