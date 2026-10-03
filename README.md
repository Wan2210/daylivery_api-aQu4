# Daylivery Admin

Panel operasional berbasis Laravel 12 untuk mengelola dan memantau pesanan Daylivery. Dashboard berada di `/admin` dan hanya dapat diakses oleh pengguna dengan peran `admin` serta status `active`.

## Persyaratan

- PHP 8.2 atau lebih baru dan Composer
- Node.js dan npm
- SQLite (konfigurasi bawaan) atau database lain yang didukung Laravel

## Instalasi lokal di Windows

Jalankan dari direktori proyek menggunakan PowerShell:

```powershell
composer install
if (!(Test-Path .env)) { Copy-Item .env.example .env }
if (!(Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
```

Isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env`. Kata sandi admin harus 12–72 karakter. Jangan masukkan kredensial asli ke repositori.

Kemudian siapkan aplikasi dan akun admin:

```powershell
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve
```

Buka URL yang ditampilkan oleh `php artisan serve`, lalu masuk melalui `/admin/login` dengan kredensial dari `.env`. Seeder membuat atau memperbarui akun admin yang ditentukan oleh `ADMIN_EMAIL`.

Untuk memakai MySQL atau database lain, ubah variabel `DB_*` di `.env` sebelum menjalankan migrasi.

## Struktur admin

```text
app/Http/Controllers/
	AdminAuthController.php        # Login dan logout admin
	AdminDashboardController.php   # Dashboard dan pengelolaan pesanan
app/Http/Middleware/
	EnsureUserIsAdmin.php          # Membatasi akses admin aktif
app/Models/
	Order.php                      # Data pesanan
database/migrations/             # Peran pengguna dan tabel pesanan
database/seeders/DatabaseSeeder.php  # Membuat akun admin dari .env
resources/views/admin/
	auth/login.blade.php           # Form login
	dashboard.blade.php            # Ringkasan dan daftar pesanan
	orders/create.blade.php        # Form tambah pesanan
routes/web.php                   # Rute halaman admin
```

## Fitur yang tersedia

- Login admin dengan pembatasan percobaan masuk, sesi aman, dan logout.
- Ringkasan pesanan hari ini, jumlah pesanan aktif, pesanan selesai, dan nilai pesanan non-pembatalan.
- Grafik nilai pesanan tujuh hari terakhir dan rincian status pesanan hari ini.
- Tambah pesanan dengan nomor otomatis, pencarian, filter status, paginasi, perubahan status, dan ekspor CSV.
- Tampilan responsif untuk desktop dan perangkat seluler.

Status pesanan yang digunakan: `menunggu`, `diproses`, `diantar`, `selesai`, dan `dibatalkan`. Data pesanan disimpan di tabel `orders`. Nilai pendapatan merupakan penjumlahan nilai pesanan selain yang dibatalkan, bukan konfirmasi pembayaran.

## Backend API mobile

API menggunakan prefix `/api/v1` dan bearer token Sanctum. Endpoint yang tersedia:

- `POST /api/v1/register` dengan `name`, `email`, `password`, dan `password_confirmation` untuk mendaftarkan customer dan memperoleh token.
- `POST /api/v1/login` dengan `email` dan `password` untuk memperoleh token.
- `GET /api/v1/user` untuk membaca profil dengan header `Authorization: Bearer <token>`.
- `POST /api/v1/logout` untuk mencabut token aktif.
- `GET /api/health` untuk memeriksa status API.

Pendaftaran selalu membuat akun berperan `customer` dengan status `active`; akun admin tidak dapat dibuat melalui API ini.