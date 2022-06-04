FROM php:8.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpcre3-dev \
    libpq-dev \
    zip \
    libzip-dev \
    unzip \
    mc \
    cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd  pdo pdo_pgsql pgsql zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

COPY local.ini /usr/local/etc/php/php.ini
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf; \
    mkdir -p /socket; \
    chown www:www /socket; \
    mkdir -p /var/parser; \
    chown www:www /var/parser

COPY crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab && touch /var/log/cron.log

# Change current user to www
USER www

# Set working directory
WORKDIR /var/www

VOLUME ["/var/www", "/var/parser", "/socket", "/var/log/php"]

CMD ["php-fpm", "--nodaemonize"]

