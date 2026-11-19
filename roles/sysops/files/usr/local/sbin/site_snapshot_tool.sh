#!/bin/bash
#
# site_snapshot_tool.sh - companion to site_snapshot.sh
#
# List the git snapshots of a site, or export one snapshot as a tar.gz so an
# admin can restore it by hand. Never changes the live site.
#
#   site_snapshot_tool.sh list   <site>
#   site_snapshot_tool.sh export <site> <tag|latest> [output_dir]
#
# <site> is the directory name under /var/www (e.g. example.org).
# Snapshots exist only for sites with a .dosnapshot file.
#
export PATH=/usr/bin:/bin:/usr/local/sbin:/usr/local/bin

SITES=/var/www
DEFAULT_OUT=/root/site_snapshots

usage() {
  echo "Usage:"
  echo "  $0 list   <site>"
  echo "  $0 export <site> <tag|latest> [output_dir]   (default dir: $DEFAULT_OUT)"
  exit 1
}

die() {
  echo "Error: $*" >&2
  exit 1
}

# Validate <site> and set SITE_DIR and SNAP
check_site() {
  local site=$1
  [ -n "$site" ] || usage
  # plain directory name only, no paths
  [[ "$site" =~ ^[A-Za-z0-9][A-Za-z0-9._-]*$ ]] || die "invalid site name '$site'"
  SITE_DIR=$SITES/$site
  SNAP=$SITE_DIR/snapshot
  [ -d "$SITE_DIR" ] || die "no such site: $SITE_DIR"
  [ -d "$SNAP" ] || die "no snapshots for $site (no $SNAP; add $SITE_DIR/.dosnapshot to enable)"
}

snap_git() {
  git --git-dir="$SNAP" "$@"
}

cmd_list() {
  check_site "$1"
  echo "Snapshots for $1 (oldest first):"
  if ! snap_git for-each-ref --sort=creatordate \
        --format='  %(refname:short)    %(creatordate:iso)' refs/tags; then
    die "cannot read $SNAP"
  fi
  echo
  echo "Database dumps in $SITE_DIR:"
  local dumps=("$SITE_DIR"/*.sql*.gz) d
  if [ -e "${dumps[0]}" ]; then
    for d in "${dumps[@]}"; do
      echo "  $(date -r "$d" '+%F %T')  $(basename "$d")"
    done
  else
    echo "  none"
  fi
}

cmd_export() {
  check_site "$1"
  local site=$1 tag=$2 out=${3:-$DEFAULT_OUT}
  [ -n "$tag" ] || usage

  if [ "$tag" = "latest" ]; then
    tag=$(snap_git for-each-ref --sort=-creatordate --count=1 --format='%(refname:short)' refs/tags)
    [ -n "$tag" ] || die "no snapshots found for $site"
  fi
  [[ "$tag" =~ ^[A-Za-z0-9._+-]+$ ]] || die "invalid tag '$tag'"
  snap_git rev-parse --quiet --verify "refs/tags/$tag^{commit}" >/dev/null \
    || die "no snapshot tagged '$tag' for $site (see: $0 list $site)"

  # Archive holds wp-config.php and a database dump: keep it root only
  umask 077
  mkdir -p "$out" || die "cannot create $out"
  chmod 700 "$out"
  local file=$out/$site-$tag.tar.gz
  [ -e "$file" ] && die "$file already exists"

  snap_git archive --format=tar.gz --prefix=wordpress/ -o "$file" "refs/tags/$tag" \
    || { rm -f "$file"; die "export failed"; }
  chmod 600 "$file"

  echo "Exported $site snapshot $tag"
  echo "  $file ($(du -h "$file" | cut -f1))"
  echo
  echo "Contents unpack to wordpress/ and include that snapshot's database dump"
  echo "(wordpress/<db_name>.sql). Suggested restore, after checking the files:"
  echo "  mkdir /root/restore-$site && tar -xzf $file -C /root/restore-$site"
  echo "  # files: copy from /root/restore-$site/wordpress/ into $SITE_DIR/wordpress/"
  echo "  # database: mysql <db_name> < /root/restore-$site/wordpress/<db_name>.sql"
  echo "  # then remove the .sql from the site's wordpress/ folder and fix ownership"
}

if [[ $EUID -ne 0 ]]; then
  die "must be run as root"
fi

case "$1" in
  list)   shift; cmd_list "$@" ;;
  export) shift; cmd_export "$@" ;;
  *)      usage ;;
esac
