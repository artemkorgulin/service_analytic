FROM phpswoole/swoole:4.8-php8.0

RUN apt-get update && apt-get -y --no-install-recommends install \
        git \
        cron \
        mc \
        procps \
        zip \
        unzip \
        libzip-dev \
        nano
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl update-channels && \
    pecl install redis && \
    docker-php-ext-enable redis && \
    docker-php-ext-install pcntl pdo pdo_mysql zip

# Copy existing application directory contents
COPY . /var/www

# Set working directory
WORKDIR /var/www

CMD ["php", "artisan", "horizon"]
