FROM php:8.2-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

COPY docker/apache-vhost.conf /etc/apache2/conf-available/rddigital.conf
RUN a2enconf rddigital

WORKDIR /var/www/html
COPY composer.json /var/www/html/composer.json
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/logs /var/www/html/storage/rate-limits \
    && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80
