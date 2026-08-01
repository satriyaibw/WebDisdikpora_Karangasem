FROM php:8.3-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        libzip-dev \
        icu-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        oniguruma-dev \
        git \
        curl \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        intl \
        zip \
        opcache \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${GID} www || true \
    && adduser -u ${UID} -G www -s /bin/sh -D www

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-disdikpora.ini

USER www

EXPOSE 9000

CMD ["php-fpm"]
