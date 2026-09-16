#!/usr/bin/env sh
set -e

# Cache konfigurasi untuk produksi (env vars dari Render sudah tersedia di sini)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Jalankan migrasi database (aman untuk produksi)
php artisan migrate --force

# Symlink storage (abaikan kalau sudah ada)
php artisan storage:link || true

# Jalankan aplikasi. Render mengisi $PORT secara otomatis.
php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
