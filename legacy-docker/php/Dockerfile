FROM php:7.2-fpm

RUN sed -i 's/deb.debian.org/archive.debian.org/g; s/security.debian.org/archive.debian.org/g; /stretch-updates/d' /etc/apt/sources.list \
    && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99no-check-valid-until \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j"$(nproc)" mysqli pdo_mysql gd zip mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/conf.d/legacy.ini

WORKDIR /var/www/html

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data || true
