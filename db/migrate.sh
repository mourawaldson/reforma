#!/usr/bin/env bash
set -Eeuo pipefail

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd -- "${SCRIPT_DIR}/.." && pwd)"
MIGRATIONS_DIR="${SCRIPT_DIR}/migrations"

cd "$PROJECT_DIR"

if ! docker compose ps --status running --services | grep -Fxq db; then
    echo "Erro: o serviço 'db' não está em execução. Inicie-o com 'docker compose up -d db'." >&2
    exit 1
fi

mysql_exec() {
    docker compose exec -T db sh -c \
        'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --default-character-set=utf8mb4 "$MYSQL_DATABASE"'
}

mysql_exec <<'SQL'
CREATE TABLE IF NOT EXISTS schema_migrations (
    version VARCHAR(190) NOT NULL PRIMARY KEY,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL

applied_versions="$(docker compose exec -T db sh -c \
    'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql --batch --skip-column-names "$MYSQL_DATABASE" -e "SELECT version FROM schema_migrations ORDER BY version"')"

for migration_file in "$MIGRATIONS_DIR"/*.sql; do
    [[ -f "$migration_file" ]] || continue
    version="$(basename "$migration_file")"

    if grep -Fxq "$version" <<< "$applied_versions"; then
        echo "Já aplicada: $version"
        continue
    fi

    echo "Aplicando: $version"
    mysql_exec < "$migration_file"
    printf "INSERT INTO schema_migrations (version) VALUES ('%s');\n" "$version" | mysql_exec
    applied_versions+=$'\n'"$version"
done

echo "Migrações concluídas."
