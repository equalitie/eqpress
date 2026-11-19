#!/bin/bash
#
# monit_connections.sh
#
# Run every 60s by the systemd timer mon_connections (timed-jobs).
# Counts established incoming web connections (ports 80/443) and alerts via
# eqpress_alert.sh when the count goes over LIMIT and when it recovers.
# Exit 1 while over the limit (shown in the journal).
#
export PATH=/bin:/usr/bin:/usr/local/sbin

LIMIT=200
host=$(hostname)

if ! out=$(ss -Htn state established '( sport = :80 or sport = :443 )'); then
	echo "Error: ss failed"
	exit 2
fi
conn=$(printf '%s' "$out" | grep -c .)

if [ "$conn" -gt "$LIMIT" ]; then
	echo "eqpress has ${conn} active web connections on ${host} (limit ${LIMIT})"
	eqpress_alert.sh connections problem "${host} has ${conn} active web connections (limit ${LIMIT})"
	exit 1
fi
eqpress_alert.sh connections ok "${host} web connections back to ${conn} (limit ${LIMIT})"
exit 0
