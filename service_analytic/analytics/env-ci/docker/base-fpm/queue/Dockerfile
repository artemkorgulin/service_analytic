FROM php:8.0-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpcre3-dev \
    libssl-dev \
    libpq-dev \
    zip \
    libzip-dev \
    unzip \
    mc

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pcntl bcmath pdo_pgsql pgsql

# Add user for laravel application
RUN /usr/sbin/groupadd -g 1000 www
RUN /usr/sbin/useradd -u 1000 -ms /bin/sh -g www www

# Change current user to www
USER www

# Set working directory
WORKDIR /var/www
VOLUME ["/var/www"]
CMD ["php", "artisan", "horizon"]
