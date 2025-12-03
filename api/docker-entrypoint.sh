#!/bin/sh
set -e

cd /var/www/html

# Se tiver composer.json e ainda não existir vendor/autoload.php, roda o install
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
  echo ">> composer.json encontrado e vendor/autoload.php ausente."
  echo ">> Rodando composer install..."
  composer install --no-interaction --no-dev --prefer-dist
fi

echo ">> Iniciando Apache..."
exec apache2-foreground
