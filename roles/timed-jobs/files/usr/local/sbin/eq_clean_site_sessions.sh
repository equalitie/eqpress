#!/bin/bash
#
# eq_clean_site_sessions.sh
#
# Run from cron (/etc/cron.d/eqpress_php). Deletes PHP session files from each
# site's own session folder, /var/www/<site>/wordpress/.sessions (set by nginx
# with PHP_VALUE session.save_path), once they have been idle longer than
# session.gc_maxlifetime.
#
# Why: PHP's own session cleanup is off (session.gc_probability = 0, the
# Ubuntu default) and Ubuntu's sessionclean only cleans the session.save_path
# from php.ini, not these per-site folders.
#
# session.gc_maxlifetime is read from the PHP-FPM php.ini (and its conf.d) of
# every installed PHP version, the same way Ubuntu's sessionclean does; the
# largest value is used. Nothing is hardcoded here.
#
export PATH=/usr/sbin:/usr/bin:/sbin:/bin

maxlifetime=0
for version in $(phpquery -V 2>/dev/null); do
	ini=/etc/php/$version/fpm/php.ini
	[ -f "$ini" ] || continue
	value=$(PHP_INI_SCAN_DIR=/etc/php/$version/fpm/conf.d/ "php$version" -c "$ini" \
		-d "error_reporting='~E_ALL'" -r 'echo (int) ini_get("session.gc_maxlifetime");' 2>/dev/null)
	if [[ "$value" =~ ^[0-9]+$ ]] && [ "$value" -gt "$maxlifetime" ]; then
		maxlifetime=$value
	fi
done

if [ "$maxlifetime" -le 0 ]; then
	echo "eq_clean_site_sessions.sh: could not read session.gc_maxlifetime from any PHP-FPM php.ini" >&2
	exit 1
fi

# find -mmin works in whole minutes; round up so nothing is removed early
minutes=$(( (maxlifetime + 59) / 60 ))

# -maxdepth 1 and the default -P (do not follow symlinks): only real session
# files directly in each .sessions folder are removed.
ionice -c3 nice -n 19 find /var/www/*/wordpress/.sessions -maxdepth 1 -type f \
	-name 'sess_*' -mmin +"$minutes" -delete 2>/dev/null
exit 0
