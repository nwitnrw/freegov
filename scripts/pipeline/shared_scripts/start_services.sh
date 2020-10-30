#!/usr/bin/env bash

set -o nounset
set -o pipefail
set -o errexit

if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

# shellcheck disable=SC2164
__DIR__="$(cd "$(dirname "${0}")"; pwd)"

main() {
  # shellcheck source=.
  source "$__DIR__/../.env"
  bash "$__DIR__/../default_setup_ci.sh"
  # shellcheck source=.
  source "$__DIR__/common_functions.sh"

  cd project
  _info "### Start services"

  _info "### Start mysql"
  docker run --name mysql-$1 -e MYSQL_USER=testing -e MYSQL_PASSWORD=testing -e MYSQL_DATABASE=testing -p 3306:3306 -d mysql/mysql-server:5.7 --max_allowed_packet=1024M

  _info "### Start chromedriver"
  # See the following page for info for the Docker image, which is a meta image from the following one: https://github.com/SeleniumHQ/docker-selenium
  docker run --name testing -e START_XVFB=false --add-host host.docker.internal:$BITBUCKET_DOCKER_HOST_INTERNAL -v "$BITBUCKET_CLONE_DIR:$BITBUCKET_CLONE_DIR" -p 4444:4444 --shm-size=2g -d selenium/standalone-chrome:3.141.59-oxygen
  bash "$BITBUCKET_CLONE_DIR/scripts/pipeline/shared_scripts/wait-for-grid.sh"

  _info "### Start php-server"
  (cd docroot && screen -dmS php-server php -S 0.0.0.0:80 .ht.router.php)
}

main "$@"
