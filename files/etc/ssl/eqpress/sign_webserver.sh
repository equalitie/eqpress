#!/bin/sh
#
# Sign a cert. 
#
CNF=./sign_webserver.conf
CSR=./$1.csr
PEM=./$1.pem
CAPEM=./root_CA.pem
CAKEY=./root_CA.key
DAYSVALID=3650

#
# DO NOT EDIT BEYOND THIS POINT
#

[ ! -f $PEM ] || { echo "Certificate file already exists: $PEM"; exit; }


openssl ca  \
    -batch \
    -config $CNF \
    -days $DAYSVALID \
    -cert $CAPEM \
    -keyfile $CAKEY \
    -in $CSR \
    -out $PEM

# show certificate
openssl x509 -text -inform PEM -in $PEM

