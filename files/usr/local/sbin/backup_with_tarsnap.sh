#!/bin/bash

export PATH=/bin:/usr/bin:/usr/sbin:/sbin:/usr/local/bin:/usr/local/sbin

do_tarsnap() {
	echo -n "Starting $TYPE backup for $SITE..."
	tarsnap cf ${NOW}-${TYPE}-${SITE} -C / var/www/${SITE}
	if [ $? = 0 ] ; then
		echo done
	else
		echo Backup failed: $?
	fi
}

prune_archives() {
	COUNT=`grep ${TYPE}-${SITE} ${ARCHIVES} | wc -l`
	if [ $COUNT -gt ${TYPE_TO_COUNT[$TYPE]} ]; then
		echo -n "$COUNT $TYPE backs exist. Deleting the oldest backup: "
		ARCHIVE_TO_DELETE=`grep ${TYPE}-${SITE} ${ARCHIVES} | head -1`
		tarsnap df ${ARCHIVE_TO_DELETE}
		if [ $? = 0 ] ; then
			echo ${ARCHIVE_TO_DELETE}
		else
			echo Delete of ${ARCHIVE_TO_DELETE} failed: $?
		fi
	fi
}

# What day of the week to take the weekly snapshot?
WEEKLY_DOW=6
# Do you want to use UTC time? (1 = Yes) Default = 0, use local time.
USE_UTC=0
# How many daily, weekly, monthly backups to keep
declare -A TYPE_TO_COUNT
TYPE_TO_COUNT=([DAILY]=30 [WEEKLY]=15 [MONTHLY]=6)

# The day of the week (Monday = 1, Sunday = 7)
DOW=$(date +%u)
# The calendar day of the month
DOM=$(date +%d)
# The last day of the current month
LDOM=$(echo $(cal) | awk '{print $NF}')

# Today's date
NOW=$(date +%Y%m%d)
if [ "$USE_UTC" = "1" ] ; then
	NOW=$(date -u +%Y%m%d-%H)
fi

# Daily, weekly or monthly backup?
TYPE=DAILY	#Default to DAILY
if [ "$DOM" = "$LDOM" ]; then
	TYPE=MONTHLY
else
	if [ "$DOW" = "$WEEKLY_DOW" ]; then
		TYPE=WEEKLY
	fi
fi

# create the log directory if it doesn't exist
if [ ! -d /var/log/tarsnap ]; then
	mkdir /var/log/tarsnap
fi
ARCHIVES=/var/log/tarsnap/archives-${NOW}

# Get and save a listing of existing backups
tarsnap --list-archives | sort > $ARCHIVES

ALL_SITES=`ls -1 /var/www`
for SITE in ${ALL_SITES}; do
	if [ -d /var/www/${SITE} ]; then
		do_tarsnap
		prune_archives
	fi
done

tarsnap --print-stats

