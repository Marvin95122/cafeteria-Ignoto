FROM webdevops/php-nginx:8.2-alpine

# Le decimos al servidor que lea la carpeta "public" de Laravel
ENV WEB_DOCUMENT_ROOT=/app/public
ENV APP_ENV=production

# Copiamos todo tu código a la máquina de la nube
COPY . /app
WORKDIR /app

# Instalamos Node.js para poder compilar tus vistas y JavaScript
RUN apk add --no-cache nodejs npm

# Instalamos las dependencias de Laravel y compilamos el diseño
RUN composer install --no-interaction --optimize-autoloader
RUN npm install
RUN npm run build

# Creamos la base de datos en blanco y damos permisos
RUN touch database/database.sqlite
RUN chown -R application:application /app/storage /app/bootstrap/cache /app/database