#!/bin/sh
set -e

cd /var/www/html

# Se tiver composer.json e ainda não existir vendor/autoload.php, roda o install
# Instala dependências se composer.json existir
if [ -f composer.json ]; then
  echo ">> [api] Garantindo dependências do Composer..."
  composer install --no-interaction --no-dev --prefer-dist || composer update --no-interaction --no-dev --prefer-dist
fi

echo ">> Iniciando Apache..."
exec apache2-foreground
