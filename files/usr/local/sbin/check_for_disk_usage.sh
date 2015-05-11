#!/bin/bash
export PATH=/usr/local/bin:/usr/local/sbin:/bin:/usr/bin:/sbin:/usr/sbin
disku=`df -h|head -2|tail -1|awk '{print $5}'|sed -e 's/%//'`
if [ "$disku" -gt 95 ]; then
	df -h | mail -s "Disk is low on $(hostname)" sysadmin@easypress.ca
fi
