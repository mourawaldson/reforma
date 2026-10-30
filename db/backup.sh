#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
BACKUP_DIR="${SCRIPT_DIR}/backups"

cd "$PROJECT_DIR"
mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date +"%Y-%m-%d_%H-%M-%S")"
BACKUP_FILE="${BACKUP_DIR}/controle_reforma_${TIMESTAMP}.sql"
TEMP_FILE="${BACKUP_FILE}.tmp"

cleanup() {
    rm -f -- "$TEMP_FILE"
}
trap cleanup EXIT

if ! docker compose ps --status running --services | grep -Fxq db; then
    echo "Erro: o serviço 'db' não está em execução. Inicie-o com 'docker compose up -d db'." >&2
    exit 1
fi

DB_NAME="$(docker compose exec -T db printenv MYSQL_DATABASE | tr -d '\r')"
if [[ ! "$DB_NAME" =~ ^[A-Za-z0-9_]+$ ]]; then
    echo "Erro: MYSQL_DATABASE ausente ou contém caracteres não suportados." >&2
    exit 1
fi

BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql"
TEMP_FILE="${BACKUP_FILE}.tmp"

echo "Gerando backup do banco '${DB_NAME}'..."

# A senha é lida dentro do container, sem depender de uma senha fixa ou expô-la
# na linha de comando deste host. O dump original é preservado para restauração.
docker compose exec -T db sh -c \
    'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysqldump --single-transaction --routines --triggers --events --hex-blob --no-tablespaces "$MYSQL_DATABASE"' \
    > "$TEMP_FILE"

if [[ ! -s "$TEMP_FILE" ]]; then
    echo "Erro: o dump ficou vazio; nenhum backup foi criado." >&2
    exit 1
fi

mv -- "$TEMP_FILE" "$BACKUP_FILE"
trap - EXIT

echo "Backup concluído: ${BACKUP_FILE}"
