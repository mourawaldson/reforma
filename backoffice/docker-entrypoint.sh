#!/bin/sh
set -eu

cd /var/www/html

: "${APP_AUTH_USERNAME:?APP_AUTH_USERNAME precisa ser configurado}"
: "${APP_AUTH_PASSWORD:?APP_AUTH_PASSWORD precisa ser configurado}"

# Instala dependências se composer.json existir
if [ -f composer.json ]; then
  echo ">> [backoffice] Garantindo dependências do Composer..."
  composer install --no-interaction --no-dev --prefer-dist || composer update --no-interaction --no-dev --prefer-dist
fi

echo ">> [backoffice] Iniciando Apache..."
exec apache2-foreground
