#!/bin/bash
#
# eq_resync_space_check.sh <bytes>:<path> [<bytes>:<path> ...]
#
# Run by the mariadb-local-enable-replication role before a slave resync,
# while nothing has been stopped yet. Each argument says how many bytes the
# resync will write under <path>. Paths on the same filesystem are added up,
# a 10% margin is added, and the total is compared with the free space.
#
# Exit codes: 0 enough space, 1 not enough space or bad arguments.
#
export PATH=/usr/bin:/bin:/usr/sbin:/sbin

declare -A need avail
order=()

for arg in "$@"; do
	bytes=${arg%%:*}
	path=${arg#*:}
	if [[ ! "$bytes" =~ ^[0-9]+$ ]] || [ -z "$path" ]; then
		echo "bad argument '$arg', expected <bytes>:<path>"
		exit 1
	fi
	# The path may not exist yet; use the closest existing parent
	while [ ! -e "$path" ]; do path=$(dirname "$path"); done
	read -r free mount < <(df -B1 --output=avail,target "$path" 2>/dev/null | tail -1)
	if [[ ! "$free" =~ ^[0-9]+$ ]] || [ -z "$mount" ]; then
		echo "cannot read free space for $path"
		exit 1
	fi
	if [ -z "${need[$mount]+x}" ]; then
		order+=("$mount")
		need[$mount]=0
		avail[$mount]=$free
	fi
	need[$mount]=$(( need[$mount] + bytes ))
done

h() { numfmt --to=iec-i --suffix=B "$1"; }

rc=0
for mount in "${order[@]}"; do
	want=$(( need[$mount] + need[$mount] / 10 ))
	if [ "${avail[$mount]}" -ge "$want" ]; then
		echo "OK    $mount: needs $(h "$want"), free $(h "${avail[$mount]}")"
	else
		echo "SHORT $mount: needs $(h "$want"), free $(h "${avail[$mount]}")"
		rc=1
	fi
done
exit $rc
