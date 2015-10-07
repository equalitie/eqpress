#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

basedir=/var/www/easypress.ca
ansible=${basedir}/ansible

node=${1}
domain=${2}

cd ${ansible}
source ./hacking/env-setup -q
if out=`ansible ${node} -o -m command -a "wp --allow-root --path=/var/www/${domain}/wordpress option get siteurl" -u root` ; then
    siteurl=`echo ${out} | awk '{print $8}'`
else
    echo ${out}
    exit 1
fi

if out=`ansible ${node} -o -m command -a "/usr/local/sbin/ep_get_sftp_user.sh ${domain}" -u root` ; then
    sftp_user=`echo ${out} | awk '{print $8}'`
else
    echo ${out}
    exit 1
fi

echo -n ${siteurl}
echo -n "|"
echo -n ${sftp_user}
exit 0
