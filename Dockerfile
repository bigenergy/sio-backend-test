FROM php:8.3-cli-alpine AS sio_test
RUN apk add --no-cache git zip bash

# Setup php extensions
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql

ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup php app user
ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app

# Symfony writes cache/logs to var/, which docker-compose.yml keeps in a named volume
# instead of the bind mount. Create it here so the volume inherits app's ownership
# rather than being provisioned root-owned on first start.
RUN mkdir -p /app/var && chown -R app:app /app

USER app

COPY --chown=app . /app
WORKDIR /app

EXPOSE 8337

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public"]
