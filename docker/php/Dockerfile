FROM php:8.3-apache

# Composer берём из оф. Docker-образа,
# чтоб не ставить вручную.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# git тут нужен Composer'у для резервной загрузки пакетов из исходников.
# unzip и zip ext тоже нужны - чтоб ставить пакеты из dist.
# pdo_mysql - это для app - для подключения к MySQL.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
    && a2enmod rewrite \
    && printf 'ServerName localhost\n' \
        > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*

# Подменяем стандартную конфигурацию виртуального хоста Apache.
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Исходники подмонтированы с хоста, так что владелец каталога отличается от пользователя, делающего CLI-команды в контейнере.
RUN git config --system --add safe.directory /var/www/html
