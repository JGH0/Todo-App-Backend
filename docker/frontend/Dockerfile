# ─────────────────────────────────────────────────────────────────────────────
# Todo App Frontend – Dockerfile
#
# Single-stage: runtime clone + build at container start.
# Every restart pulls the latest code from GitHub.
# ─────────────────────────────────────────────────────────────────────────────

FROM nginx:alpine

# Install Node.js, npm, and git for runtime cloning + building
RUN apk add --no-cache nodejs npm git

# Copy custom nginx config (proxies /api/ to backend)
RUN rm /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Entrypoint – clones repo, builds SPA, starts nginx
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
