# PHP 8.2 FPM
FROM php:8.2-fpm

WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev zip libzip-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy app
COPY . .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Cache config/routes/views
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Link storage
RUN php artisan storage:link

EXPOSE 10000

# Start app (migrations + server)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
