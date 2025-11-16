FROM php:8.3-fpm

# Install system deps + node
RUN apt-get update && apt-get install -y \
    curl git unzip zip libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
  && apt-get install -y nodejs

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy PHP ini if exists (mounted also from host)
COPY ./docker/php.ini /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
