#!/bin/bash
#
# eq_deactivate_site.sh <domain>
#
# Deactivate a site so the default Be Happy page shows up (removes its
# nginx sites-enabled link and reloads nginx).
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

deactivate() {
	local domain=$1
	if [ -e "/etc/nginx/sites-enabled/${domain}" ]; then
		rm -- "/etc/nginx/sites-enabled/${domain}"
	else
		if [ -e "/etc/nginx/sites-available/${domain}" ]; then
			echo "${domain} is already deactivated."
		else
			echo "${domain} does not exist."
		fi
		exit 1
	fi
	nginx -s reload 2> /dev/null || { echo "nginx reload failed"; exit 1; }
}

if [ -z "$1" ]; then
	echo "Error: No domain name provided."
	echo "${0} example.co"
	exit 1
fi
if [[ ! "$1" =~ ^[A-Za-z0-9][A-Za-z0-9._-]*$ ]]; then
	echo "Error: invalid domain name '$1'"
	exit 1
fi
deactivate "$1"
