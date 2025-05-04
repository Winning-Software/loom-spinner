RUN pecl install xdebug > /dev/null 2>&1

COPY ./xdebug.ini "${PHP_INI_DIR}/conf.d/xdebug.ini"