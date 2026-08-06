FROM php:8.3-apache

# Composer берём из его официального Docker-образа,
# чтобы не устанавливать его вручную.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PDO входит в PHP, отдельно добавляем драйвер для MySQL.
# mod_rewrite понадобится для красивых адресов страниц.
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Подменяем стандартную конфигурацию виртуального хоста Apache.
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
