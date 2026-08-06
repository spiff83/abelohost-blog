#!/usr/bin/env bash

set -Eeuo pipefail

BASE_URL="${1:-http://localhost:8088}"
BASE_URL="${BASE_URL%/}"

TEMP_DIR="$(mktemp -d)"
PASSED_CHECKS=0

trap 'rm -rf "$TEMP_DIR"' EXIT

fail()
{
    echo "Ошибка: $1" >&2
    exit 1
}

check_status()
{
    local path="$1"
    local expected_status="$2"
    local actual_status

    actual_status="$(
        curl -sS \
            -o "$TEMP_DIR/response.html" \
            -w '%{http_code}' \
            "${BASE_URL}${path}"
    )"

    if [[ "$actual_status" != "$expected_status" ]]; then
        fail "${path}: ожидался HTTP ${expected_status}, получен ${actual_status}"
    fi

    printf 'OK  %-52s HTTP %s\n' "$path" "$actual_status"
    ((PASSED_CHECKS += 1))
}

check_occurrences()
{
    local path="$1"
    local pattern="$2"
    local expected_count="$3"
    local description="$4"
    local actual_count

    curl -sS \
        "${BASE_URL}${path}" \
        -o "$TEMP_DIR/response.html"

    actual_count="$(
        grep -c "$pattern" "$TEMP_DIR/response.html" || true
    )"

    if [[ "$actual_count" != "$expected_count" ]]; then
        fail "${description}: ожидалось ${expected_count}, получено ${actual_count}"
    fi

    printf 'OK  %-52s %s\n' "$path" "$description"
    ((PASSED_CHECKS += 1))
}

echo "Проверяем приложение: ${BASE_URL}"
echo

# Основные страницы.
check_status "/" 200
check_status "/category/php" 200
check_status "/category/php?sort=date_asc" 200
check_status "/category/php?sort=views_desc" 200
check_status "/category/php?page=2" 200
check_status "/post/simple-php-architecture" 200

# Несуществующие адреса обязаны возвращать настоящий HTTP 404.
check_status "/category/unknown-category" 404
check_status "/post/unknown-article" 404
check_status "/unknown-page" 404

# Проверки рассчитаны на стандартные данные из bin/seed.php.
check_occurrences \
    "/" \
    'class="category-section"' \
    5 \
    "на главной показано 5 категорий"

check_occurrences \
    "/" \
    'class="post-card"' \
    15 \
    "на главной показано 15 карточек"

check_occurrences \
    "/category/php" \
    'class="post-card"' \
    6 \
    "на первой странице категории показано 6 статей"

check_occurrences \
    "/category/php?page=2" \
    'class="post-card"' \
    2 \
    "на второй странице категории показано 2 статьи"

check_occurrences \
    "/post/many-to-many-mysql" \
    'class="post-card"' \
    3 \
    "на странице статьи показано 3 похожих материала"

echo
echo "Все проверки пройдены: ${PASSED_CHECKS}."
