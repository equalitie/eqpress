#!/bin/bash

/usr/local/sbin/ep_install_console.sh $1
/usr/local/sbin/ep_bruteprotect.sh $1
/usr/local/sbin/ep_install_cache_purge.sh $1
