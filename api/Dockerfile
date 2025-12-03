FROM php:8.3-apache

# Extensões do PHP
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite e aponta DocumentRoot para /public
RUN a2enmod rewrite && \
    sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html
