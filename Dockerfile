# ─────────────────────────────────────────────────────────────
# Stage 1 – Build frontend assets (Node)
# ─────────────────────────────────────────────────────────────
FROM node:20-slim AS node_builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ ./resources/
COPY public/ ./public/

RUN npm run build

# ─────────────────────────────────────────────────────────────
# Stage 2 – PHP-FPM application image (Debian-based)
# ─────────────────────────────────────────────────────────────
FROM php:8.4-fpm AS app

# Install system dependencies via apt (pre-built binaries – fast)
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    postgresql-client \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    pcntl \
    bcmath \
    xml \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies without running scripts (artisan doesn't exist yet)
RUN composer install --no-interaction --no-scripts --no-dev

# Copy application source (artisan is now present)
COPY . .

# Embed the built frontend assets
COPY --from=node_builder /app/public/build ./public/build

# Re-generate optimised autoload and run post-install scripts (package:discover etc.)
RUN composer dump-autoload --optimize --no-dev

# Storage and bootstrap/cache permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# PHP-FPM tweaks
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# ─────────────────────────────────────────────────────────────
# Stage 3 – Nginx image (static assets embedded)
# ─────────────────────────────────────────────────────────────
FROM nginx:1.27-alpine AS nginx

# Copy the built static assets and the public directory so
# Nginx can serve CSS/JS files without going through PHP-FPM.
COPY --from=node_builder /app/public/build /var/www/html/public/build
COPY public/ /var/www/html/public/

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
