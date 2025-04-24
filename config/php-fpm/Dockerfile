FROM php:${PHP_VERSION}-fpm AS php-build

RUN mkdir -p /var/www/html

RUN apt-get -qq update && apt-get -qq install -y \
    apt-transport-https \
    git \
    jq \
    curl \
    zip \
    unzip \
    libicu-dev \
    nano \
    bash \
    dnsutils \
    > /dev/null 2>&1

RUN docker-php-ext-configure intl > /dev/null 2>&1
RUN docker-php-ext-install sockets intl exif bcmath > /dev/null 2>&1
RUN docker-php-ext-install opcache > /dev/null 2>&1
COPY ./opcache.ini "${PHP_INI_DIR}/conf.d"

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

ENV NVM_DIR /root/.nvm

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.2/install.sh | bash \
    && . "$NVM_DIR/nvm.sh" \
    && nvm install ${NODE_VERSION} \
    && nvm use ${NODE_VERSION}

COPY --from=node:${NODE_VERSION} /usr/local/bin/npx /usr/local/bin/npx