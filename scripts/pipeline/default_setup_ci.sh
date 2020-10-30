#!/usr/bin/env bash

set -o nounset
set -o pipefail
set -o errexit

# shellcheck disable=SC2164
__DIR__="$(cd "$(dirname "${0}")"; pwd)"

# shellcheck source=.
source "$__DIR__/.env"
if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

main() {
  ln -sf "$BITBUCKET_CLONE_DIR/php_error.log" /tmp/php_error.log
  echo "$BITBUCKET_DOCKER_HOST_INTERNAL host.docker.internal" >> /etc/hosts
  if [[ ! -d "$PROFILE_DIR" ]];then
    # Restore the data which was deleted in composer_setup.sh and is not part of the artifact.
    mkdir "$BITBUCKET_CLONE_DIR/project/docroot/profiles/contrib"
    mkdir "$BITBUCKET_CLONE_DIR/testing/lfs_data"
    mv -v "$BITBUCKET_CLONE_DIR/project/$CONTRIBNAME-stable-$DB_DUMP_VERSION.sql.gz" "$BITBUCKET_CLONE_DIR/testing/lfs_data/$CONTRIBNAME-stable-$DB_DUMP_VERSION.sql.gz"
    (cd "$BITBUCKET_CLONE_DIR" && rsync -az --exclude="project/" . "$PROFILE_DIR")
  fi
}

main "$@"
