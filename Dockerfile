# syntax=docker/dockerfile:1.4

# Versions
FROM dunglas/frankenphp:latest-alpine AS frankenphp_upstream
FROM composer/composer:2-bin AS composer_upstream


# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

# persistent / runtime deps
# hadolint ignore=DL3018
RUN apk add --no-cache \
		acl \
		file \
		gettext \
		git \
    	nodejs \
    	npm \
    	sqlite \
        supervisor;

RUN set -eux; \
	install-php-extensions \
		apcu \
		intl \
		zip \
	;

# Install the PHP Redis extension.
#RUN apk add --no-cache autoconf \
#                        gcc \
#                        g++ \
#                        make ;
#RUN pecl install redis \
#    && docker-php-ext-enable redis
###> recipes ###
###> doctrine/doctrine-bundle ###
#RUN #apk add --no-cache --virtual .pgsql-deps postgresql-dev; \
#	docker-php-ext-install -j"$(nproc)" pdo_pgsql; \
#	apk add --no-cache --virtual .pgsql-rundeps so:libpq.so.5; \
#	apk del .pgsql-deps
###< doctrine/doctrine-bundle ###
###< recipes ###

COPY --link frankenphp/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --link --chmod=755 frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY --link frankenphp/text_speech_go_bot_worker.ini /etc/supervisor.d/text_speech_go_bot_worker.ini
COPY --link frankenphp/supervisord.conf /etc/supervisor/supervisord.conf

ENTRYPOINT ["docker-entrypoint"]

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer_upstream --link /composer /usr/bin/composer

HEALTHCHECK CMD wget --no-verbose --tries=1 --spider http://localhost:2019/metrics || exit 1

RUN chmod -R 777 /tmp

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev XDEBUG_MODE=off
VOLUME /app/storage/
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/sessions storage/views;

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

COPY --link frankenphp/conf.d/app.dev.ini $PHP_INI_DIR/conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link frankenphp/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/
COPY --link frankenphp/Caddyfile /etc/caddy/Caddyfile

# prevent the reinstallation of vendors at every change in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link . ./
RUN rm -Rf frankenphp/

RUN set -eux; \
	mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/sessions storage/views; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	sync;
