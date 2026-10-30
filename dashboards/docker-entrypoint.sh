#!/bin/sh
set -eu

cd /var/www/html

: "${APP_AUTH_USERNAME:?APP_AUTH_USERNAME precisa ser configurado}"
: "${APP_AUTH_PASSWORD:?APP_AUTH_PASSWORD precisa ser configurado}"

# Se tiver composer.json e ainda não existir vendor/autoload.php, roda o install
# Instala dependências se composer.json existir
if [ -f composer.json ]; then
  echo ">> [dashboards] Garantindo dependências do Composer..."
  composer install --no-interaction --no-dev --prefer-dist || composer update --no-interaction --no-dev --prefer-dist
fi

echo ">> [dashboards] Iniciando Apache..."
exec apache2-foreground
