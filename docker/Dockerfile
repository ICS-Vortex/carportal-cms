FROM php:8.4-cli-bookworm AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        librabbitmq-dev \
        libicu-dev \
        libzip-dev \
        pkg-config \
        ${PHPIZE_DEPS} \
    && docker-php-ext-install intl pdo_pgsql opcache zip \
    && pecl install amqp redis \
    && docker-php-ext-enable amqp redis \
    && apt-get purge -y --auto-remove ${PHPIZE_DEPS} \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/router.php /opt/cms/router.php
COPY docker/web-entrypoint.sh /usr/local/bin/cms-web-entrypoint
COPY docker/worker-entrypoint.sh /usr/local/bin/cms-worker-entrypoint
RUN chmod +x /usr/local/bin/cms-web-entrypoint /usr/local/bin/cms-worker-entrypoint

WORKDIR /app
EXPOSE 8000

FROM app AS dev
CMD ["cms-web-entrypoint"]

FROM app AS prod
COPY . /app
RUN composer install --no-interaction --prefer-dist --no-dev --no-scripts --classmap-authoritative \
    && php bin/console assets:install public --no-interaction
CMD ["cms-web-entrypoint"]
