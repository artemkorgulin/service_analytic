FROM php:8.0-alpine

# Install system dependencies
RUN apk update && apk add --no-cache \
    shadow \
    libpng-dev \
    libxml2-dev \
    zip \
    libzip-dev \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql exif pcntl bcmath gd zip

# Add user for laravel application
RUN /usr/sbin/groupadd -g 1000 www
RUN /usr/sbin/useradd -u 1000 -ms /bin/sh -g www www

# Add socket
RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf; \
    addgroup --gid 1001 --system nginx; \
    adduser --uid 1001 --system --ingroup nginx nginx;

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

# Set working directory
WORKDIR /var/www

VOLUME ["/var/www"]

CMD ["php", "artisan", "horizon"]
