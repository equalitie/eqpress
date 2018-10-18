#!/bin/bash

# Look at logs produced by backup_from_master.sh and tell if the time spent to rsync was too long

unset ERROR WARN CRIT MAXAGE HELP

while getopts "c:w:m:h" options ; do
case $options in                 
  c )
   [ -n $OPTARG ] && CRIT=$OPTARG
  ;;
  w )
   [ -n $OPTARG ] && WARN=$OPTARG
  ;;
  m )
   [ -n $OPTARG ] && MAXAGE=$OPTARG
  ;;
  h )
    HELP=ME
  ;;
esac
done

[ "$CRIT" -gt 0 ] 2>/dev/null && [ "$CRIT" -lt 9999999 ] || ERROR="$ERROR crit value looks wrong;"
[ "$WARN" -gt 0 ] 2>/dev/null && [ "$WARN" -lt 9999999 ] || ERROR="$ERROR warn value looks wrong;"
[ "$WARN" -gt "$CRIT" ] 2>/dev/null && ERROR="$ERROR <crit> must be greater than <warn>;"
[ "$MAXAGE" -gt 0 ] 2>/dev/null && [ "$MAXAGE" -lt 9999999 ] || ERROR="$ERROR maxage value looks wrong;"

if [ "$HELP" = "ME" ] || [ -n "$ERROR" ] ; then
    echo -e "\nUsage:\n\n`basename $0` -w <warn> -c <crit> -m <maxage>\n"
    [ -n "$ERROR" ] && echo "Errors:$ERROR"
    exit 2
fi

LOGFILE=/var/log/backup_from_master.log

# Get last log line that matches the expected format
LASTLOGLINE=$(egrep '^[0-9]{10} backup-end [A-Za-z0-9]+ .*' "$LOGFILE" 2>/dev/null | tail -n 1)

if [ -z "$LASTLOGLINE" ]; then
    echo CRITICAL - No parsable log line in log file
    exit 2
fi

DATE_LAST_FINISH=$(echo "$LASTLOGLINE" | cut -d' ' -f 1)
RETURN_STRING=$(echo "$LASTLOGLINE" | cut -d' ' -f 3)
MESSAGE=$(echo "$LASTLOGLINE" | cut -d' ' -f 4-)

AGE=$(($(date +%s) - $DATE_LAST_FINISH))

if [ $AGE -gt $MAXAGE ]; then
    echo CRITICAL - Last backup finished too long ago: $AGE seconds
    exit 2
fi
if [ "$RETURN_STRING" != "OK" ]; then
    echo CRITICAL - Log line: "$RETURN_STRING" "$MESSAGE"
    exit 2
fi

DURATION_MS=$(echo "$MESSAGE" | sed -r '/^[0-9]+ms$/!d;s/ms//')
if [ -z "$DURATION_MS" ]; then
    echo CRITICAL - Cannot parse last reported execution time "$MESSAGE"
    exit 2
fi

DURATION=$((${DURATION_MS}/1000))
RETSTR="OK"
RETCODE=0
if [ $DURATION -gt $CRIT ]; then
    RETSTR="CRITICAL"
    RETCODE=2
fi
if [ $DURATION -gt $WARN ]; then
    RETSTR="WARNING"
    RETCODE=1
fi

echo $RETSTR - duration=$DURATION age=$AGE
exit $RETCODE
