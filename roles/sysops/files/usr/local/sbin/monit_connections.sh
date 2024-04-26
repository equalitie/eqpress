#!/bin/sh
export PATH=/bin:/usr/bin
hostname=`hostname`

conn=`netstat -an|egrep ':80|:443'|grep tcp|grep ESTAB|wc -l`
if [ $conn -gt 200 ]; then
	echo
	echo eqpress has ${conn} active connections
	exit 1
else
	exit 0
fi
