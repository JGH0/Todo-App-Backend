# ─────────────────────────────────────────────────────────────────────────────
# Todo App Backend – Dockerfile
#
# PHP 8.2 Apache with:
#   - mysqli extension       (for MySQL/MariaDB)
#   - mod_rewrite            (for CodeIgniter clean URLs)
#   - git + unzip            (for repo cloning & Composer)
#   - Composer               (for PHP dependencies)
#   - Repo cloned + deps     (baked into image at build time)
#   - entrypoint script      (git pull, write .env, run migrations)
# ─────────────────────────────────────────────────────────────────────────────

FROM php:8.2-apache

ARG BACKEND_REPO=https://github.com/JGH0/Todo-App-Backend.git

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
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2dissite 000-default && a2ensite 000-default

# ── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# ── Clone the application code & install dependencies ────────────────────────
# This happens at BUILD time so the vendor directory is baked into the image.
# The entrypoint will git-pull updates at container start.
WORKDIR /var/www/app
RUN git clone --depth 1 "${BACKEND_REPO}" /var/www/app \
    && composer install --no-interaction --no-progress --prefer-dist \
    && rm -rf /var/www/.composer/cache

# ── Entrypoint ───────────────────────────────────────────────────────────────
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
