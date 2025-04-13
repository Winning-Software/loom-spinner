FROM php:${PHP_VERSION}-fpm

RUN mkdir -p /var/www/html

RUN apt-get -qq update && apt-get -qq install -y \
    apt-transport-https \
    git \
    curl \
    zip \
    unzip \
    libicu-dev \
    nano \
    bash \
    dnsutils

RUN docker-php-ext-configure intl > /dev/null
RUN docker-php-ext-install mysqli pdo pdo_mysql sockets intl exif bcmath > /dev/null

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html