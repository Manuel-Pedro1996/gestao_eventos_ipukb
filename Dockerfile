# Estágio de Build para Assets (Vite)
FROM node:20-alpine as assets_builder
WORKDIR /app
COPY . .

# Estágio Final - PHP 8.4 Apache
FROM php:8.4-apache

# Dependências do Sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev zip unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip intl bcmath opcache

# Configurar limites de upload diretamente no PHP do container (Evita Erro 500)
RUN echo "upload_max_filesize = 32M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/php.ini

# Configuração do Apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Copiar Código e Assets Compilados
WORKDIR /var/www/html
COPY . .
COPY --from=assets_builder /app/public/build ./public/build

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Permissões iniciais
RUN chown -R www-data:www-data storage bootstrap/cache

# Porta do Render
EXPOSE 80

# Script de Inicialização
COPY deployment/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]