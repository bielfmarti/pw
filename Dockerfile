FROM php:7.4.2-fpm-alpine

WORKDIR /app

RUN apk --update upgrade \
    && apk add --no-cache autoconf automake make gcc g++ icu-dev \
    && pecl install xdebug-2.8.1 \
    && docker-php-ext-install -j $(nproc) \
    pdo_mysql \
    && docker-php-ext-enable xdebug

COPY etc/php/ /usr/local/etc/php/
