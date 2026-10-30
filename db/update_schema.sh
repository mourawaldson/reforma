#!/usr/bin/env bash
set -Eeuo pipefail

# Backward-compatible entry point: schema changes are now applied from the
# versioned migration files instead of dumping and rewriting the live schema.
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
exec "${SCRIPT_DIR}/migrate.sh" "$@"
