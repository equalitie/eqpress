#!/bin/sh
export PATH=/bin:/usr/bin
hostname=`hostname`

conn=`ss -n -t state established '( dport = :80 or dport = :443 )'|tail +2|wc -l`
if [ $conn -gt 200 ]; then
	echo
	echo ${hostname} has ${conn} active connections
	exit 1
else
	exit 0
fi
