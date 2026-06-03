#!/bin/bash
#
# Backend entrypoint
#   - Updates the code from GitHub (git pull)
#   - Re-runs composer if composer.lock changed
#   - Writes .env from environment variables
#   - Waits for database
#   - Runs migrations
#   - Starts Apache in foreground
#
set -euo pipefail

APP_DIR="/var/www/app"

cd "${APP_DIR}"

# ── 1. Update code from GitHub ──────────────────────────────────────────────
echo "[backend] Updating code from GitHub ..."
git fetch --all 2>/dev/null || true
git reset --hard origin/main 2>/dev/null || echo "[backend] No updates (staying on current HEAD)"

# ── 2. Update Composer dependencies if needed ────────────────────────────────
echo "[backend] Updating Composer dependencies ..."
composer install --no-interaction --no-progress --prefer-dist

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

# ── 4. Wait for database ─────────────────────────────────────────────────────
echo "[backend] Waiting for database ..."
for i in $(seq 1 30); do
    if php -r "
        try {
            \$conn = new mysqli(
                getenv('DB_HOSTNAME') ?: 'db',
                getenv('DB_USERNAME') ?: 'root',
                getenv('DB_PASSWORD') ?: '',
                '',
                (int)(getenv('DB_PORT') ?: 3306)
            );
            echo \$conn->ping() ? 'ok' : 'fail';
            \$conn->close();
        } catch (\Exception \$e) { echo 'fail'; }
    " 2>/dev/null | grep -q ok; then
        echo "[backend] Database is ready."
        break
    fi
    echo "[backend] Waiting for database (attempt $i/30) ..."
    sleep 2
done

# ── 5. Run migrations ────────────────────────────────────────────────────────
echo "[backend] Running migrations ..."
php spark migrate --no-interaction 2>&1 || echo "[backend] Migrations already applied."

# ── 6. Fix storage permissions ───────────────────────────────────────────────
chown -R www-data:www-data writable/ 2>/dev/null || true

# ── 7. Start Apache in foreground ────────────────────────────────────────────
echo "[backend] Starting Apache on port 80 ..."
exec apache2-foreground
