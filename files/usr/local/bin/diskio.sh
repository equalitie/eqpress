#!/bin/sh
#
# Reports Disk IO to mrtg
# mf@in-tuition.net
VMSTAT="/usr/bin/vmstat"

BI=`$VMSTAT 1 2| gawk '{print $9}'`
BO=`$VMSTAT 1 2| gawk '{print $10}'`

#Now the variables contain a blank, bo/bi and the value so strip all but value out

BI=`echo $BI | gawk '{print $3}'`
BO=`echo $BO | gawk '{print $3}'`

echo $BI
echo $BO
