FROM phpswoole/swoole:4.8-php8.0

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpcre3-dev \
    libssl-dev \
    zip \
    libzip-dev \
    unzip \
    procps \
    mc \
    nano
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl update-channels && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-install pcntl pdo pdo_mysql zip bcmath

# Add user for laravel application
RUN /usr/sbin/groupadd -g 1000 www
RUN /usr/sbin/useradd -u 1000 -ms /bin/sh -g www www

# Change current user to www
USER www

# Set working directory
WORKDIR /var/www
VOLUME ["/var/www"]

CMD ["php", "artisan", "horizon"]
