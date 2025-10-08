FROM laravelsail/php83-composer:latest

RUN apt-get update && apt-get install -y nodejs npm git unzip && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy seluruh source code Laravel dulu (termasuk artisan)
COPY . .

# Install composer dependencies
RUN composer install --no-interaction --ignore-platform-reqs

# Install node dependencies
RUN npm install
RUN npm run build

CMD php artisan serve --host=0.0.0.0 --port=8000
