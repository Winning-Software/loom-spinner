RUN pecl install xdebug redis \
    && docker-php-ext-enable redis

COPY ./xdebug.ini.tmp "${PHP_INI_DIR}/conf.d/xdebug.ini"
RUN docker-php-ext-install opcache > /dev/null
COPY ./opcache.ini "${PHP_INI_DIR}/conf.d"