#!/bin/bash
top -bn1  | grep '^[ 0-9]' | field 1 6 NF | grep '[mg] ' | sed 's/\.\([0-9]\)g/\100m/' | awk '{m[$3]+=$2} END {for (a in m) { print a, m[a] }}' | sort -k2rn
