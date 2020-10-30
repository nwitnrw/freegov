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

_info() {
  local color_info="\\x1b[32m"
  local color_reset="\\x1b[0m"
  echo -e "$(printf '%s%s%s\n' "$color_info" "$@" "$color_reset")"
}

_composer() {
  composer --ansi --profile "$@"
}

main() {
  _info "### Setting up project folder"
  _composer create-project --remove-vcs --no-progress "$PROJECT:dev-$PROJECT_BRANCH" project
  cd project
  _info "### Install profile"
  cd "$BITBUCKET_CLONE_DIR"
  git fetch --unshallow
  git config remote.origin.fetch "+refs/heads/*:refs/remotes/origin/*"
  git fetch --quiet origin
  # Was the composer.json changed? Then lets composer download the dependencies.
  if ! git diff --exit-code "origin/$RELEASE_BRANCH" "composer.json" >/dev/null;then
    cd "$BITBUCKET_CLONE_DIR/project"
    # --no-update change only the composer.json
    _composer require --no-progress "$INSTALL_PROJECT:dev-$BITBUCKET_BRANCH#$BITBUCKET_COMMIT" --no-update
    # Now downloads install profile + whitelist of dependencies.
    _composer update $INSTALL_PROJECT $INSTALL_PROJECT_UPDATE_LIST
    # Re-apply patches in case they was changed.
    _composer install
  fi
  cd "$BITBUCKET_CLONE_DIR/project"
  # Move the lfs_data out of the install profile before we delete it. But lets the pipeline store the data in the project artifact.
  mv -v "$TEST_DIR/lfs_data/$CONTRIBNAME-stable-$DB_DUMP_VERSION.sql.gz" .
  # Do not store data (as artifact in the pipeline) which is in the git repo itself. (this makes artifact smaller)
  # We restore the profile in default_setup_ci.sh.
  rm -rf "$PROFILE_DIR"
  find . -type d -name '.git' -print0 -exec rm -rf {} + >/dev/null
}

main "$@"
