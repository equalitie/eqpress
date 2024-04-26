#!/bin/bash
#
# ep_deactivate_site.sh
#
# Deactivate a site so the default Be Happy page shows up.
#
export PATH=/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin

deactivate() {
    if [ -e /etc/nginx/sites-enabled/${1} ]; then
        rm /etc/nginx/sites-enabled/${1}
    else
        if [ -e /etc/nginx/sites-available/${1} ]; then
            echo "${1} is already deactivated."
        else
            echo "${1} does not exist."
        fi
        exit 1;
    fi
    nginx -s reload 2> /dev/null || { echo "nginx reload failed"; exit 1; }
}

if [ -z ${1} ]; then
    echo "Error: No domain name provided."
    echo "${0} example.co"
    exit 1
else
    deactivate ${1}
fi
