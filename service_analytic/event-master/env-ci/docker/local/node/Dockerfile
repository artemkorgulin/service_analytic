FROM node:lts

RUN npm install -g laravel-echo-server

RUN npm install -g laravel-echo socket.io-client

VOLUME ["/var/www/"]

WORKDIR /var/www

USER node

RUN rm -f laravel-echo-server.lock

CMD [ "laravel-echo-server", "start", "--force"]
