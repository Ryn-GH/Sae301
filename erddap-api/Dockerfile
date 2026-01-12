# On part d'une version officielle de PHP
FROM php:8.2-cli

# On installe les outils nécessaires (zip, mysql...)
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# On installe Composer (le gestionnaire de paquets PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# On se place dans le dossier de l'application
WORKDIR /app

# On copie tout ton code dans le conteneur
COPY . .

# On installe les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# On lance le serveur sur le port 8080
CMD php artisan serve --host=0.0.0.0 --port=8080