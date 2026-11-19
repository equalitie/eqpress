#!/bin/bash
#
# eq_admin_emails.sh
#
# Print the email and name of WordPress users 1 and 2 for every site as
# CSV lines: "email","first","last"
#
# wp-cli runs wp-config.php (PHP), so it runs as that file's owner, never as
# root.
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

for docroot in /var/www/*/wordpress; do
	[ -f "$docroot/wp-config.php" ] || continue
	site=${docroot%/wordpress}
	echo "$site"
	owner=$(stat -c %U "$docroot/wp-config.php")
	root_flag=""
	[ "$owner" = "root" ] && root_flag="--allow-root"
	for ctr in 1 2; do
		email=$(sudo -u "$owner" wp $root_flag --path="$docroot" user get "$ctr" --field=user_email 2>/dev/null)
		allnames=$(sudo -u "$owner" wp $root_flag --path="$docroot" user get "$ctr" --field=display_name 2>/dev/null)
		read -r first last _ <<< "$allnames"
		echo "\"${email}\",\"${first}\",\"${last}\""
	done
done
