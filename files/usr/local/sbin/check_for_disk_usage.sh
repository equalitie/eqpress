#!/bin/bash
export PATH=/usr/local/bin:/usr/local/sbin:/bin:/usr/bin:/sbin:/usr/sbin
THRESHOLD=50
WARNPARTS=$(
df -h | tail -n +2 | while read line; do
  MOUNTPOINT=$(echo $line | cut -d' ' -f 6-)
  FILLPC=$(echo $line | cut -d' ' -f 5 | sed 's/%$//')
  [ $FILLPC -gt $THRESHOLD ] && echo -n "$MOUNTPOINT, "
done
)
WARNPARTS=${WARNPARTS%??}

if [ -n "$WARNPARTS" ]; then
  df -h | mail -s "Partitions with low disk space on $(hostname): $WARNPARTS" sysadmin@easypress.ca
fi
