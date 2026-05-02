# ──────────────────────────────────────────────
# Stage 1: Build de assets (Node + Vite)
# ──────────────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts

COPY . .
RUN npm run build


# ──────────────────────────────────────────────
# Stage 2: Runtime PHP-FPM
# ──────────────────────────────────────────────
FROM php:8.5-fpm

WORKDIR /var/www/html

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl unzip zip \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    sqlite3 libsqlite3-dev \
    openssl rsync \
    && docker-php-ext-install \
        pdo pdo_sqlite \
        mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código da aplicação
COPY . /var/www/html

# Assets compilados do stage 1 (substitui qualquer build antigo)
COPY --from=node-builder /app/public /var/www/html/public

# .env de produção
COPY .env.production /var/www/html/.env

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Dependências PHP (sem dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
