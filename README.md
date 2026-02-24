# HRI System

Aplikasi Laravel 12 menggunakan Laravel Sail, Inertia.js, dan MySQL 8.4.

------------------------------------------------------------------------

# Teknologi & Versi

  Teknologi                      Versi
  ------------------------------ ------------------------
  - PHP                            \^8.2
  - Laravel                        \^12.0
  - Laravel Sail                   \^1.41
  - Inertia.js (Laravel Adapter)   \^2.0
  - MySQL                          8.4
  - Docker                         Compose v2
  - Node.js                        18+ (direkomendasikan)

------------------------------------------------------------------------

# Environment Development

Project ini menggunakan Laravel Sail sebagai environment development
berbasis Docker.

> Docker Desktop dan WSL2 (untuk pengguna Windows) wajib terinstall.

------------------------------------------------------------------------

# Persyaratan

Pastikan sudah menginstall:

-   Docker Desktop
-   WSL2 (khusus Windows)
-   Git

Cek apakah Docker sudah terinstall:

``` bash
docker compose version
```

Cek WSL:

``` bash
wsl --status
```


------------------------------------------------------------------------

# Cara Instalasi

## Clone Repository

``` bash
git clone <repository-url>
cd <nama-folder-project>
```

## Install Dependency PHP

``` bash
composer install
```

## Copy File Environment

``` bash
cp .env.example .env
```

## Generate App Key

``` bash
php artisan key:generate
```

## Jalankan Laravel Sail

``` bash
./vendor/bin/sail up -d
```

Windows PowerShell:

``` bash
vendor\bin\sail up -d
```

## Jalankan Migrasi Database

``` bash
./vendor/bin/sail artisan migrate
```

## Install Dependency Frontend

``` bash
./vendor/bin/sail npm install
```

## Jalankan Vite Dev Server

``` bash
./vendor/bin/sail npm run dev
```

------------------------------------------------------------------------

# Akses Aplikasi

Buka di browser:

http://localhost

Jika kamu mengubah `APP_PORT` di file `.env`, sesuaikan port yang
digunakan.

------------------------------------------------------------------------

# Menghentikan Container

``` bash
./vendor/bin/sail down
```

------------------------------------------------------------------------

# Masuk ke Shell Container

``` bash
./vendor/bin/sail shell
```

------------------------------------------------------------------------

# Perintah yang Sering Digunakan

Menjalankan perintah artisan:

``` bash
./vendor/bin/sail artisan <command>
```

Menjalankan perintah npm:

``` bash
./vendor/bin/sail npm <command>
```

Contoh:

``` bash
./vendor/bin/sail artisan migrate:fresh --seed
```

## Cek Status Container

``` bash
./vendor/bin/sail ps
```

------------------------------------------------------------------------

# File Penting

-   `compose.yml` → Konfigurasi Docker (Laravel Sail)
-   `.env` → Konfigurasi environment (jangan di-commit)
-   `composer.json` → Dependency PHP
-   `package.json` → Dependency Frontend

------------------------------------------------------------------------

# Catatan Penting

-   Jangan commit file `.env`
-   Jangan commit folder `vendor/`
-   Jangan commit folder `node_modules/`
-   Pastikan Docker dalam keadaan running sebelum menjalankan Sail
-   Project menggunakan Docker Compose v2 (`compose.yml`)
- Jangan gunakan MySQL dari XAMPP.
- Database project berjalan di dalam container Docker (MySQL 8.4).
- Pastikan Docker dalam keadaan running sebelum menjalankan Sail.

------------------------------------------------------------------------

# Arsitektur Project

-   Laravel menangani backend dan business logic
-   Inertia.js sebagai penghubung backend dan frontend (SPA)
-   Vite untuk bundling dan Hot Module Reload (HMR)
-   MySQL berjalan di dalam container Docker
-   Laravel Sail mengelola seluruh service Docker
