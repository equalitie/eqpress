#!/bin/bash
export PATH=/bin:/usr/bin
if [ ! -z $1 ]; then
	if [ -d /var/cache/nginx/$1 ]; then
		rm -r /var/cache/nginx/$1/*
		echo cache deleted
	else
		echo directory does not exist
	fi
else 
	echo must supply a domain name
fi
