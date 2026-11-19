#!/bin/bash
echo "eq_purge_cache.sh is deprecated and here as reference only"
echo "Per-site cache folders no longer exist. Purge from the site with the nginx-helper plugin."
exit 1

# ---------------------------------------------------------------------------
# Reference only, never runs. Original script:
#export PATH=/bin:/usr/bin
#if [ ! -z $1 ]; then
#	if [ -d /var/cache/nginx/$1 ]; then
#		rm -r /var/cache/nginx/$1/*
#		echo cache deleted
#	else
#		echo directory does not exist
#	fi
#else 
#	echo must supply a domain name
#fi
