# Dockerfile — Laravel + PHP 8.3 + Composer + Node 22.x
# Menggunakan base image Laravel Sail dengan PHP 8.3 & Composer
FROM laravelsail/php83-composer:latest

# Non-interactive & install tools dasar (curl, ca-certificates, gnupg) lalu Node 22.x
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get install -y --no-install-recommends curl ca-certificates gnupg build-essential unzip git && \
    # Setup NodeSource untuk Node 22.x (channel 22)
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y --no-install-recommends nodejs && \
    # bersihkan cache apt untuk mengurangi ukuran image
    rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy konfigurasi PHP custom (pastikan file ada di ./docker/php.ini)
COPY ./docker/php.ini /usr/local/etc/php/conf.d/uploads.ini

# Copy seluruh source code Laravel (pastikan .dockerignore mengabaikan vendor/node_modules saat perlu)
COPY . .

# Install Composer dependencies (non-interactive)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Install node dependencies & build assets
# Gunakan npm ci jika package-lock.json tersedia (lebih deterministik)
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi && npm run build

# Optional: jalankan composer post-install scripts (jika diperlukan)
RUN composer run-script post-root-package-install || true

# Expose port aplikasi
EXPOSE 8000

# Jalankan artisan serve
# Jalankan server Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
