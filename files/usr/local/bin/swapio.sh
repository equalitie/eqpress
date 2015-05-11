#!/bin/sh
#
# Reports Swap IO to mrtg
# mf@in-tuition.net
VMSTAT="/usr/bin/vmstat"

SI=`$VMSTAT 1 2| gawk '{print $7}'`
SO=`$VMSTAT 1 2| gawk '{print $8}'`

#Now the variables contain a blank, si/so and the value so strip all but value out

SI=`echo $SI | gawk '{print $3}'`
SO=`echo $SO | gawk '{print $3}'`

echo $SI
echo $SO
