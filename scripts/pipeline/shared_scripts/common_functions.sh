#!/usr/bin/env bash

set -o nounset
set -o pipefail
set -o errexit

if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

if [[ -n "${CI:-}" ]];then
  # shellcheck source=.
  source "$BITBUCKET_CLONE_DIR/scripts/pipeline/.env"
  bash "$BITBUCKET_CLONE_DIR/scripts/pipeline/default_setup_ci.sh"
fi

_info() {
  local color_info="\\x1b[32m"
  local color_reset="\\x1b[0m"
  echo -e "$(printf '%s%s%s\n' "$color_info" "$@" "$color_reset")"
}

_err() {
  local color_error="\\x1b[31m"
  local color_reset="\\x1b[0m"
  echo -e "$(printf '%s%s%s\n' "$color_error" "$@" "$color_reset")" 1>&2
}

_drush() {
  COLUMNS=$(tput cols 2>/dev/null) drush --yes --ansi "$@"
}

_robo() {
  COLUMNS=$(tput cols 2>/dev/null) robo --ansi "$@"
}

_drush_watchdog() {
  _info "### Show watchdog"
  _drush ws --extended --count 500
  _drush watchdog:delete all
}

_backstopjs() {
  (cd $TEST_DIR && \
  docker run --add-host host.docker.internal:$BITBUCKET_DOCKER_HOST_INTERNAL -v "$(pwd)/backstopjs":/src -v "$(pwd)/lfs_data":/lfs_data backstopjs/backstopjs "$@")
}

_behat() {
  _info "### Setting up Behat"
  mv -v $TEST_DIR/behat/behat-no-drupal.dist.yml .
  mv -v $TEST_DIR/behat/behat.dist.yml .
  behat --format=pretty --out=std --format=junit --out=$BITBUCKET_CLONE_DIR/test-reports/ --strict --colors "$@"
}

_setup_settings_php() {
  _info "### Configuring settings.php"
  echo '$settings["file_private_path"] = "sites/default/files/private";' >> docroot/sites/default/settings.php
  echo '$settings["file_public_path"] = "sites/default/files";' >> docroot/sites/default/settings.php
  echo '$settings["file_temp_path"] = "/tmp";' >> docroot/sites/default/settings.php
  echo '$settings["trusted_host_patterns"] = ["^127.0.0.1$","^localhost$","^host.docker.internal$"];' >> docroot/sites/default/settings.php
  echo '$config["locale.settings"]["translation"]["path"] = "sites/default/files/translations";' >> docroot/sites/default/settings.php
  echo '$config["swiftmailer.transport"]["transport"] = "spool";' >> docroot/sites/default/settings.php
  echo '$config["swiftmailer.transport"]["spool_directory"] = "/tmp/swiftmailer-spool";' >> docroot/sites/default/settings.php
}

_setup_local_settings_php() {
  cp -v $BITBUCKET_CLONE_DIR/project/docroot/profiles/contrib/degov/testing/behat/template/settings.local.php docroot/sites/default/settings.local.php
  sed -i 's/{{ mysql_auth.db }}/testing/g' docroot/sites/default/settings.local.php
  sed -i 's/{{ mysql_auth.user }}/testing/g' docroot/sites/default/settings.local.php
  sed -i 's/{{ mysql_auth.password }}/testing/g' docroot/sites/default/settings.local.php
  sed -i 's/{{ mysql_host }}/127.0.0.1/g' docroot/sites/default/settings.local.php
  echo '$settings["hash_salt"] = "7asdiugasd8f623gjwgasgf7a8stfasjdfsdafasdfasdfasdf";' >> docroot/sites/default/settings.local.php
}

_setup_file_system() {
  _info '### Creating file system folders'
  mkdir -p docroot/sites/default/files/private/
  mkdir docroot/sites/default/files/translations/
  chmod 777 -R docroot/sites/default/files/
}

_run_npm_audit() {
  _info '### Npm audit'
  _info '# Install nvm'
  set +o errexit
  export NVM_DIR="$HOME/.nvm"
  [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"  # This loads nvm
  _info '# Install node with nvm'
  set -o errexit
  nvm install --no-progress --latest-npm "$(cat .nvmrc)"
  _info '# Audit non-dev dependencies'
  npm install --production
  npm audit --production
}

_composer() {
  composer --ansi --profile "$@"
}
