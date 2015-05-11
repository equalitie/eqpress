#!/bin/bash
if [ -f /proc/sys/net/ipv4/conf/all/accept_redirects ]; then
	echo "   Kernel ignores all ICMP redirects"
	echo 0 > /proc/sys/net/ipv4/conf/all/accept_redirects
fi

if [ -f /proc/sys/net/ipv4/icmp_echo_ignore_broadcasts ]; then
   echo "   Kernel ignores ICMP Echo requests sent to broadcast/multicast addresses"
   echo 1 > /proc/sys/net/ipv4/icmp_echo_ignore_broadcasts
fi

if [ -f /proc/sys/net/ipv4/icmp_ignore_bogus_error_responses ]; then
   echo "   Kernel ignores bogus responses to broadcast frames"
   echo 1 > /proc/sys/net/ipv4/icmp_ignore_bogus_error_responses
fi

if [ -f /proc/sys/net/ipv4/tcp_syncookies ]; then
	echo "Kernel enforces syn cookies"
       echo 1 > /proc/sys/net/ipv4/tcp_syncookies
fi

if [ -f /proc/sys/net/ipv4/ip_forward ]; then
	echo "Kernel does not forward packets"
       echo 0 > /proc/sys/net/ipv4/ip_forward 
fi

if [ -f /proc/sys/net/ipv4/ip_always_defrag ]; then
       echo 1 > /proc/sys/net/ipv4/ip_always_defrag
fi

if [ -f /proc/sys/net/ipv4/icmp_ignore_bogus_error_responses ]; then
	echo 1 > /proc/sys/net/ipv4/icmp_ignore_bogus_error_responses
fi

if [ -f /proc/sys/net/ipv4/conf/all/rp_filter ]; then
       echo 1 > /proc/sys/net/ipv4/conf/all/rp_filter
fi

if [ -f /proc/sys/net/ipv4/conf/all/send_redirects ]; then
       echo 0 > /proc/sys/net/ipv4/conf/all/send_redirects
fi

if [ -f /proc/sys/net/ipv4/conf/all/accept_source_route ]; then
       echo 0 > /proc/sys/net/ipv4/conf/all/accept_source_route
fi

