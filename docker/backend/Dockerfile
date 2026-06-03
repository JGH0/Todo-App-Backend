# ─────────────────────────────────────────────────────────────────────────────
# Todo App Backend – Dockerfile
#
# Build context: project root (Todo-App-Backend/)
# Dockerfile:    docker/backend/Dockerfile
#
# Copies local code into the image (avoids git clone during build),
# then runs composer install. Entrypoint does git pull at runtime.
# ─────────────────────────────────────────────────────────────────────────────

FROM php:8.2-apache

# ── System dependencies ──────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        mariadb-client \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions ───────────────────────────────────────────────────────────
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# ── Apache modules ───────────────────────────────────────────────────────────
RUN a2enmod rewrite

# ── Apache site config (point docroot at app/public) ─────────────────────────
# Overwrite the default vhost to point DocumentRoot at app/public
COPY docker/backend/000-default.conf /etc/apache2/sites-available/000-default.conf

# ── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# ── Copy application code ────────────────────────────────────────────────────
# Build context is the project root (Todo-App-Backend/).
# The entrypoint will git-pull updates at container start.
WORKDIR /var/www/app
COPY . .

# ── Install PHP dependencies ─────────────────────────────────────────────────
RUN composer install --no-interaction --no-progress --prefer-dist \
    && rm -rf /var/www/.composer/cache

# ── Entrypoint ───────────────────────────────────────────────────────────────
COPY docker/backend/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
