#!/bin/bash
export PATH=/bin:/usr/bin:/usr/local/sbin
PSLIMIT=14
PS=`ps ax|grep php-fpm|grep www|wc -l`
hostname=`hostname`
if [ "$PS" -gt $PSLIMIT ]; then
	echo "warning: php-fpm www pool has reached $PS procs on ${hostname}"
	exit 1
else
	echo "info: php-fpm www pool has reached $PS procs on ${hostname}"
	exit 0
fi
