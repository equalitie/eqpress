#!/bin/bash
export PATH=/usr/bin:/bin:/usr/local/sbin:/usr/local/bin

function mysql_check() {
	local query="$1"
	local opts="$2"
	mysql_exec_result=$(
	printf "%s\n" \
		"[client]" \
		"user=${dbuser}" \
		"password=${dbpass}" \
		"host=${dbhost}" \
		"database=${dbname}" \
		| HOME="/sys" mysql --defaults-file=/dev/stdin "$opts" -e "$query"
	)
}

for docroot in /var/www/*/wordpress; do
	unset wpconfig userinfo passinfo hostinfo dbname dbuser dbpass dbhost
	echo "Start $docroot"
	cd "$docroot"
	wpconfig=$(cat "$docroot/wp-config.php" | egrep '^define.*' | egrep 'DB_NAME|DB_USER|DB_PASSWORD|DB_HOST')
	dbinfo=($(echo "$wpconfig" | grep DB_NAME |tr "'" '\n'))
	dbname="${dbinfo[3]}"
	userinfo=($(echo "$wpconfig" | grep DB_USER |tr "'" '\n'))
	dbuser="${userinfo[3]}"
	passinfo=($(echo "$wpconfig" | grep DB_PASSWORD |tr "'" '\n'))
	dbpass="${passinfo[3]}"
	hostinfo=($(echo "$wpconfig" | grep DB_HOST |tr "'" '\n'))
	dbhost="${hostinfo[3]}"

	if [ -z "$dbname" ] || [ -z "$dbuser" ] || [ -z "$dbpass" ] || [ -z "$dbhost" ]
	then
		echo "Could no login with given wp-config.php credentials [missing]"
		continue
	else
		if mysql_check ";"
		then
			cd ../
			mv "$dbname.sql.gz" "$dbname.sql.1.gz"
			mysqldump "$dbname" | gzip > "$dbname.sql.gz"
			echo "End $docroot"
		else
			echo "Could not login with given wp-config.php credentials"
		fi
	fi
	echo
done
