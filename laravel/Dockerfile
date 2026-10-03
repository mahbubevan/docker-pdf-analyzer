FROM php:8.4-fpm 
WORKDIR /var/www/html 
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip \ 
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./ 
RUN composer install \ 
    --no-dev \ 
    --no-interaction \
    --prefer-dist \ 
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize 
CMD [ "php-fpm" ]