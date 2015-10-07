#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

basedir=/var/www/easypress.ca
ansible=${basedir}/ansible

node=${1}
domain=${2}

cd ${ansible}
source ./hacking/env-setup -q
if out=`ansible ${node} -m command -a "/usr/local/sbin/ep_chpass.sh ${domain}" -u root` ; then
    echo ${out}
    exit 0
else
    echo ${out}
    exit 1
fi