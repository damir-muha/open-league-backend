FROM php:8.2-cli

# Устанавливаем системные пакеты
WORKDIR /var/www
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    libicu-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) gd pdo_pgsql pgsql mbstring exif pcntl bcmath zip intl sockets

# Устанавливаем Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Права доступа
RUN chown -R www-data:www-data /var/www

EXPOSE 8000