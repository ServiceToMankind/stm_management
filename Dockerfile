# Pinned to the PHP2Shell fix (CVE-2026-109618 / CVE-2026-109619); bump for future 8.2.x security releases
FROM php:8.2.35-apache

# Don't advertise the PHP version in the X-Powered-By header
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/zz-hardening.ini

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html
