#!/bin/bash
echo "monit_php-fpm_procs.sh is deprecated and here as reference only"
exit 1

# ---------------------------------------------------------------------------
# Reference only, never runs. Original script:
#export PATH=/bin:/usr/bin:/usr/local/sbin
#PS=`ps ax|grep php-fpm|grep www|wc -l`
#if [ "$PS" -gt 14 ]; then
#	echo "warning: php-fpm www pool has reached $PS procs"
#	exit 1
#else
#	echo "info: php-fpm www pool has reached $PS procs"
#	exit 0
#fi
