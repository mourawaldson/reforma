#!/usr/bin/env bash
set -euo pipefail

########################################
# Configurações
########################################

CONTAINER_NAME="reforma_db"
DB_NAME="controle_reforma"
DB_USER="root"
DB_PASS="root"

OUTPUT_DIR="../sql"
OUTPUT_FILE="${OUTPUT_DIR}/schema.sql"

# Ordem correta das tabelas (pais → filhos)
TABLES_ORDER=("suppliers" "tags" "expenses" "expense_tags")

########################################
# Preparação
########################################

mkdir -p "$OUTPUT_DIR"

echo "Gerando schema limpo do banco '${DB_NAME}'"
echo "Container: ${CONTAINER_NAME}"
echo "Tabelas: ${TABLES_ORDER[*]}"
echo "Saída: ${OUTPUT_FILE}"
echo "----------------------------------------"

########################################
# Dump SOMENTE estrutura
########################################

docker exec "$CONTAINER_NAME" sh -c "
  mysqldump \
    --no-data \
    --no-tablespaces \
    --skip-comments \
    --skip-add-drop-table \
    --skip-add-locks \
    -u${DB_USER} -p${DB_PASS} \
    ${DB_NAME} ${TABLES_ORDER[*]}
" \
| sed \
    -e '/^\/\*!/d' \
    -e '/^SET /d' \
    -e 's/CREATE TABLE `/CREATE TABLE IF NOT EXISTS `/' \
    -e 's/ AUTO_INCREMENT=[0-9]*//g' \
    -e 's/ COLLATE [^ ]*//g' \
> "$OUTPUT_FILE"

echo "----------------------------------------"
echo "✔ Schema limpo gerado com sucesso:"
echo "  → $OUTPUT_FILE"
