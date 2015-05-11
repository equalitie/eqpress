#!/bin/sh
#
# Reports current network connection numbers to mrtg
# mf@in-tuition.net

TCP=`cat /proc/net/nf_conntrack | grep tcp | wc -l`
UDP=`cat /proc/net/nf_conntrack | grep udp | wc -l`

echo $TCP
echo $UDP
