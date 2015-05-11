#!/bin/bash

#
# Event handler script for tracerouting to the host when a website is unreachable.
#

# What state is the HTTP service in?
case "$1" in

OK)
	# The service just came back up, so don't do anything...
;;

WARNING)
	echo -n "tracerouting to host (warning state)..."
	/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
	/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
;;

UNKNOWN)
	# We don't know what might be causing an unknown error, so don't do anything...
;;

CRITICAL)
	# Aha!  The HTTP service appears to have a problem - perhaps we should restart the server...
	# Is this a "soft" or a "hard" state?

	case "$2" in

	# We're in a "soft" state, meaning that Nagios is in the middle of retrying the
	# check before it turns into a "hard" state and contacts get notified...

	SOFT)
		# run traceroute at every step of the way during a failed check

		case "$3" in
		1)
			echo -n "tracerouting to host (1st soft critical state)..."
			/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
			/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
		;;

		2)
			echo -n "tracerouting to host (2nd soft critical state)..."
			/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
			/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
		;;

		3)
			echo -n "tracerouting to host (3rd soft critical state)..."
			/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
			/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
		;;
		esac
	;;

	HARD)
		echo -n "tracerouting to host (hard critical state)..."
		/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
		/usr/sbin/traceroute -n $4 >> /var/log/traceroutes/$5-`date +%Y%m%d%H%M%S`
	;;
	esac
;;
esac

exit 0


