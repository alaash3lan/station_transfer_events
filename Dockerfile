# --- Stage 1: Base Environment ---
FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    linux-headers \
    sqlite \
    bash \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo pcntl \
    && apk del $PHPIZE_DEPS

# Install SQLite PDO driver
RUN apk add --no-cache sqlite-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_sqlite \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- Stage 2: Dependencies ---
FROM base AS deps

COPY composer.json composer.lock ./
# Install without scripts/autoloader first to leverage Docker cache
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

# --- Stage 3: Final Production Image ---
FROM base AS app

# Copy only the necessary files from the deps stage
COPY --from=deps /var/www/html /var/www/html

# Setup SQLite and Permissions
RUN touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]