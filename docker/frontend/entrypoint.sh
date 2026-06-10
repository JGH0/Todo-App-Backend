#!/bin/sh
#
# Frontend entrypoint
#   - Clones the latest code from GitHub
#   - Installs dependencies and builds the SPA
#   - Starts nginx
#
set -e

FRONTEND_REPO="${FRONTEND_REPO:-https://github.com/JGH0/Todo-App.git}"
VITE_API_BASE_URL="${VITE_API_BASE_URL:-/api/v1}"
BUILD_DIR="/tmp/todo-build"

echo "[frontend] Cloning latest code from ${FRONTEND_REPO} ..."
rm -rf "${BUILD_DIR}"
git clone --depth 1 "${FRONTEND_REPO}" "${BUILD_DIR}"

cd "${BUILD_DIR}"

# Force the correct API base URL
rm -f .env .env.*
echo "VITE_API_BASE_URL=${VITE_API_BASE_URL}" > .env
echo "VITE_API_BASE_URL=${VITE_API_BASE_URL}" > .env.production

echo "[frontend] Installing dependencies ..."
npm ci --no-audit --no-fund 2>/dev/null || npm install --no-audit --no-fund

echo "[frontend] Building SPA ..."
npm run build

echo "[frontend] Copying build to nginx ..."
cp -r dist/* /usr/share/nginx/html/

echo "[frontend] Starting nginx ..."
exec nginx -g "daemon off;"
