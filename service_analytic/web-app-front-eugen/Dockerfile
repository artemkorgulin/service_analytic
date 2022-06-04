FROM node:14-alpine

# Fix node-gyp error
RUN apk add g++ make python3

COPY ./package* /app/
WORKDIR /app
RUN npm --quiet install
COPY ./ /app/
RUN chmod +x entrypoint.production.sh
RUN npm --quiet run build
