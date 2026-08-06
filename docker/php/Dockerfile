FROM php:8.3-apache

# Composer берём из официального Docker-образа,
# чтобы не устанавливать его вручную.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# git нужен Composer для резервной загрузки пакетов из исходников.
# unzip и расширение zip позволяют устанавливать пакеты из dist-архивов.
# pdo_mysql используется приложением для подключения к MySQL.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Подменяем стандартную конфигурацию виртуального хоста Apache.
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Исходники примонтированы с хоста, поэтому владелец каталога
# отличается от пользователя, выполняющего CLI-команды в контейнере.
RUN git config --system --add safe.directory /var/www/html
