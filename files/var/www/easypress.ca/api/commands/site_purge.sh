#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

basedir=/var/www/easypress.ca
ansible=${basedir}/ansible

location=${1}
domain=${2}

cd ${ansible}
source ./hacking/env-setup -q
ansible ${location} -m command -a "/usr/local/sbin/ep_purge_site.sh ${domain}" -u root -v
