# ─────────────────────────────────────────────────────────────────────────────
# Todo App Backend – Dockerfile
#
# PHP 8.2 Apache with:
#   - mysqli extension       (for MySQL/MariaDB)
#   - mod_rewrite            (for CodeIgniter clean URLs)
#   - git + unzip            (for repo cloning & Composer)
#   - Composer               (for PHP dependencies)
#   - entrypoint script      (auto-pull repo, install deps, run migrations)
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
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2dissite 000-default && a2ensite 000-default

# ── Composer ─────────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# ── Entrypoint ───────────────────────────────────────────────────────────────
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Default Apache is on port 80
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
