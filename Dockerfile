FROM webdevops/php-nginx:8.2-alpine

ENV WEB_DOCUMENT_ROOT=/app/public
ENV APP_ENV=production
ENV DB_CONNECTION=sqlite
ENV DB_DATABASE=/app/database/database.sqlite
ENV SESSION_DRIVER=file
ENV CACHE_STORE=file
ENV QUEUE_CONNECTION=sync

COPY . /app
WORKDIR /app

RUN apk add --no-cache nodejs npm sqlite sqlite-dev

RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN npm ci || npm install
RUN npm run build

RUN if [ ! -f database/database.sqlite ]; then touch database/database.sqlite; fi \
    && php artisan migrate --force \
    && mkdir -p storage/app/public/products \
    && rm -rf public/storage \
    && php artisan storage:link \
    && php artisan optimize:clear

RUN chown -R application:application /app/storage /app/bootstrap/cache /app/database /app/public \
    && chmod -R ug+rwX /app/storage /app/bootstrap/cache /app/database /app/public