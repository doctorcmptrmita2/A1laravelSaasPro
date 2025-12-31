FROM php:8.2-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev

# PHP extensions
RUN docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port
EXPOSE 8000

# Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Start command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
