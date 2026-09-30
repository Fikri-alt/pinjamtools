#!/usr/bin/env bash
set -e

# Apache mengikuti $PORT dari Render (default 10000)
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

# Migrasi selalu jalan (aman diulang)
php artisan migrate --force

# Seed master data + akun bawaan hanya bila DB masih kosong
DEPTS=$(php artisan tinker --execute='echo App\Models\Department::count();' 2>/dev/null | tr -cd '0-9')
if [ -z "$DEPTS" ] || [ "$DEPTS" = "0" ]; then
  echo "Database kosong - menjalankan seeder..."
  php artisan db:seed --force
fi

exec apache2-foreground
