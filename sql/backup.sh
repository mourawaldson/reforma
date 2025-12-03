#!/usr/bin/env bash
set -e

# Configurações
CONTAINER_NAME="reforma_db"
DB_NAME="controle_reforma"
DB_USER="root"
DB_PASS="root"
BACKUP_DIR="./sql/backups"

# Garante que a pasta existe
mkdir -p "$BACKUP_DIR"

# Nome do arquivo com data e hora
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M")
RAW_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}_raw.sql"
OUT_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql"

echo "Gerando dump bruto do banco '${DB_NAME}' a partir do container '${CONTAINER_NAME}'..."

# 1) Dump "bruto" (estrutura + dados), já sem comentários comuns e locks
docker exec "$CONTAINER_NAME" sh -c "
  mysqldump \
    --no-tablespaces \
    --skip-comments \
    --skip-add-locks \
    --single-transaction \
    -u${DB_USER} -p${DB_PASS} \
    ${DB_NAME}
" > "$RAW_FILE"

echo "Dump bruto salvo em: $RAW_FILE"
echo "Limpando e ajustando CREATE TABLE IF NOT EXISTS..."

# 2) Pós-processamento:
#    - troca CREATE TABLE por CREATE TABLE IF NOT EXISTS
#    - remove comentários especiais de versão (linhas que começam com /*! ...)
sed \
  -e 's/CREATE TABLE `/CREATE TABLE IF NOT EXISTS `/' \
  -e '/^\/\*!/d' \
  "$RAW_FILE" > "$OUT_FILE"

# 3) Remove o arquivo bruto (opcional)
rm "$RAW_FILE"

echo "Backup final gerado em: $OUT_FILE"
echo "Concluído."
