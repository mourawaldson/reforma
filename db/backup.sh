#!/usr/bin/env bash
set -euo pipefail

########################################
# Configurações
########################################

CONTAINER_NAME="reforma_db"
DB_NAME="controle_reforma"
DB_USER="root"
DB_PASS="root"
BACKUP_DIR="./db/backups"

# Ordem correta das tabelas (pais → filhos)
TABLES_ORDER=("suppliers" "tags" "expenses" "expense_tags")

########################################
# Preparação
########################################

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
RAW_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}_raw.sql"
OUT_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql"

echo "Gerando dump do banco '${DB_NAME}' a partir do container '${CONTAINER_NAME}'..."
echo "Ordem das tabelas: ${TABLES_ORDER[*]}"
echo "Arquivo bruto: $RAW_FILE"

########################################
# 1) Dump bruto (estrutura + dados)
#    - sem comments comuns
#    - sem locks
#    - single-transaction
#    - TABELAS EM ORDEM EXPLÍCITA
########################################

docker exec "$CONTAINER_NAME" sh -c "
  mysqldump \
    --no-tablespaces \
    --skip-comments \
    --skip-add-locks \
    --single-transaction \
    -u${DB_USER} -p${DB_PASS} \
    ${DB_NAME} ${TABLES_ORDER[*]}
" > "$RAW_FILE"

echo "Dump bruto salvo em: $RAW_FILE"
echo "Pós-processando (CREATE TABLE IF NOT EXISTS + limpeza de comentários + FK checks)..."

########################################
# 2) Pós-processamento
#    - Troca CREATE TABLE por CREATE TABLE IF NOT EXISTS
#    - Remove comentários especiais de versão (linhas /*! ... */)
#    - Envolve tudo com FOREIGN_KEY_CHECKS=0/1
########################################

{
  echo "SET FOREIGN_KEY_CHECKS=0;"

  sed \
    -e 's/CREATE TABLE `/CREATE TABLE IF NOT EXISTS `/' \
    -e '/^\/\*!/d' \
    "$RAW_FILE"

  echo "SET FOREIGN_KEY_CHECKS=1;"
} > "$OUT_FILE"

########################################
# 3) Limpeza
########################################

rm "$RAW_FILE"

echo "Backup final gerado em: $OUT_FILE"
echo "Concluído com sucesso."
