#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

host=$(hostname)
mailq_count=`/usr/bin/mailq | wc -l`
if [ $mailq_count -gt 60 ]; then
        echo "Mail messages stuck in the queue on ${host}: ${mailq_count}"
        exit 1
fi
exit 0
