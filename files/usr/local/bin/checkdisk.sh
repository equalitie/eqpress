#!/bin/sh
df="/bin/df"
cut="/usr/bin/cut"
grep="/bin/grep"
part=$1
line=`$df -P -m -x nfs| grep "$part"`
libre=`echo $line|$cut -d" " -f 4`
utilise=`echo $line|$cut -d" " -f 3`
echo $libre
echo $utilise
