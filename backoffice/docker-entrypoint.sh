#!/bin/sh
set -eu

cd /var/www/html

: "${APP_AUTH_USERNAME:?APP_AUTH_USERNAME precisa ser configurado}"
: "${APP_AUTH_PASSWORD:?APP_AUTH_PASSWORD precisa ser configurado}"

# Instala as dependências travadas somente quando ainda não existem.
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
  echo ">> [backoffice] Garantindo dependências do Composer..."
  composer install --no-interaction --no-dev --prefer-dist
fi

echo ">> [backoffice] Iniciando Apache..."
exec apache2-foreground
