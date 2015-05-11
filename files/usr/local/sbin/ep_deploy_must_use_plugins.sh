#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

for site in `ls -1d /var/www/*`; do
    #site=$(basename $sites)
    if [ -d "$site/wordpress" ]; then
	    mu_dir=${site}/wordpress/wp-content/mu-plugins
	    if [ ! -d ${mu_dir} ]; then
		    mkdir ${mu_dir}
	    fi
        rsync -av /var/www/easypress.ca/bruteprotect/* ${site}/wordpress/wp-content/mu-plugins
	    #cp -r /var/www/easypress.ca/console/plugin/* ${MU_DIR}/
	    #cp -r /var/www/easypress.ca/cache-purge/* ${MU_DIR}/
	    #chepown ${site}
	fi
done
