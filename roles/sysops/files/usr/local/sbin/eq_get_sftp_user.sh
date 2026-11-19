#!/bin/bash
#
# eq_get_sftp_user.sh <domain>
#
# Print the SFTP account of a site. Uses eq_site_sftp_user, which only
# returns the account provisioning created for that site (and says why
# otherwise).
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

if [ -z "$1" ]; then
	echo "No domain name supplied"
	exit 1
fi
exec eq_site_sftp_user "$1"
