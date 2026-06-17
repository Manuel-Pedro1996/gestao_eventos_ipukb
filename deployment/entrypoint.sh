#!/bin/sh

# 1. Criar as pastas de armazenamento necessárias para o Laravel e Livewire
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/app/livewire-tmp
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views

# 2. Forçar a criação do link simbólico com permissão em tempo de execução
php artisan storage:link --force

# 3. Dar controlo total ao utilizador do Apache (www-data)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Rodar os comandos normais do Laravel
php artisan migrate --force
php artisan optimize

# Executar o comando principal (apache2-foreground)
exec "$@"