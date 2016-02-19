#!/bin/sh
#
# Reports current network connection numbers to mrtg

TCP=`cat /proc/net/nf_conntrack | grep tcp | wc -l`
UDP=`cat /proc/net/nf_conntrack | grep udp | wc -l`

echo $TCP
echo $UDP
