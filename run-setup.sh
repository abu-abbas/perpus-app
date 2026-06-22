#!/bin/bash

# Hentikan eksekusi jika terjadi error
set -e

echo "=== Memulai Setup Perpustakaan App ==="

# 1. Salin file .env jika belum ada
if [ ! -f .env ]; then
    echo "-> Menyalin .env dari .env.example..."
    cp .env.example .env
fi

# 2. Buat database SQLite jika belum ada
if [ ! -f database/database.sqlite ]; then
    echo "-> Membuat file database/database.sqlite..."
    touch database/database.sqlite
fi

# 3. Hapus tes bawaan starter kit dan file konfigurasi yang tidak terpakai
echo "-> Membersihkan folder tes bawaan, provider, dan file konfigurasi Fortify/Inertia..."
rm -rf tests/Feature/Auth \
       tests/Feature/Settings \
       tests/Feature/DashboardTest.php \
       tests/Feature/ExampleTest.php \
       tests/Unit/ExampleTest.php \
       config/fortify.php \
       config/inertia.php \
       app/Providers/FortifyServiceProvider.php \
       bootstrap/cache/packages.php \
       bootstrap/cache/services.php \
       app/Actions/Fortify \
       app/Http/Controllers/Settings \
       app/Http/Requests/Settings





# 4. Install dependency Composer (Backend)
echo "-> Menginstall dependensi Composer (Backend)..."
composer update

# 5. Jalankan fresh migration dan seeding database
echo "-> Melakukan migrasi database dan pengisian seeder..."
php artisan migrate:fresh --seed

# 6. Buat storage symbolic link
echo "-> Membuat storage symbolic link..."
php artisan storage:link || echo "Symbolic link storage sudah terpasang."

# 7. Install dependency Yarn (Frontend)
echo "-> Menginstall dependensi Yarn (Frontend)..."
yarn install

# 8. Build aset frontend SPA
echo "-> Membangun bundle produksi aset frontend..."
yarn build

echo "=== Setup Berhasil! Menjalankan Test Suite ==="

# 9. Jalankan phpunit tests
php artisan test

echo "=== Semua tes lulus! Aplikasi siap digunakan ==="
echo "Untuk menjalankan server development:"
echo "• Backend: php artisan serve"
echo "• Frontend: yarn dev"
