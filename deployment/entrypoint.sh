#!/bin/sh

# 1. Criar as pastas necessárias para o Livewire e Storage se não existirem
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/app/livewire-tmp

# 2. Garantir que o utilizador do Apache (www-data) é dono absoluto destas pastas
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Rodar os comandos normais do Laravel
php artisan migrate --force
php artisan optimize

# Executar o comando principal (apache2-foreground)
exec "$@"