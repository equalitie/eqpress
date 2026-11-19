#!/bin/bash
#
# wp_update_plugins.sh - 2013/Apr/29
#
# Update plugins on every site:
#   wp_update_plugins.sh all                    - update all plugins
#   wp_update_plugins.sh <plugin> [<plugin> ...] - update only these plugins
#                                                (exact plugin slugs)
#
# Before updating a site its database is saved to
#   /var/www/<site>/<site>-pre-plugin-update-<date>.sql.gz  (root:sftponly 0640)
#
# wp-cli loads WordPress and every active plugin, so it runs as the owner of
# the site's wp-config.php (site user when locked down, www-data otherwise),
# never as root. Updated files keep that owner, so lockdown is unchanged.
#
export PATH=/bin:/usr/bin:/usr/local/bin:/usr/local/sbin:/sbin:/usr/sbin
set -o pipefail

SFTPGROUP=sftponly

usage() {
	echo "Usage: wp_update_plugins.sh all | plugin1 [plugin2 ...]"
	echo "Update all plugins or those provided as options to this script, on all sites."
	echo
	echo "Example: wp_update_plugins.sh all - update all plugins on all sites."
	echo "Example: wp_update_plugins.sh wp-super-cache w3-total-cache - update the listed plugins on all sites."
	exit 1
}

# wp-cli as the owner of the site's wp-config.php
wp_as() {
	local root=$1 owner root_flag=""
	shift
	owner=$(stat -c %U "$root/wp-config.php") || return 1
	[ "$owner" = "root" ] && root_flag="--allow-root"
	sudo -u "$owner" wp $root_flag --path="$root" "$@"
}

# Save the database next to the site (root writes the file)
backup_db() {
	local root=$1 domain=$2 site out
	site=${root%/wordpress}
	out="$site/${domain}-pre-plugin-update-$(date +%F-%H%M%S).sql.gz"
	echo -n "Backing up database for ${domain} ..."
	if wp_as "$root" db export - --quiet | gzip -5 > "$out.tmp" && [ -s "$out.tmp" ]; then
		mv "$out.tmp" "$out"
		chown root:"$SFTPGROUP" "$out"
		chmod 0640 "$out"
		echo "done."
		return 0
	fi
	rm -f "$out.tmp"
	echo "failed!"
	return 1
}

[ -n "$1" ] || usage
if [ "$1" != "all" ]; then
	for p in "$@"; do
		[[ "$p" =~ ^[a-z0-9._-]+$ ]] || { echo "Error: invalid plugin name '$p'"; exit 1; }
	done
fi

rc=0
for ROOT in /var/www/*/wordpress; do
	[ -f "$ROOT/wp-config.php" ] || continue
	DOMAIN=$(basename "${ROOT%/wordpress}")

	if [ "$1" = "all" ]; then
		echo "Beginning plugin updates for ${DOMAIN} ..."
		if backup_db "$ROOT" "$DOMAIN"; then
			wp_as "$ROOT" plugin update --all || rc=1
		else
			echo "Update of ${DOMAIN} aborted: DB backup failed."
			rc=1
		fi
		continue
	fi

	# Only the listed plugins that are installed here and have an update
	available=$(wp_as "$ROOT" plugin list --update=available --field=name 2>/dev/null)
	todo=()
	for p in "$@"; do
		grep -qxF -- "$p" <<< "$available" && todo+=("$p")
	done
	[ "${todo[*]}" = "" ] && continue

	if backup_db "$ROOT" "$DOMAIN"; then
		echo -n "Updating ${todo[*]} for ${DOMAIN} ..."
		if wp_as "$ROOT" plugin update "${todo[@]}" --quiet; then
			echo "done."
		else
			echo "failed!"
			rc=1
		fi
	else
		echo "Update of ${todo[*]} for ${DOMAIN} aborted: DB backup failed."
		rc=1
	fi
done
exit $rc
