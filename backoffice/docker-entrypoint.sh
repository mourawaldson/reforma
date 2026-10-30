#!/bin/sh
set -eu

cd /var/www/html

case "${APP_AUTH_USERNAME:-}" in
  ''|*[!A-Za-z0-9_.-]*)
    echo "APP_AUTH_USERNAME ausente ou inválido; use letras, números, ponto, hífen ou sublinhado." >&2
    exit 1
    ;;
esac
: "${APP_AUTH_PASSWORD:?APP_AUTH_PASSWORD precisa ser configurado}"
printf '%s\n' "$APP_AUTH_PASSWORD" | htpasswd -i -B -c /var/www/.htpasswd "$APP_AUTH_USERNAME"
chown root:www-data /var/www/.htpasswd
chmod 640 /var/www/.htpasswd

# Instala dependências se composer.json existir
if [ -f composer.json ]; then
  echo ">> [backoffice] Garantindo dependências do Composer..."
  composer install --no-interaction --no-dev --prefer-dist || composer update --no-interaction --no-dev --prefer-dist
fi

echo ">> [backoffice] Iniciando Apache..."
exec apache2-foreground
