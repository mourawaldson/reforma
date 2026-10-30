#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
TEST_DB="reforma_migrations_test_$$"

cd "$PROJECT_DIR"
if ! docker compose ps --status running --services | grep -Fxq db; then
    echo "Erro: o serviço 'db' não está em execução. Inicie-o com 'docker compose up -d db'." >&2
    exit 1
fi

mysql_admin() {
    docker compose exec -T db sh -c \
        'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --default-character-set=utf8mb4 --batch --skip-column-names'
}

mysql_in_database() {
    local database="$1"
    docker compose exec -T db sh -c \
        'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --default-character-set=utf8mb4 --batch --skip-column-names "$1"' \
        sh "$database"
}

printf 'CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;\n' "$TEST_DB" | mysql_admin
cleanup() {
    printf 'DROP DATABASE IF EXISTS `%s`;\n' "$TEST_DB" | mysql_admin >/dev/null 2>&1 || true
}
trap cleanup EXIT

mysql_in_database "$TEST_DB" <<'SQL'
CREATE TABLE expenses (
    id INT NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    calendar_year INT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
SQL

echo "Testando aplicação repetida de 002_expense_query_indexes.sql..."
mysql_in_database "$TEST_DB" < "$SCRIPT_DIR/migrations/002_expense_query_indexes.sql"
mysql_in_database "$TEST_DB" < "$SCRIPT_DIR/migrations/002_expense_query_indexes.sql"

index_count="$(printf '%s\n' \
    "SELECT COUNT(DISTINCT index_name) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'expenses' AND index_name IN ('idx_expenses_calendar_year', 'idx_expenses_date');" \
    | mysql_in_database "$TEST_DB")"
if [[ "$index_count" != "2" ]]; then
    echo "Erro: esperados dois índices, encontrados ${index_count}." >&2
    exit 1
fi

echo "Migração idempotente validada."
