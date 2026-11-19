#!/bin/bash
#
# site_snapshot_tool.sh - companion to site_snapshot.sh
#
# List the git snapshots of a site, or export one snapshot as a tar.gz so an
# admin can restore it by hand. Never changes the live site.
#
#   site_snapshot_tool.sh list    <site>
#   site_snapshot_tool.sh export  <site> <tag|latest> [output_dir]
#   site_snapshot_tool.sh cleanup <site|all> [days] [--yes]
#
# cleanup removes snapshots older than <days> (default 365). Without --yes it
# only shows what would be removed. The newest snapshot is always kept.
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
  echo "  $0 cleanup <site|all> [days] [--yes]         (default days: 365, dry run without --yes)"
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

# Remove snapshots older than <days> from one site.
#
# Deleting tags alone frees nothing: every snapshot commit points to the one
# before it. So the history is rebuilt starting at the oldest snapshot kept
# (same files, dates and message), tags are moved to the rebuilt commits, and
# git gc then drops the old data.
cleanup_site() {
  local site=$1 days=$2 apply=$3
  check_site "$site"

  local cutoff=$(( $(date +%s) - days * 86400 ))
  local -a old=() keep=()
  local tag ts
  while read -r tag ts; do
    [ -n "$tag" ] || continue
    if [ "$ts" -lt "$cutoff" ]; then old+=("$tag"); else keep+=("$tag"); fi
  done < <(snap_git for-each-ref --sort=creatordate --format='%(refname:short) %(creatordate:unix)' refs/tags)

  # Always keep the newest snapshot
  if [ ${#keep[@]} -eq 0 ] && [ ${#old[@]} -gt 0 ]; then
    keep=("${old[-1]}")
    unset 'old[-1]'
  fi

  echo "$site: ${#old[@]} snapshot(s) older than $days days, ${#keep[@]} kept, repo size $(du -sh "$SNAP" | cut -f1)"
  if [ ${#old[@]} -eq 0 ]; then
    return 0
  fi
  echo "  remove: ${old[0]} .. ${old[-1]}"
  echo "  keep:   ${keep[0]} .. ${keep[-1]}"
  if [ "$apply" != "yes" ]; then
    echo "  dry run, nothing changed (add --yes to remove)"
    return 0
  fi

  # Same lock as site_snapshot.sh, so a snapshot cannot run at the same time
  [ -f "$SITE_DIR/.lock" ] && die "$site is locked (snapshot running?), try later"
  touch "$SITE_DIR/.lock"
  trap 'rm -f "$SITE_DIR/.lock"' EXIT

  local branch_ref old_tip
  branch_ref=$(snap_git symbolic-ref -q HEAD) || die "$site: snapshot HEAD is not a branch"
  old_tip=$(snap_git rev-parse --verify "$branch_ref") || die "$site: cannot read $branch_ref"

  # Rebuild every commit from the oldest kept snapshot up to the branch tip
  local start c tree parent="" newc
  start=$(snap_git rev-parse --verify "refs/tags/${keep[0]}^{commit}") || die "$site: bad tag ${keep[0]}"
  declare -A map=()
  local started=0
  while read -r c; do
    [ "$c" = "$start" ] && started=1
    [ $started -eq 1 ] || continue
    tree=$(snap_git rev-parse "$c^{tree}")
    newc=$(
      GIT_AUTHOR_NAME=$(snap_git show -s --format=%an "$c") \
      GIT_AUTHOR_EMAIL=$(snap_git show -s --format=%ae "$c") \
      GIT_AUTHOR_DATE=$(snap_git show -s --format=%aI "$c") \
      GIT_COMMITTER_NAME=$(snap_git show -s --format=%cn "$c") \
      GIT_COMMITTER_EMAIL=$(snap_git show -s --format=%ce "$c") \
      GIT_COMMITTER_DATE=$(snap_git show -s --format=%cI "$c") \
      snap_git commit-tree "$tree" ${parent:+-p "$parent"} -F <(snap_git show -s --format=%B "$c")
    ) || die "$site: rebuild failed, nothing changed"
    map[$c]=$newc
    parent=$newc
  done < <(snap_git rev-list --reverse --first-parent "$old_tip")
  [ $started -eq 1 ] || die "$site: ${keep[0]} is not on the snapshot branch, nothing changed"

  # Point kept tags and the branch at the rebuilt commits, then drop old tags
  for tag in "${keep[@]}"; do
    c=$(snap_git rev-parse "refs/tags/$tag^{commit}")
    [ -n "${map[$c]}" ] || die "$site: tag $tag not on snapshot branch, stopping"
    snap_git update-ref "refs/tags/$tag" "${map[$c]}"
  done
  snap_git update-ref "$branch_ref" "$parent" "$old_tip" || die "$site: branch moved during cleanup"
  for tag in "${old[@]}"; do
    snap_git update-ref -d "refs/tags/$tag"
  done

  snap_git reflog expire --expire=now --all
  snap_git gc --prune=now --quiet
  rm -f "$SITE_DIR/.lock"
  trap - EXIT
  echo "  removed ${#old[@]}, repo size now $(du -sh "$SNAP" | cut -f1)"
}

cmd_cleanup() {
  local target=$1 days=365 apply=no
  [ -n "$target" ] || usage
  shift
  local arg
  for arg in "$@"; do
    case "$arg" in
      --yes) apply=yes ;;
      *) [[ "$arg" =~ ^[0-9]+$ ]] && [ "$arg" -ge 1 ] || die "days must be a whole number >= 1"
         days=$arg ;;
    esac
  done

  if [ "$target" = "all" ]; then
    local d
    for d in "$SITES"/*/snapshot; do
      [ -d "$d" ] || continue
      # each site in a subshell, so one failure does not stop the others
      ( cleanup_site "$(basename "$(dirname "$d")")" "$days" "$apply" )
    done
  else
    cleanup_site "$target" "$days" "$apply"
  fi
}

if [[ $EUID -ne 0 ]]; then
  die "must be run as root"
fi

case "$1" in
  list)    shift; cmd_list "$@" ;;
  export)  shift; cmd_export "$@" ;;
  cleanup) shift; cmd_cleanup "$@" ;;
  *)       usage ;;
esac
