#!/bin/bash
#
# monit_php_error_logs.sh - 2013/May/06
#
# Send alert when php-errors.log file in docroot exceeds x MB
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

MAX=15000000
RETURN=0
BIGLOG=$(find /var/www -maxdepth 3 -name php-errors.log -size +$MAX -exec ls -lh {} \;)

if [ -n "$BIGLOG" ]; then
  echo Size of log file should not exceed $MAX bytes
  echo $BIGLOG
  RETURN=1
fi
exit $RETURN
