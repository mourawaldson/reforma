#!/bin/sh
set -e

cd /var/www/html

# Instala dependências se composer.json existir
if [ -f composer.json ]; then
  echo ">> [backoffice] Garantindo dependências do Composer..."
  composer install --no-interaction --no-dev --prefer-dist || composer update --no-interaction --no-dev --prefer-dist
fi

echo ">> [backoffice] Iniciando Apache..."
exec apache2-foreground
