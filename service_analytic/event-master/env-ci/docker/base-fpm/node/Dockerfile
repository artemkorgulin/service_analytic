FROM node:lts

RUN npm install -g laravel-echo-server

RUN npm install -g laravel-echo socket.io-client

WORKDIR /var/www

VOLUME ["/var/www"]

CMD [ "laravel-echo-server", "start" ]
