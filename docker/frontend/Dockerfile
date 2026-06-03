# ─────────────────────────────────────────────────────────────────────────────
# Todo App Frontend – Dockerfile
#
# Multi-stage:
#   1. build   – Node 22, clone repo, npm install, npm run build
#   2. runtime – Nginx, copy the built SPA
# ─────────────────────────────────────────────────────────────────────────────

# ── Stage 1: Build ───────────────────────────────────────────────────────────
FROM node:22-alpine AS builder

ARG FRONTEND_REPO=https://github.com/JGH0/Todo-App.git
ARG VITE_API_BASE_URL=http://localhost:8080

ENV VITE_API_BASE_URL=${VITE_API_BASE_URL}

RUN apk add --no-cache git

WORKDIR /build

# Clone the repo
RUN git clone --depth 1 "${FRONTEND_REPO}" .

# Install & build
RUN npm ci && npm run build

# ── Stage 2: Nginx runtime ───────────────────────────────────────────────────
FROM nginx:alpine

# Copy built files from builder stage
COPY --from=builder /build/dist /usr/share/nginx/html

# Custom nginx config
RUN rm /etc/nginx/conf.d/default.conf
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
