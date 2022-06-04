FROM nginx

RUN apt-get update \
        && apt-get -y --no-install-recommends install \
                                iputils-ping \
    && apt-get clean \
        && rm -rf /var/lib/apt/lists/*

RUN addgroup --gid 1000 --system www
RUN adduser nginx www

ADD nginx.conf /etc/nginx/nginx.conf
ADD conf.d/app.conf /etc/nginx/conf.d/app.conf
RUN rm -f /etc/nginx/conf.d/default.conf

VOLUME ["/var/www/", "/var/log/nginx/", "/socket"]
