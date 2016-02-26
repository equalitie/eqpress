#!/bin/sh
#
# Checks load and turns off SA if too high

LOAD=`cat /proc/loadavg | gawk '{print $3}'`

#Now get rid of the fraction and make it an integer.  Do it simply by grabbing everything before the '.'

LOAD2=`echo $LOAD | gawk -F . '{print $1}'`

if [ $LOAD2 -gt 2 ]; then
	echo "Load is greater than 2!"
	echo "Load on sa1 is above 2, shutting down SA" | mail -s "SA1 Load Alert" your-noc@example.com
	#/usr/local/bin/svc -d /service/spamd
	/sbin/shutdown -r now
fi

