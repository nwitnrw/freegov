#!/usr/bin/env bash

set -o nounset
set -o pipefail
set -o errexit

if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

# shellcheck disable=SC2164
__DIR__="$(cd "$(dirname "${0}")"; pwd)"

__fail() {
  if [[ $? -ne 0 ]];then
    EXITCODE=1
  fi
}

main() {
  # shellcheck source=.
  source "$__DIR__/../.env"
  bash "$__DIR__/../default_setup_ci.sh"
  # shellcheck source=.
  source "$__DIR__/common_functions.sh"
  cd project
  mkdir "$BITBUCKET_CLONE_DIR/test-reports"

  EXITCODE=0
  set +o errexit
  _info "### Check php compatibility"
  phpcs -p -s --standard=phpcompatibility.xml
  _info "### Run static analyse"
  phpstan analyse --ansi --no-progress --error-format=junit > $BITBUCKET_CLONE_DIR/test-reports/phpstan-junit.xml
  __fail
  _info "### Checking coding standards"
  phpcs --report=junit --report-file=$BITBUCKET_CLONE_DIR/test-reports/phpcs-junit.xml
  __fail
  _info "### Running PHPUnit and KernelBase tests"
  (cd "docroot/profiles/contrib/$CONTRIBNAME" && phpunit --colors=auto --log-junit $BITBUCKET_CLONE_DIR/test-reports/phpunit-junit.xml --testdox)
  __fail
  _info "### Run npm audit"
  local npm_audit_was_used=0;
  if [[ -d "docroot/profiles/contrib/$CONTRIBNAME/themes" ]];then
    (cd "docroot/profiles/contrib/$CONTRIBNAME/themes/degov_theme" && _run_npm_audit)
    npm_audit_was_used=1
  fi
  if [[ -d "docroot/themes/nrw/nrw_base_theme" ]];then
    (cd "docroot/themes/nrw/nrw_base_theme" && _run_npm_audit)
    npm_audit_was_used=1
  fi
  if [[ "$npm_audit_was_used" -eq 0 ]];then
    _err "npm audit was not used. Did you move the Theme(s) ?"
    exit 1
  fi
  exit $EXITCODE
}

main "$@"
