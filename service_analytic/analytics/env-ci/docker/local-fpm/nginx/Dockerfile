FROM nginx

RUN apt-get update \
	&& apt-get -y --no-install-recommends install \
				iputils-ping \
    && apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

RUN addgroup --gid 3000 --system www
RUN adduser --uid 3000 --system --disabled-login --disabled-password --gid 3000 www

ADD nginx.conf /etc/nginx/nginx.conf

VOLUME ["/var/www/", "/var/www/logs/"]

EXPOSE 441

RUN touch /var/run/nginx.pid \
 && chown -Rf www:www \
    /var/run/nginx.pid \
    /var/cache/nginx \
    /var/log/nginx

USER www
