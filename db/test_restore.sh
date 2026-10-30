#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd -- "${SCRIPT_DIR}/.." && pwd)"

if [[ $# -ne 1 ]]; then
    echo "Uso: bash db/test_restore.sh caminho/para/backup.sql" >&2
    exit 2
fi

BACKUP_FILE="$1"
if [[ "$BACKUP_FILE" != /* ]]; then
    BACKUP_FILE="${PROJECT_DIR}/${BACKUP_FILE}"
fi
if [[ ! -f "$BACKUP_FILE" || ! -s "$BACKUP_FILE" ]]; then
    echo "Erro: arquivo de backup inexistente ou vazio: $BACKUP_FILE" >&2
    exit 1
fi

cd "$PROJECT_DIR"
if ! docker compose ps --status running --services | grep -Fxq db; then
    echo "Erro: o serviço 'db' não está em execução. Inicie-o com 'docker compose up -d db'." >&2
    exit 1
fi

SOURCE_DB="$(docker compose exec -T db printenv MYSQL_DATABASE | tr -d '\r')"
if [[ ! "$SOURCE_DB" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "Erro: MYSQL_DATABASE ausente ou contém caracteres não suportados." >&2
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

RESTORE_DB="reforma_restore_test_$$"
printf 'CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;\n' "$RESTORE_DB" | mysql_admin

cleanup() {
    printf 'DROP DATABASE IF EXISTS `%s`;\n' "$RESTORE_DB" | mysql_admin >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "Testando restauração em banco temporário '${RESTORE_DB}'..."
if grep -Eq '^(CREATE DATABASE|USE) ' "$BACKUP_FILE"; then
    sed "s/\`${SOURCE_DB}\`/\`${RESTORE_DB}\`/g" "$BACKUP_FILE" | mysql_admin
else
    mysql_in_database "$RESTORE_DB" < "$BACKUP_FILE"
fi

for table in suppliers tags expenses expense_tags; do
    source_count="$(printf 'SELECT COUNT(*) FROM `%s`;\n' "$table" | mysql_in_database "$SOURCE_DB")"
    restored_count="$(printf 'SELECT COUNT(*) FROM `%s`;\n' "$table" | mysql_in_database "$RESTORE_DB")"
    if [[ "$source_count" != "$restored_count" ]]; then
        echo "Erro: quantidade de registros divergente na tabela '${table}'." >&2
        exit 1
    fi
done

echo "Restauração validada: estrutura e quantidades de registros conferem."
