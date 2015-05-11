#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

for site in `ls -1d /var/www/*`; do
	echo ${site}
	cd ${site}/wordpress
	for ctr in 1 2; do 
		email=$(wp --allow-root user get ${ctr} --field=user_email)
		allnames=$(wp --allow-root user get ${ctr} --field=display_name)
		n=($allnames)
		echo \"${email}\",\"${n[0]}\",\"${n[1]}\"
	done
done

