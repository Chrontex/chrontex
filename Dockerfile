FROM php:8.5-apache

RUN docker-php-ext-install mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/html/src/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

ENV PORT=8080
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/ports.conf \
    && sed -i "s/:80/:\${PORT}/g" /etc/apache2/sites-available/000-default.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY src/ /var/www/html/src/

RUN chown -R www-data:www-data /var/www/html/src