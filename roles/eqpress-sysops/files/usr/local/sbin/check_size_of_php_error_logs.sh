#!/bin/bash
#
# monit_php_error_logs.sh - 2013/May/06
#
# Send alert when php-errors.log file in docroot exceeds x MB
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

MAX=15000000
RETURN=0
for LOG in `find /var/www -maxdepth 3 -name php-errors.log`; do
	SIZE=`du -b ${LOG}|cut -f 1`
	if [ "$MAX" -lt "$SIZE" ]; then
		if [ 0 -eq "$RETURN" ]; then
			echo Size of log file should not exceed: $MAX bytes
		fi
		ls -lh ${LOG}
		RETURN=1
	fi
done
exit $RETURN
