FROM php:8.4-fpm-alpine

# Build arguments
ARG UID=1000
ARG GID=1000

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    nodejs \
    npm \
    supervisor \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Create application user
RUN addgroup -g ${GID} www \
    && adduser -u ${UID} -G www -s /bin/bash -D www

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (layer cache optimization)
COPY --chown=www:www composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy package files first (layer cache optimization for npm)
COPY --chown=www:www package.json package-lock.json* ./

# Install Node dependencies
RUN npm ci --no-audit --no-fund

# Copy application files
COPY --chown=www:www . .

# Run composer autoloader and scripts
RUN composer dump-autoload --optimize

# Build frontend assets (Vite)
RUN npm run build

# Snapshot public dir (including build/) so entrypoint can sync it to shared volume
RUN cp -a public /var/www/html/public-build

# Bake PHP config into image (no bind-mount needed in prod)
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Copy entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ensure storage and cache dirs are writable
RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
    && chown -R www:www storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
