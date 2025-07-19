# Dockerfile pour Frontend Vue.js - MuscuScope
# Build stage multi-stage pour optimiser la taille finale

# Stage 1: Build de l'application
FROM node:20-alpine AS builder

# Définir le répertoire de travail
WORKDIR /app

# Copier les fichiers de dépendances
COPY package*.json ./

# Installer gcloud CLI pour récupérer l'URL du backend
RUN apk add --no-cache curl python3 py3-pip bash

# Installer les dépendances (incluant devDependencies pour le build)
RUN npm ci --silent

# Copier le code source
COPY . .

# Arguments de build (passés depuis docker build ou script)
ARG VITE_API_URL=http://localhost:80
ARG VITE_APP_NAME=MuscuScope

# Variables d'environnement pour le build
ENV NODE_ENV=production
ENV VITE_API_URL=${VITE_API_URL}
ENV VITE_APP_NAME=${VITE_APP_NAME}

# Build de l'application pour la production (séquentiel pour éviter run-p)
RUN npm run type-check && npm run build-only

# Stage 2: Production avec Nginx (version plus sécurisée)
FROM nginx:1.26-alpine AS production

# Copier la configuration Nginx personnalisée
COPY <<EOF /etc/nginx/conf.d/default.conf
server {
    listen 80;
    listen [::]:80;
    server_name localhost;
    
    root /usr/share/nginx/html;
    index index.html;
    
    # Gestion des routes SPA (Single Page Application)
    location / {
        try_files \$uri \$uri/ /index.html;
    }
    
    # Cache pour les assets statiques
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Sécurité headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Compression gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
}
EOF

# Configuration Nginx principale pour utilisateur non-root
COPY <<EOF /etc/nginx/nginx.conf
worker_processes auto;
error_log /var/log/nginx/error.log notice;
pid /tmp/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    log_format main '\$remote_addr - \$remote_user [\$time_local] "\$request" '
                    '\$status \$body_bytes_sent "\$http_referer" '
                    '"\$http_user_agent" "\$http_x_forwarded_for"';
    
    access_log /var/log/nginx/access.log main;
    
    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    
    include /etc/nginx/conf.d/*.conf;
}
EOF

# Copier les fichiers buildés depuis le stage builder
COPY --from=builder /app/dist /usr/share/nginx/html

# Installer curl pour le health check
RUN apk add --no-cache curl

# Ajuster les permissions pour l'utilisateur nginx et forcer le PID location
RUN chown -R nginx:nginx /usr/share/nginx/html && \
    chown -R nginx:nginx /var/cache/nginx && \
    chown -R nginx:nginx /var/log/nginx && \
    chown -R nginx:nginx /etc/nginx && \
    # Créer les répertoires nécessaires avec les bonnes permissions
    mkdir -p /var/cache/nginx /var/log/nginx /tmp && \
    # Permissions complètes pour nginx sur ses répertoires
    chmod -R 755 /var/cache/nginx /var/log/nginx /tmp && \
    # Donner les permissions sur /tmp pour le PID file
    chown nginx:nginx /tmp && \
    # Remplacer le nginx.conf par défaut pour éviter les conflits
    rm -f /etc/nginx/nginx.conf.default

# Changer vers l'utilisateur non-root (standard sécurisé)
USER nginx

# Exposer le port 80
EXPOSE 80

# Health check avec curl
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Commande de démarrage avec configuration explicite
CMD ["nginx", "-g", "daemon off;", "-c", "/etc/nginx/nginx.conf"]
