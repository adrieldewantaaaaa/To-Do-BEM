# ---- Stage 1: build frontend (Vite + Vue + Tailwind) ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: PHP application ----
FROM php:8.2-cli-alpine AS app
WORKDIR /app

# System deps + PHP extensions yang dibutuhkan Laravel
RUN apk add --no-cache bash git unzip libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependency PHP (tanpa dev, dioptimalkan)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Salin seluruh source
COPY . .

# Ambil hasil build frontend dari stage sebelumnya
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint: cache config, migrasi, lalu jalankan server
COPY docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8080
CMD ["entrypoint"]
