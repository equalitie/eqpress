#!/bin/bash
#
# monit_check_mailq.sh
#
# Run every 60s by the systemd timer mon_check-mailq (timed-jobs).
# Counts messages in the Postfix queue and alerts via eqpress_alert.sh when
# the count goes over LIMIT and when it recovers.
# Exit 1 while over the limit (shown in the journal).
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/sbin

LIMIT=10
host=$(hostname)

# postqueue -j prints one JSON line per queued message
if ! out=$(postqueue -j 2>/dev/null); then
	echo "Error: postqueue failed"
	exit 2
fi
count=$(printf '%s' "$out" | grep -c .)

if [ "$count" -gt "$LIMIT" ]; then
	echo "Mail messages stuck in the queue on ${host}: ${count} (limit ${LIMIT})"
	eqpress_alert.sh mailq problem "${count} mail messages stuck in the queue on ${host} (limit ${LIMIT}). Check with: mailq"
	exit 1
fi
eqpress_alert.sh mailq ok "Mail queue on ${host} back to ${count} messages (limit ${LIMIT})"
exit 0
