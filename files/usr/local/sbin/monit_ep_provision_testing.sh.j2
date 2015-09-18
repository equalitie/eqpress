#!/bin/bash
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin
export HOME=/root

basedir=/var/www/easypress.ca
ansible=${basedir}/ansible
prov=${basedir}/provision-testing
pending=${prov}/pending
processing=${prov}/processing
processed=${prov}/processed


sites=`find ${pending}/* -type d -exec basename {} \; 2> /dev/null`
if [ ! -z ${sites} ]; then
    #
    # wait a second just in case provision process is still mid-write
    sleep 1
    for site in ${sites}; do
        mv ${pending}/${site} ${processing} || ( echo Failed to move ${site}; exit 1 )
    done

    cd ${ansible}
    source ./hacking/env-setup -q

    for site in ${sites}; do
        cd ${processing}/${site}
        ansible-playbook ep-provision.yml -v
        #
        # delete duplicate directories
        if [ -d ${processed}/${site} ]; then
            echo ${site} already existed in ${processed}
            rm -rf ${processed}/${site}
        fi
        mv ${processing}/${site} ${processed}
        cd ${processed}
        tar cfz ${site}.tar.gz ./${site}
        rm -rf ${site}

    done

    exit 1
else
    exit 0
fi
