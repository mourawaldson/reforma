#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd -- "${SCRIPT_DIR}/.." && pwd)"

if [[ $# -ne 1 ]]; then
    echo "Uso: bash db/restore.sh caminho/para/backup.sql" >&2
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

echo "ATENÇÃO: restaurar vai substituir os dados atuais do banco pelo conteúdo do backup."
echo "Arquivo: $BACKUP_FILE"
read -r -p "Digite RESTAURAR para continuar: " confirmation
if [[ "$confirmation" != "RESTAURAR" ]]; then
    echo "Restauração cancelada."
    exit 1
fi

echo "Criando um backup de segurança do estado atual..."
"${SCRIPT_DIR}/backup.sh"

echo "Restaurando..."
docker compose exec -T db sh -c \
    'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --default-character-set=utf8mb4 "$MYSQL_DATABASE"' \
    < "$BACKUP_FILE"

echo "Restauração concluída."
