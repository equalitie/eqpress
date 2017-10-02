#!/bin/bash
export PATH=/usr/bin:/bin:/usr/local/sbin:/usr/local/bin
for docroot in /var/www/*/wordpress; do
	echo $docroot
	cd $docroot
	dbinfo=($(grep DB_NAME ${docroot}/wp-config.php |tr "'" '\n'))
	dbname=${dbinfo[3]}
	wp --allow-root db export ${dbname}.sql || mysqldump ${dbname} > ${dbname}.sql
	gzip ${dbname}.sql
	mv ${dbname}.sql.gz ../
	echo
done
