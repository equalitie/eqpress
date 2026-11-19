#!/bin/bash
#
# eq_provision_gpg_key.sh <key id or fingerprint> <keyserver> <refresh days>
#
# Run by the provision-server role (ansible.builtin.script, as root). Makes
# sure root's keyring holds the public key monit_provision.sh encrypts the
# provision archives to.
#
# The keyserver is often down, so it is only contacted when the key is
# missing, or no longer usable for encryption within <refresh days> (a
# refresh picks up an extended expiry date). Whatever is in the keyring is
# then checked locally.
#
# Exit codes (the role maps them):
#    0  key present and usable, nothing changed
#   10  key imported or refreshed (changed)
#   20  warning: key missing, expired or expiring (keyserver down or the
#       key was not extended); provisioning goes on, monit_provision.sh
#       keeps unencrypted archives root-only and emails
#    1  error: wrong or ambiguous key in the keyring, or bad arguments
#
export PATH=/usr/bin:/bin:/usr/sbin:/sbin

id=$(tr -d ' ' <<<"${1#0x}" | tr 'a-f' 'A-F')
keyserver=$2
days=$3

if [[ ! "$id" =~ ^([0-9A-F]{16}|[0-9A-F]{40})$ ]]; then
	echo "auto_provision_gpg_id must be a 16 character long key ID or a 40 character fingerprint, not '$1'"
	exit 1
fi
[[ "$days" =~ ^[0-9]+$ ]] || { echo "refresh days must be a number"; exit 1; }

now=$(date +%s)
limit=$(( now + days * 86400 ))

# Sets: count (matching primary keys), fpr (primary fingerprint), all_fpr
# (primary and subkey fingerprints) and usable_until (latest expiry of a
# valid encryption key; 0 = never expires, -1 = none usable).
inspect() {
	local listing
	count=0; fpr=""; all_fpr=""; usable_until=-1
	listing=$(gpg --batch --with-colons --fixed-list-mode --list-keys -- "$id" 2>/dev/null) || return 0
	count=$(grep -c '^pub:' <<<"$listing")
	fpr=$(awk -F: '/^pub:/{p=1; next} p && /^fpr:/{print $10; exit}' <<<"$listing")
	all_fpr=$(awk -F: '/^fpr:/{print $10}' <<<"$listing")
	# pub/sub lines: field 2 validity, 7 expiry (epoch, empty = never),
	# 12 capabilities (lower case e = this key can encrypt)
	usable_until=$(awk -F: -v now="$now" '
		BEGIN { best = -1 }
		# an expired or revoked primary key makes every subkey unusable
		/^pub:/ && $2 ~ /[er]/ { dead = 1 }
		/^(pub|sub):/ {
			if (dead) next
			if ($2 ~ /[erdi]/ || $12 !~ /e/) next
			if ($7 == "") { best = 0; next }
			if ($7 > now && best != 0 && $7 > best) best = $7
		}
		END { print best }' <<<"$listing")
}

usable_past_limit() {
	[ "$usable_until" -eq 0 ] || [ "$usable_until" -gt "$limit" ]
}

describe() {
	if [ "$usable_until" -eq 0 ]; then
		echo "does not expire"
	elif [ "$usable_until" -gt 0 ]; then
		echo "usable until $(date -u -d "@$usable_until" '+%F')"
	else
		echo "expired or not usable for encryption"
	fi
}

inspect
changed=0
if [ "$count" -eq 0 ] || ! usable_past_limit; then
	if [ "$count" -eq 0 ]; then
		why="not in root's keyring"
	else
		why="$(describe), refreshing"
	fi
	before=$(gpg --batch --with-colons --list-keys -- "$id" 2>/dev/null | sha256sum)
	if out=$(timeout 60 gpg --batch --keyserver "$keyserver" --recv-keys -- "$id" 2>&1); then
		after=$(gpg --batch --with-colons --list-keys -- "$id" 2>/dev/null | sha256sum)
		[ "$before" != "$after" ] && changed=1
		echo "Key $id $why: fetched from $keyserver"
	else
		echo "Key $id $why: could not fetch from $keyserver ($(tail -1 <<<"$out"))"
	fi
	inspect
fi

if [ "$count" -gt 1 ]; then
	echo "ERROR: $count keys in root's keyring match $id; remove the wrong one (gpg --delete-keys <fingerprint>)"
	exit 1
fi
# The ID may be the primary key or one of its subkeys
if [ "$count" -eq 1 ] && ! grep -q -- "$id\$" <<<"$all_fpr"; then
	echo "ERROR: the key in root's keyring for $id has fingerprint $fpr, which does not match"
	exit 1
fi
if [ "$count" -eq 0 ]; then
	echo "WARNING: no key $id in root's keyring; provision archives cannot be encrypted until it is imported"
	exit 20
fi
if ! usable_past_limit; then
	echo "WARNING: key $fpr $(describe); extend it and publish it to $keyserver, or import it by hand"
	exit 20
fi

echo "Key $fpr $(describe)"
[ "$changed" -eq 1 ] && exit 10
exit 0
