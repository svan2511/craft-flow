# =========================
# Stage 1: Composer
# =========================
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist \
    --no-scripts \
    --no-interaction \
    --ignore-platform-reqs

# =========================
# Stage 2: PHP + Nginx
# =========================
FROM php:8.4-fpm-alpine

# Install system packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    git \
    curl \
    zip \
    unzip \
    netcat-openbsd \
    libzip-dev \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    $PHPIZE_DEPS

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        zip \
        pcntl \
        bcmath \
        gd \
        exif

# Install Redis extension
RUN pecl install redis && \
    docker-php-ext-enable redis

# Remove build dependencies
RUN apk del $PHPIZE_DEPS

# Copy configs
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Application
WORKDIR /var/www/html

COPY . .
COPY --from=composer /app/vendor ./vendor

# Storage permissions
RUN mkdir -p bootstrap/cache && \
    mkdir -p storage/framework/cache && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/views && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# Copy entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80