#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_ROOT="$(
    cd "$(dirname "${BASH_SOURCE[0]}")/.."
    pwd
)"

cd "$PROJECT_ROOT"

export HOST_UID="${HOST_UID:-$(id -u)}"
export HOST_GID="${HOST_GID:-$(id -g)}"

run_npm()
{
    docker compose \
        --profile tools \
        run --rm --no-deps \
        assets \
        npm "$@"
}

case "${1:-build}" in
    ci)
        run_npm ci
        ;;

    build)
        run_npm run build:css
        ;;

    watch)
        run_npm run watch:css
        ;;

    *)
        echo "Использование: $0 {ci|build|watch}" >&2
        exit 1
        ;;
esac
