# Gunakan base image Laravel Sail (dengan PHP 8.3 dan Composer)
FROM laravelsail/php83-composer:latest

# Instal dependensi tambahan (node, npm, git, unzip)
RUN apt-get update && apt-get install -y \
    nodejs npm git unzip \
    && rm -rf /var/lib/apt/lists/*

# Set folder kerja di dalam container
WORKDIR /var/www/html

# Copy file konfigurasi PHP custom (untuk upload besar)
COPY ./docker/php.ini /usr/local/etc/php/conf.d/uploads.ini

# Copy seluruh source code Laravel ke dalam container
COPY . .

# Install Composer dependencies (tanpa tanya-tanya)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies dan build assets
RUN npm install && npm run build

# Expose port 8000 agar bisa diakses dari luar container
EXPOSE 8000

# Jalankan server Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
