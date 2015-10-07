#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

basedir=/var/www/easypress.ca
ansible=${basedir}/ansible

domain=${1}
new_url=${2}
location=${3}

cd ${ansible}
source ./hacking/env-setup -q
old_url=`ansible ${location} -u root -o -m command -a "/usr/local/sbin/wp --allow-root --path=/var/www/${domain}/wordpress option get siteurl" | awk '{print $8}'`
ansible ${location} -m command -a "/usr/local/sbin/wp --allow-root --path=/var/www/${domain}/wordpress search-replace ${old_url} ${new_url}" -u root -v
