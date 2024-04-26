#!/bin/bash
#
# wp_update_plugins.sh - 2013/Apr/29
#
# update a list of plugins provided on the command line or stdin
#
export PATH=/bin:/usr/bin:/usr/local/bin:/usr/local/sbin:/sbin:/usr/sbin

PWD=$(pwd)

do_update_with_file() {
	echo -n "Backing up database for ${DOMAIN} ..."
	if wp --allow-root --quiet --path=${ROOT} db export; then
		echo "done."
		echo -n "Compressing and relocating database file for ${DOMAIN} ..."
		gzip -5 *.sql
		mv *.sql.gz ${ROOT}/../
		echo "done."
		echo -n "Updating $PLUGIN for $DOMAIN ..."
		#if wp --allow-root --path=${ROOT} plugin install ${FILE} --force; then
		#if wp --allow-root --path=${ROOT} plugin install ${PLUGIN} --force; then
		if wp --allow-root --path=${ROOT} plugin update ${PLUGIN}; then
			echo "done."
		else
			echo "failed!"
		fi
		chwebown ${ROOT}/wp-content/plugins/${PLUGIN}
	else
		echo "failed!"
		echo "Update of ${PLUGIN} for ${DOMAIN} aborting."
	fi
}

do_update_all_plugins() {
	echo -n "Begining plugin updates for ${DOMAIN} ..."
	if wp --allow-root --quiet --path=${ROOT} db export; then
		mv ${ROOT}/*.sql ${ROOT}/../
		wp --allow-root --path=${ROOT} plugin update-all
		chwebown ${ROOT}/wp-content/plugins/
	else
		echo "DB backup failed!"
	fi
}

if [ -n "$1" ]; then
	if [ "$1" = "all" ]; then
		for ROOT in `find /var/www -maxdepth 2 -name wordpress`; do
			cd $ROOT
			DOMAIN=`echo ${ROOT}|sed -e 's/\/var\/www\/\(.*\)\/wordpress/\1/'`
			do_update_all_plugins
		done
	else
	    PLUGIN=$1
	    FILE=$2
			for ROOT in `find /var/www -maxdepth 2 -name wordpress`; do
				cd $ROOT
				DOMAIN=`echo $ROOT|sed -e 's/\/var\/www\/\(.*\)\/wordpress/\1/'`
				if find $ROOT/wp-content/plugins/ -type d | grep ${PLUGIN} > /dev/null; then
					wp --allow-root --path=${ROOT} plugin status ${PLUGIN} | grep -i "update available" && do_update_with_file
				fi
			done
	fi
else
	echo "Usage: wp_update_plugin.sh all | plugin1 [plugin2 ...] [path_to_plugin]"
	echo "Update all plugins or those provided as options to this script."
	echo
	echo "Example: wp_update_plugin.sh all - update all plugin on all sites."
	echo "Example: wp_update_plugin.sh wp-super-cache w3-total-cache - update the listed plugins on all sites."
	echo "Example: wp_update_plugin.sh jetpack /path/to/jetpack.2.9.3.zip"
	echo
fi

cd $PWD
exit 0
