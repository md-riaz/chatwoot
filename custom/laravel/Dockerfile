# Multi-stage Dockerfile for ClearLine Laravel Application

# Base PHP image with extensions
FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    redis \
    supervisor \
    nginx \
    bash \
    dcron \
    shadow \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        opcache \
        zip \
        intl \
        soap

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create application user
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Development stage
FROM base AS development

# Install development dependencies
RUN composer install --no-scripts --no-autoloader

# Copy application code
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy configuration files
COPY deploy/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY deploy/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

USER www

EXPOSE 9000

CMD ["php-fpm"]

# Production stage
FROM base AS production

# Install production dependencies only
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Install Node.js for asset compilation (if needed)
RUN apk add --no-cache nodejs npm

# Install and build frontend assets (if package.json exists)
RUN if [ -f package.json ]; then \
        npm ci --only=production && \
        npm run build && \
        rm -rf node_modules; \
    fi

# Set permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy configuration files
COPY deploy/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY deploy/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY deploy/nginx/default.conf /etc/nginx/http.d/default.conf
COPY deploy/supervisor/ /etc/supervisor.d/

# Copy entrypoint script
COPY deploy/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Create necessary directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/run/nginx \
    && mkdir -p /var/log/nginx \
    && touch /var/log/nginx/access.log \
    && touch /var/log/nginx/error.log

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["supervisord", "-c", "/etc/supervisord.conf"]