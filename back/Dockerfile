# Dockerfile pour Backend Symfony - MuscuScope
# Build multi-stage pour optimiser la production

# Stage 1: Build des dépendances et optimisations
FROM php:8.3-apache-bookworm AS builder

# Installation des outils et dépendances pour le build
RUN apt-get -qq update && \
    apt-get -qq install -y git libpq-dev libicu-dev locales libzip-dev libgd-dev libxml2-dev unzip && \
    rm -rf /var/lib/apt/lists/*

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installation des extensions PHP
RUN docker-php-ext-install gd intl opcache pdo_pgsql soap zip

# Répertoire de travail pour le build
WORKDIR /app

# Copier les fichiers de dépendances Composer
COPY composer.json composer.lock ./

# Installation des dépendances Composer (optimisé pour production, sans scripts)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-scripts

# Copier le code source
COPY . .

# Créer le fichier .env.local pour forcer l'environnement de production
RUN echo "APP_ENV=prod" > .env.local && \
    echo "APP_DEBUG=0" >> .env.local

# Ignorer les scripts post-install et faire directement les optimisations

# Optimisations Symfony pour la production (avec environnement explicite)
RUN APP_ENV=prod APP_DEBUG=0 composer dump-autoload --optimize --no-dev && \
    APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --env=prod --no-debug && \
    APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --env=prod --no-debug

# Stage 2: Image de production finale
FROM php:8.3-apache-bookworm AS production

# Créer la configuration Apache directement
COPY <<EOF /etc/apache2/sites-available/symfony.conf
<VirtualHost *:80>
    DocumentRoot /var/www/app/public
    <Directory /var/www/app/public>
        Require all granted
        AllowOverride All
        FallbackResource /index.php
    </Directory>

    ErrorLog /var/log/app/error.log
    CustomLog /var/log/app/access.log combined
</VirtualHost>
EOF

# Installation des packages système et configuration
RUN apt-get -qq update && \
    apt-get -qq install -y libpq-dev libicu-dev locales libzip-dev libgd-dev libxml2-dev curl && \
    echo -e "en_US.UTF-8 UTF-8\nfr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen && \
    rm -rf /var/lib/apt/lists/*

# Configuration PHP pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    echo "date.timezone = Europe/Paris" > $PHP_INI_DIR/conf.d/date-timezone.ini && \
    echo "session.save_path = /var/sessions" > $PHP_INI_DIR/conf.d/session-save_path.ini && \
    echo "opcache.enable = 1" > $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.memory_consumption = 128" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer = 8" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files = 4000" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq = 60" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.fast_shutdown = 1" >> $PHP_INI_DIR/conf.d/opcache.ini

# Installation des extensions PHP
RUN docker-php-ext-install gd intl opcache pdo_pgsql soap zip

# Configuration système
RUN ln -sf /usr/share/zoneinfo/Europe/Paris /etc/localtime && \
    useradd --uid 1000 --home /var/www user && \
    mkdir -p /var/log/app /var/www/app /var/sessions && \
    a2dissite 000-default && \
    a2ensite symfony && \
    a2enmod rewrite headers && \
    touch /var/log/app/access.log /var/log/app/error.log && \
    chown -R 1000:1000 /var/www /var/sessions /var/log/app && \
    sed -i -e 's/www-data/user/' /etc/apache2/envvars

# Copier l'application buildée depuis le stage builder
COPY --from=builder --chown=1000:1000 /app /var/www/app

# Arguments de build pour les variables d'environnement dynamiques
ARG CORS_ALLOW_ORIGIN="*"

# Variables d'environnement pour la production
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN}
ENV LANG=fr_FR.UTF-8
ENV LANGUAGE=fr_FR.UTF-8
ENV LC_ALL=fr_FR.UTF-8

# Configuration supplémentaire des permissions
RUN chown -R 1000:1000 /var/www/app && \
    chmod -R 755 /var/www/app && \
    chmod -R 777 /var/www/app/var

# Changer vers l'utilisateur non-root
USER 1000
WORKDIR /var/www/app

# Exposer les ports
EXPOSE 80 8000

# Health check pour monitoring
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Point d'entrée personnalisé pour la production
COPY --chown=1000:1000 <<EOF /var/www/app/docker-entrypoint.sh
#!/bin/bash
set -e

# Attendre que la base de données soit prête
echo "Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
done

# Exécuter les migrations en production
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Créer le répertoire de cache si nécessaire
mkdir -p var/cache var/log
chmod 777 var/cache var/log

# Vérifier la configuration Symfony
php bin/console about --env=prod

echo "Starting Apache..."
exec apache2-foreground
EOF

RUN chmod +x /var/www/app/docker-entrypoint.sh

# Commande de démarrage
CMD ["/var/www/app/docker-entrypoint.sh"]
