#!/usr/bin/env bash

set -o nounset
set -o pipefail
set -o errexit

if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

# shellcheck disable=SC2164
__DIR__="$(cd "$(dirname "${0}")"; pwd)"

# shellcheck source=.
source "$__DIR__/.env"
export CI_ROOT_DIR
# shellcheck source=.
source "$__DIR__/shared_scripts/common_functions.sh"

bash "$__DIR__/shared_scripts/start_services.sh" $1

_update_translations() {
  _info "### Update translations"
  _drush locale:check \
  && _drush locale:update
  _drush php-eval 'Drupal\degov\TranslationImporter::importForProfile()'
  _info "### Clear cache"
  _drush cr
}

cd project

_setup_settings_php
_setup_file_system

_info "### Setup database by new installation or database dump"

_disable_geocoder_presave() {
  # Geocoder ignores lat/long in Mediagenerator. (See function geocoder_field_entity_presave())
  _info "### Disable geocoder presave."
  _drush config:set geocoder.settings geocoder_presave_disabled ${1:-1}
}

if [[ "$2" == "install" ]]; then
    _info "### Installing a new"
    _behat -c behat-no-drupal.dist.yml
    _disable_geocoder_presave
    _info "### Install the degov_demo_content"
    _drush en degov_demo_content
    _update_translations
    _drush_watchdog
elif [[ "$2" == "db_dump" ]]; then
    _setup_local_settings_php
    _info "### Drop any existing db"
    _drush sql:drop
    _info "### Importing db dump"
    _drush sql-query --file="$TEST_DIR/lfs_data/$CONTRIBNAME-stable-$DB_DUMP_VERSION.sql.gz"
    _info "### Clear cache"
    _drush cr
    _info "### Checking if the degov_demo_content module is not installed."
    DRUSH_INSTALLED_DEGOV_DEMO_CONTENT="$(drush pm:list | grep degov_demo_content)"
    if [[ $DRUSH_INSTALLED_DEGOV_DEMO_CONTENT == *"Enabled"* ]]; then
      echo "### The degov_demo_content module is installed inside the database dump, but it should not."
      exit 1
    fi
    _info "### Delete old watchdog entries from db dump"
    _drush watchdog:delete all
    _info "### Run database updates"
    _drush updb
    _info "### Clear cache"
    _drush cr
    _disable_geocoder_presave
    _info "### Install the degov_demo_content"
    _drush en degov_demo_content
    _update_translations
    _drush_watchdog
    # For debugging via db dump
    _drush sql:dump --gzip > "$BITBUCKET_CLONE_DIR/$CONTRIBNAME.sql.gz"
elif [[ "$2" == "cli" ]];then
    _drush si degov --db-url=mysql://testing:testing@127.0.0.1:3306/testing \
    --site-name="CLI INSTALL" \
    --account-name="admin" \
    --account-pass="password" \
    --locale="de" \
    --account-mail="admin@example.com" \
    --site-mail="site@example.com"
    _info "### Checking if the degov_demo_content module is not installed."
    DRUSH_INSTALLED_DEGOV_DEMO_CONTENT="$(drush pm:list | grep degov_demo_content)"
    if [[ $DRUSH_INSTALLED_DEGOV_DEMO_CONTENT == *"Enabled"* ]]; then
      echo "### The degov_demo_content module is installed while the cli install, but it should not."
      exit 1
    fi
    _info "### Install the degov_demo_content"
    _drush en degov_demo_content
    _update_translations
    _drush_watchdog
fi

if [[ "$1" == "smoke_tests" ]]; then
    _info "### Running Behat smoke tests"
    # The installation sets admin/password as login data, therefore we reset the data here to match with the behat config.
    _drush upwd admin admin
    set +e
    _behat -c behat.dist.yml --suite=smoke-tests
    EXIT_CODE=$?
    _drush_watchdog
    exit $EXIT_CODE

elif [[ "$1" == "html_validator" ]]; then
    _info "### Start HTML VALIDATION"
    bash $BITBUCKET_CLONE_DIR/scripts/pipeline/shared_scripts/html_validation.sh

elif [[ "$1" == "backstopjs" ]]; then
    bash $BITBUCKET_CLONE_DIR/scripts/pipeline/shared_scripts/backstopjs.sh

elif [[ "$1" != "backstopjs" ]]; then
    _info "### Running Behat features by tags: $1"
    set +e
    _disable_geocoder_presave 0
    _behat -c behat.dist.yml --suite=default --tags="$1"
    EXIT_CODE=$?
    _drush_watchdog
    exit $EXIT_CODE
fi
