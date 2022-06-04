FROM phpswoole/swoole:4.8-php8.0

RUN apt-get update && apt-get -y --no-install-recommends install \
        git \
        cron \
        mc \
        procps \
        zip \
        unzip \
        libzip-dev \
        libboost-all-dev \
        nano \
        nodejs
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add debug with yasd
RUN set -ex \
    && apt-get -y --no-install-recommends install g++ autotools-dev libicu-dev libboost-all-dev \
    && docker-php-source extract \
    && mkdir /usr/src/php/ext/yasd \
    && curl -sfL https://github.com/swoole/yasd/archive/refs/tags/v0.3.9.tar.gz -o yasd.tar.gz \
    && tar xfz yasd.tar.gz --strip-components=1 -C /usr/src/php/ext/yasd \
    && cd /usr/src/php/ext/yasd \
    && phpize --clean \
    && phpize \
    && docker-php-ext-configure yasd \
    && docker-php-ext-install -j$(nproc) yasd \
    && docker-php-ext-enable yasd \
    && docker-php-source delete \
    && rm -rf /tmp/* yasd.tar.gz

RUN pecl update-channels \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pcntl pdo pdo_mysql zip

# Copy existing application directory contents
COPY . /var/www

# Set working directory
WORKDIR /var/www

# -e option need from debug
CMD ["php", "-e", "artisan", "octane:start", "--host=host.docker.internal", "--port=8084", "--workers=2", "--task-workers=2", "--max-requests=1000", "--watch"]
