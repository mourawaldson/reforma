FROM php:8.3-apache

# Habilita mod_rewrite e permite .htaccess na public/
RUN a2enmod rewrite && \
    sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copia o projeto inteiro (incluindo /public, /src, /config, etc.)
COPY . /var/www/html
