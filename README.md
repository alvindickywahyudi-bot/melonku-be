## Setup Project Paminal

### Server Requirements

-   PHP >= 8.1
-   Composer >= 2.5.8

### Instalasi

-   Clone Repositori

```bash
git clone git@gitlab.com:synergics-team/melonku/melonku-be.git <folder_nama_project>
```

-   Masuk ke direktori yang anda buat

```bash
cd <folder_nama_project>
```

-   Install Dependensi Composer

```bash
composer install
```

### Copy file .env

```bash
cp .env.example .env
```

### Generate Laravel Key

```bash
php artisan key:generate
```

### Konfigurasi koneksi database

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1

DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

### Jalankan Migrasi Database

```bash
php artisan migrate
```

### Jalankan Database Seeder

```bash
php artisan db:seed
```

### Generate Passport CLient Id dan Client Secret

```bash
php artisan passport:install
```

### Edit app url, passport secret passport client .env

```
APP_URL=https://namadomainanda
```
