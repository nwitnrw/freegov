#!/usr/bin/env bash
set -o nounset
set -o pipefail
set -o errexit

# shellcheck disable=SC2164
__DIR__="$(cd "$(dirname "${0}")"; pwd)"

if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

_composer() {
  composer --ansi --profile "$@"
}

_info() {
  local color_info="\\x1b[32m"
  local color_reset="\\x1b[0m"
  echo -e "$(printf '%s%s%s\n' "$color_info" "$@" "$color_reset")"
}

main() {
  # shellcheck source=.
  source "$BITBUCKET_CLONE_DIR/scripts/pipeline/.env"
  _info "### Setting up project folder"
  _composer create-project --remove-vcs --no-progress "$PROJECT" project
  # shellcheck source=.
  source "$__DIR__/common_functions.sh"
  bash "$__DIR__/start_services.sh" stable
  cd project
  _setup_settings_php
  _setup_file_system
  # Do not use the config from the stable release.
  cp -v "$BITBUCKET_CLONE_DIR/testing/behat/behat-no-drupal.dist.yml" .
  cp -v "$BITBUCKET_CLONE_DIR/testing/behat/features_install/installation.feature" "docroot/profiles/contrib/$CONTRIBNAME/testing/behat/features_install/installation.feature"
  behat --format=pretty --out=std --format=junit --out="$BITBUCKET_CLONE_DIR/test-reports/" --strict --colors -c behat-no-drupal.dist.yml
  _drush cr
  _drush_watchdog
  _drush sql:dump --gzip --result-file="$BITBUCKET_CLONE_DIR/$CONTRIBNAME-stable.sql"
}

main "$@"
