FROM php:8.1-cli as base

RUN apt-get update && apt-get install -y git unzip libpq-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000

RUN groupadd -g $GID appgroup || true && \
    useradd -u $UID -g $GID -m appuser || true

WORKDIR /app

EXPOSE 8000

FROM base as dev

COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER appuser

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]

FROM base as prod

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

ENTRYPOINT ["php"]
CMD ["-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
