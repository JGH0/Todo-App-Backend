#!/bin/bash
#
# Backend entrypoint – pulls the Todo-App-Backend repo, installs deps,
# writes the .env, runs migrations, then starts Apache in the foreground.
#
set -euo pipefail

# Where the app lives inside the container
APP_DIR="/var/www/app"
APACHE_DOCROOT="${APP_DIR}/public"

# ── 1. Clone / pull the repo ────────────────────────────────────────────────
if [ -n "${BACKEND_REPO:-}" ]; then
    echo "[backend] Cloning/pulling from ${BACKEND_REPO} ..."

    if [ -d "${APP_DIR}/.git" ]; then
        cd "${APP_DIR}"
        git fetch --all
        git reset --hard origin/main
    else
        rm -rf "${APP_DIR}" 2>/dev/null || true
        git clone --depth 1 "${BACKEND_REPO}" "${APP_DIR}"
    fi
else
    echo "[backend] BACKEND_REPO not set – using existing code in ${APP_DIR}"
fi

cd "${APP_DIR}"

# ── 2. Composer install ──────────────────────────────────────────────────────
if [ -f composer.json ]; then
    echo "[backend] Installing Composer dependencies ..."
    composer install --no-interaction --no-progress --prefer-dist || true
fi

# ── 3. Write .env from environment variables ─────────────────────────────────
echo "[backend] Writing .env ..."
cat > .env <<ENVEOF
CI_ENVIRONMENT = development

database.default.hostname = ${DB_HOSTNAME:-db}
database.default.database = ${DB_DATABASE:-TodoApp}
database.default.username = ${DB_USERNAME:-root}
database.default.password = ${DB_PASSWORD:-}
database.default.DBDriver = MySQLi
database.default.port = ${DB_PORT:-3306}
ENVEOF

# ── 4. Wait for database and run migrations ──────────────────────────────────
echo "[backend] Waiting for database ..."
for i in $(seq 1 30); do
    if php -r "
        try {
            \\\$conn = new mysqli(
                getenv('DB_HOSTNAME') ?: 'db',
                getenv('DB_USERNAME') ?: 'root',
                getenv('DB_PASSWORD') ?: '',
                '',
                (int)(getenv('DB_PORT') ?: 3306)
            );
            echo \\\$conn->ping() ? 'ok' : 'fail';
            \\\$conn->close();
        } catch (\\\Exception \\\$e) { echo 'fail'; }
    " 2>/dev/null | grep -q ok; then
        echo "[backend] Database is ready."
        break
    fi
    echo "[backend] Waiting for database (attempt $i/30) ..."
    sleep 2
done

echo "[backend] Running migrations ..."
php spark migrate --no-interaction 2>&1 || echo "[backend] Migrations already applied or skipped."

# ── 5. Fix storage permissions ───────────────────────────────────────────────
chown -R www-data:www-data writable/ 2>/dev/null || true

# ── 6. Start Apache in foreground ────────────────────────────────────────────
echo "[backend] Starting Apache on port 80 ..."
exec apache2-foreground
