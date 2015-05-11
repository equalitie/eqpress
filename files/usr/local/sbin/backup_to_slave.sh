#!/bin/bash
#
# backup_to_slave.sh
#
# If the slave alias exists in /etc/hosts then this script will rsync
# the following files over.
#
export PATH=/bin:/usr/bin
if ping -c 1 slave > /dev/null 2>&1; then
    rsync -av --delete \
	    --exclude '*.zip' \
	    --exclude '*.tar.gz' \
	    --exclude '*.tgz' \
	    --exclude '.git*' \
	    --exclude '*.log' \
	    --exclude '*.mp4' \
	    --exclude 'easypress.ca/' \
	    --exclude '.sessions/' \
	    /var/www slave:/var
    rsync -av --delete --exclude 'easypress.ca' /etc/nginx slave:/etc
    rsync -av /etc/ssl slave:/etc
else
    echo "backup_to_slave.sh: error, cant reach slave."
fi
