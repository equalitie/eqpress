#!/bin/sh
#
# Generate Root CA key and self-signed certificate
# - certificate issued in both DER and PEM formats
#

#
# user configurable options
#
BITS=2048
DAYSVALID=7300
RANDDEV=/dev/urandom
CNF=./genRootCA.conf
DER=./root_CA.der
PEM=./root_CA.pem
KEY=./root_CA.key

#
# DO NOT EDIT BEYOND THIS POINT
#

[ ! -f $KEY ] || { echo "Key file already exists: $KEY"; exit; }

# Issue DER-format self-signed Root CA certificate
openssl req -new -x509 -nodes \
    -config $CNF \
    -newkey rsa:$BITS \
    -rand $RANDDEV \
    -days $DAYSVALID \
    -set_serial 0 \
    -keyform DER \
    -outform DER \
    -out $DER

# Produce clean PEM-format Root CA certificate
openssl x509 -text -inform DER -in $DER -outform PEM -out $PEM

# Show root certificate
openssl x509 -text -inform DER -in $DER


