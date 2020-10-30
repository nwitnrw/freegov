#!/usr/bin/env bash

set -o nounset
set -o pipefail

if [[ -n "${DEBUG:-}" ]];then
  set -o xtrace
fi

# shellcheck disable=SC2164
__DIR__="$(cd "$(dirname "${0}")"; pwd)"
__STARTDIR__=${__STARTDIR__:-$__DIR__/..}
__SHARED_DIR__="$__STARTDIR__/html_validation_shared"
__TMP__="$__STARTDIR__/tmp"

_fetch_html_content() {
  local URLS=""
  _info "# Fetch HTML"
  URLS=$(jq --raw-output '[ .scenarios[] | select(has("skipValidation") | not) | .url ] | unique[]' ../../testing/backstopjs/backstop.json | tr '\n' ' ')
  # URLS="http://host.docker.internal/degov-demo-content/blog-post "
  rm  -rf "${__TMP__:?}"/* \
  && wget --hsts-file=/tmp/wget-hsts --no-verbose --no-cache --no-cookies --trust-server-names --adjust-extension  --directory-prefix "$__TMP__" $URLS
  local EXIT_CODE=$?
  if [[ "$EXIT_CODE" -gt 0 ]] ;then
    _err "Could not fetch the HTML content. Is the host host.docker.internal running?"
  else
    _info "# Fetched HTML"
  fi
  return $EXIT_CODE;
}

_run_validation() {
  if [[ -n "${CI:-}" ]];then
    _info "# Run validator"
    BUILD_DIR=$BITBUCKET_CLONE_DIR
    docker run \
      -v "$__TMP__":/files \
      -v "$__SHARED_DIR__":/shared \
      --add-host host.docker.internal:$BITBUCKET_DOCKER_HOST_INTERNAL \
      --name="validator" \
      validator/validator:20.6.30 /vnu-runtime-image/bin/vnu \
        --filterfile  /shared/message-filters.txt \
        --errors-only \
      /files
  else
    local CIDFILE="$__TMP__/../.cidfile"
    if [[ -f $CIDFILE ]];then
      rm $CIDFILE
    fi
    _info "# Run validator locally"
    # BUILD_DIR="/Users/tho/htdocs/GzEvD/degov_nrw-project/docroot"
    docker run \
      -t \
      -v "$__TMP__":/files \
      -v "$__SHARED_DIR__":/shared \
      --cidfile="$CIDFILE" \
      validator/validator:20.6.30 /vnu-runtime-image/bin/vnu \
        --filterfile  /shared/message-filters.txt \
        --errors-only \
      /files
  fi
  local EXIT_CODE=$?
  if [[ "$EXIT_CODE" -eq 125 ]] ;then
    _err "Could not validate the HTML content. Docker is not running."
    return $EXIT_CODE;
  fi

  # Save assets
  if [[ "$EXIT_CODE" -gt 0 ]] ;then
    _err "Found some validation errors."
    if [ -n "${BUILD_DIR:-}" ]; then
      _info "# Save HTML validation HTML assets"
      if [[ -d "$BUILD_DIR/html_validation_results" ]] ;then
          rm -rf $BUILD_DIR/html_validation_results/*
      fi
      mkdir -p "$BUILD_DIR/html_validation_results/pages"
      cp $__TMP__/*.html $BUILD_DIR/html_validation_results/pages
      cp -R $__SHARED_DIR__ $BUILD_DIR/html_validation_results
      if [[ -n "${CI:-}" ]];then
          docker logs "validator" >& $BUILD_DIR/html_validation_results/errors.txt
        else
          docker logs "$(cat $CIDFILE)" >& $BUILD_DIR/html_validation_results/errors.txt
      fi
      tar -c -p -f html_validation_results.tar.gz -C $BUILD_DIR/html_validation_results/ .
      mv html_validation_results.tar.gz $BUILD_DIR
    fi
  else
    _info "No validation errors found"
  fi
  return $EXIT_CODE;
}

main() {
  if [[ -n "${CI:-}" ]];then
      # shellcheck source=.
    source "$__DIR__/../.env"
  fi
    # shellcheck source=.
  source "$__DIR__/common_functions.sh"
  # Disable auto exit from common_functions.sh script. Use the error handler of this script to show the error messages.
  set +o errexit
  cd "$__STARTDIR__" \
  && _info "### Validating HTML5" \
  && _fetch_html_content \
  && _run_validation
  local EXIT_CODE=$?
  exit $EXIT_CODE;
}

main "$@"
