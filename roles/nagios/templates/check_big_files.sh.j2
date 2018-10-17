#!/bin/bash

# Look for files that are too big - specify sizes in -w and -c in MB

find_files_bigger_than() {
    local FINDBASE=$1
    local BIGGERTHAN=$2

    find "$FINDBASE" -type f -size +${BIGGERTHAN}M -printf '%s:%p\n' | sort -nr | while read BIGFILE; do
        echo -n "$BIGFILE "
    done
}

unset ERROR WARN CRIT HELP

while getopts "c:w:h" options ; do
case $options in                 
  c )
   [ -n $OPTARG ] && CRIT=$OPTARG
  ;;
  w )
   [ -n $OPTARG ] && WARN=$OPTARG
  ;;
  h )
    HELP=ME
  ;;
esac
done

shift $((OPTIND-1))

[ "$CRIT" -gt 0 ] 2>/dev/null && [ "$CRIT" -lt 9999999 ] || ERROR="$ERROR crit value looks wrong;"
[ "$WARN" -gt 0 ] 2>/dev/null && [ "$WARN" -lt 9999999 ] || ERROR="$ERROR warn value looks wrong;"
[ "$WARN" -gt "$CRIT" ] 2>/dev/null && ERROR="$ERROR <crit> must be greater than <warn>;"
[ ${#@} = 0 ] 2>/dev/null && ERROR="$ERROR you must specify at least one path to look in"

if [ "$HELP" = "ME" ] || [ -n "$ERROR" ] ; then
    echo -e "\nUsage:\n\n`basename $0` -w <warn> -c <crit> <path> [<path> ...]\n"
    [ -n "$ERROR" ] && echo "Errors:$ERROR"
    exit 2
fi

TRIGGERWARN=0
TRIGGERCRIT=0
FILESOUT=""

for BASEDIR in $@; do
    OUT=$(find_files_bigger_than "${BASEDIR}" "$CRIT")
    [ -n "$OUT" ] && TRIGGERCRIT=1

    OUT=$(find_files_bigger_than "${BASEDIR}" "$WARN")
    [ -n "$OUT" ] && TRIGGERWARN=1
    FILESOUT="${FILESOUT} ${OUT}"
done

RET="OK"
RETCODE=0
[ "$TRIGGERWARN" = 1 ] && RET="WARN" && RETCODE=1
[ "$TRIGGERCRIT" = 1 ] && RET="CRIT" && RETCODE=2

echo "BIGFILES $RET - ${FILESOUT:1}"
exit $RETCODE
