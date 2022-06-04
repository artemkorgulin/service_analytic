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
    unzip \
    mc \
    cron \
    nano \
&& pecl install xdebug \
&& docker-php-ext-enable xdebug

ADD local.ini /usr/local/etc/php/php.ini

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

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

# Copy hello-cron file to the cron.d directory
COPY crontab /etc/cron.d/crontab
# Give execution rights on the cron job
RUN chmod 0600 /etc/cron.d/crontab
# Apply cron job
RUN crontab /etc/cron.d/crontab
# Create the log file to be able to run tail
RUN touch /var/log/cron.log
RUN chown  www:www  /var/log/cron.log

# Setup cron job
#RUN (crontab -u www -l ; echo "* * * * * echo "Hello world1" >> /var/log/cron.log") | crontab

# Run the command on container startup
#CMD cron && tail -f /var/log/cron.log
#RUN service cron start -f

#ENTRYPOINT cron start && tail -f /var/log/cron.log

# Change current user to www
USER www

# Set working directory
WORKDIR /var/www

VOLUME ["/var/www", "/var/run/php-fpm", "/var/log/php"]

CMD ["php-fpm",  "--allow-to-run-as-root", "--nodaemonize"]






