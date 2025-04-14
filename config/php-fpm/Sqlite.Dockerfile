RUN mkdir -p /var/www/html/sqlite \
    && chown www-data:www-data /var/www/html/sqlite

RUN apt-get install sqlite3