# Stage 1: Build Composer dependencies and frontend assets
FROM composer:2.7 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-interaction

# Copy the rest of the application code
COPY . .

# Install Node and build frontend assets
FROM node:20 AS nodebuild
WORKDIR /app
COPY --from=composer /app /app
RUN npm install && npm run build

# Stage 2: Production image with PHP, Nginx, and built assets
FROM php:8.2-fpm-alpine AS app

# Update, upgrade, and install system dependencies
RUN apk update && apk upgrade --no-cache && \
    apk add --no-cache nginx supervisor bash shadow curl libpng libpng-dev libjpeg-turbo-dev freetype-dev icu-dev libzip-dev oniguruma-dev postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath intl

# Configure PHP
COPY --from=nodebuild /app /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Install Composer (for artisan commands in container)
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisord.conf

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Start Supervisor (which runs Nginx, PHP-FPM, and queue worker)
ENTRYPOINT ["/entrypoint.sh"] 