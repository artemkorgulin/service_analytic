FROM php:8.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpcre3-dev \
    libssl-dev \
    zip \
    libzip-dev \
    libpq-dev \
    unzip \
&& pecl install xdebug-3.0.4 \
&& docker-php-ext-enable xdebug

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring pcntl bcmath pdo_pgsql pgsql zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
RUN mkdir /var/log/xdebug
RUN chown -R www:www /var/log/xdebug

# Add socket
COPY www.conf /usr/local/etc/php-fpm.d/www.conf
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

VOLUME ["/var/www", "/var/run/php-fpm", "/var/log/php"]

## start php server
CMD ["php-fpm",  "--allow-to-run-as-root", "--nodaemonize"]
