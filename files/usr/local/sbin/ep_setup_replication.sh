#!/bin/bash
#
# ep_setup_replication.sh
#
#"%Y-%m-%d_%H-%M-%S"
#
export PATH=/usr/bin:/bin:/usr/local/bin:/usr/local/sbin:/usr/sbin:/sbin

now=`date "+%Y%m%d%H%M%S"`
cd /var/backups && mkdir mysql-snapshot-${now}
innobackupex /var/backups/mysql-snapshot-${now} > /dev/null 2>&1
if [ $? -ne 0 ]; then 
    echo innobackupex snapshot failed
    exit 1
fi
snap_dir=`ls /var/backups/mysql-snapshot-${now}`
innobackupex --apply-log /var/backups/mysql-snapshot-${now}/${snap_dir}/ > /dev/null 2>&1
if [ $? -ne 0 ]; then 
    echo innobackupex apply log failed
    exit 1
fi
echo "/var/backups/mysql-snapshot-${now}/${snap_dir}/"
exit 0
