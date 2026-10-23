[README](README.md) · **How to run** · [Command list](Command-list.md)

---

# How to Run

Panduan menjalankan **Image Gallery** di komputer lokal dari nol, langkah demi langkah. Ikuti urutannya; setiap langkah punya cara memeriksa bahwa ia berhasil.

## Daftar isi

1. [Prasyarat](#1-prasyarat)
2. [Siapkan kode proyek](#2-siapkan-kode-proyek)
3. [Install dependensi PHP](#3-install-dependensi-php)
4. [Buat file `.env` dan app key](#4-buat-file-env-dan-app-key)
5. [Buat database MySQL](#5-buat-database-mysql)
6. [Jalankan migrasi](#6-jalankan-migrasi)
7. [Isi data awal (seeder)](#7-isi-data-awal-seeder)
8. [Hubungkan folder storage](#8-hubungkan-folder-storage)
9. [Install dan build aset frontend](#9-install-dan-build-aset-frontend)
10. [Jalankan aplikasi](#10-jalankan-aplikasi)
11. [Login dan coba fitur](#11-login-dan-coba-fitur)
12. [Pengaturan opsional](#12-pengaturan-opsional)
13. [Jalur cepat](#13-jalur-cepat)
14. [Reset database](#14-reset-database)
15. [Mengatasi masalah umum](#15-mengatasi-masalah-umum)

---

## 1. Prasyarat

Pastikan perangkat lunak berikut sudah terpasang.

| Perangkat lunak | Versi | Cek dengan |
| --- | --- | --- |
| PHP | 8.4.1 atau lebih baru (`composer.json` mengizinkan ^8.3, tetapi `composer.lock` memuat paket Symfony 8 yang butuh 8.4.1+) | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | Sesuai kebutuhan Vite 8 (disarankan versi LTS terbaru) | `node -v` |
| NPM | Ikut terpasang bersama Node.js | `npm -v` |
| MySQL / MariaDB | Versi yang masih didukung Laravel 13 | `mysql --version` |

**Ekstensi PHP yang harus aktif:** `intl`, `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `curl`, `ctype`, `tokenizer`, `xml`. Untuk menjalankan tes, aktifkan juga `pdo_sqlite`.

Cek ekstensi yang aktif:

```bash
php -m
```

> [!TIP]
> Di Windows, paket seperti **Laragon** atau **XAMPP** sudah membawa PHP dan MySQL. Jika Composer mengeluh ekstensi `intl` tidak ada, aktifkan baris `extension=intl` di `php.ini`, lalu restart terminal (dan Apache jika dipakai).

## 2. Siapkan kode proyek

**Dari Git:**

```bash
git clone <url-repository> Image-Gallery
cd Image-Gallery
```

**Dari file ZIP:** ekstrak, lalu masuk ke folder hasil ekstrak (folder yang berisi `artisan` dan `composer.json`):

```bash
cd Image-Gallery-main
```

**Berhasil jika** perintah `ls` (Windows: `dir`) menampilkan `artisan`, `composer.json`, dan `package.json`.

> Semua perintah di panduan ini dijalankan dari folder tersebut (root proyek).

## 3. Install dependensi PHP

```bash
composer install
```

Composer mengunduh Laravel, Filament, Spatie Permission, dan paket lainnya ke folder `vendor/`. Setelah selesai, Composer otomatis menjalankan `package:discover` dan `filament:upgrade`.

**Berhasil jika** folder `vendor/` muncul dan tidak ada pesan error di akhir proses.

## 4. Buat file `.env` dan app key

Salin contoh konfigurasi:

```bash
# Linux / macOS / Git Bash
cp .env.example .env

# Windows (CMD atau PowerShell)
copy .env.example .env
```

Buat kunci enkripsi aplikasi:

```bash
php artisan key:generate
```

**Berhasil jika** baris `APP_KEY=` di `.env` kini berisi teks `base64:...`.

## 5. Buat database MySQL

Aplikasi memakai MySQL dengan nama database bawaan **`ImageGallery`**.

**Opsi A — lewat terminal MySQL:**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE ImageGallery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Opsi B — lewat phpMyAdmin / HeidiSQL / DBeaver:** buat database baru bernama `ImageGallery` dengan collation `utf8mb4_unicode_ci`.

Lalu buka `.env` dan cocokkan bagian database dengan MySQL Anda:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ImageGallery
DB_USERNAME=root
DB_PASSWORD=
```

> [!NOTE]
> Bawaan `.env.example` memakai user `root` **tanpa kata sandi**. Jika MySQL Anda memakai kata sandi, isi `DB_PASSWORD`. Di Linux, nama database **peka huruf besar/kecil**, jadi tulis persis `ImageGallery`.

Sekalian pastikan `APP_URL` sesuai alamat yang akan dibuka (bawaannya sudah benar untuk langkah 10):

```dotenv
APP_URL=http://localhost:8000
```

> [!IMPORTANT]
> `APP_URL` dipakai untuk membentuk alamat gambar yang diunggah. Jika Anda menjalankan server di port lain, ubah nilainya agar gambar tampil.

## 6. Jalankan migrasi

```bash
php artisan migrate
```

Perintah ini membuat semua tabel (users, posts, tags, comments, notes, wiki, role/permission, notifikasi, dan lainnya). Migrasi juga menyiapkan halaman wiki bantuan `help:home` dan `help:wiki`.

**Berhasil jika** setiap migrasi berstatus `DONE` dan tidak ada error koneksi database.

## 7. Isi data awal (seeder)

```bash
php artisan db:seed
```

Seeder membuat:

- Empat akun per role: `owner@gmail.com`, `admin@gmail.com`, `moderator@gmail.com`, `member@gmail.com` (kata sandi semuanya `password`).
- Seluruh permission sistem dan role Spatie, lalu menautkan setiap akun ke role-nya.

> [!WARNING]
> Jalankan `php artisan db:seed` **sekali saja** pada database yang baru. Mengulangnya akan gagal karena email `test@example.com` sudah ada. Untuk mengulang sebagian saja, lihat [Reset database](#14-reset-database).

**Berhasil jika** terminal menampilkan `INFO  Seeding database.` tanpa error.

## 8. Hubungkan folder storage

File yang diunggah disimpan di `storage/app/public`. Buat tautan agar bisa diakses lewat `/storage`:

```bash
php artisan storage:link
```

**Berhasil jika** muncul folder/tautan `public/storage`.

> Tanpa langkah ini, upload tetap berjalan tetapi gambar tidak akan tampil di halaman.

## 9. Install dan build aset frontend

```bash
npm install
```

Proyek memiliki `.npmrc` dengan `ignore-scripts=true`, jadi skrip pasca-install paket tidak dijalankan. Ini disengaja untuk keamanan.

Lalu pilih salah satu:

| Kebutuhan | Perintah | Keterangan |
| --- | --- | --- |
| Hanya ingin **mencoba** aplikasi | `npm run build` | Membuat aset final di `public/build`. Jalankan sekali. |
| Sedang **mengembangkan** tampilan | `npm run dev` | Menjalankan Vite dengan *hot reload*; biarkan terminal tetap terbuka. |

**Berhasil jika** `npm run build` selesai tanpa error dan folder `public/build` muncul.

## 10. Jalankan aplikasi

**Opsi A — satu perintah (skrip Composer `dev`):**

```bash
composer run dev
```

**Opsi B — manual dengan dua terminal** (cocok untuk pengembangan):

```bash
# Terminal 1: server Laravel
php artisan serve

# Terminal 2: Vite (hot reload). Lewati jika sudah menjalankan npm run build
npm run dev
```

Jika Anda sudah menjalankan `npm run build` di langkah 9, cukup `php artisan serve`.

**Berhasil jika** terminal menampilkan alamat `http://127.0.0.1:8000` dan halaman terbuka di browser.

> [!NOTE]
> Antrean memakai driver `database`, tetapi proyek ini belum memiliki job di `app/Jobs`, sehingga **queue worker tidak wajib** dijalankan.

## 11. Login dan coba fitur

| Tujuan | Alamat |
| --- | --- |
| Beranda galeri | <http://localhost:8000> |
| Semua post | <http://localhost:8000/posts> |
| Upload | <http://localhost:8000/upload> (perlu login) |
| Login situs | <http://localhost:8000/login> |
| Registrasi | <http://localhost:8000/register> |
| Wiki | <http://localhost:8000/wiki_pages> |
| Panel admin | <http://localhost:8000/admin> |

Urutan mencoba yang disarankan:

1. Buka `/login`, masuk dengan **`admin@gmail.com`** / **`password`**.
2. Buka `/upload`, pilih gambar, isi **rating** dan **tags** (dipisah spasi), lalu kirim. Karena admin, post langsung tampil.
3. Buka `/admin` dan lihat dashboard, resource Posts, dan Tags.
4. Keluar, masuk sebagai **`member@gmail.com`**, unggah satu gambar. Post ini berstatus *pending*.
5. Kembali sebagai admin, buka `/admin` → **Posts**, pilih post pending, lalu gunakan aksi **Approve**.

> [!NOTE]
> Panel `/admin` hanya bisa dibuka oleh **owner** dan **admin** (selama role admin memegang permission `access_admin_panel`). Moderator dan member akan ditolak.

## 12. Pengaturan opsional

### 12.1 Autotagger (saran tag otomatis)

Halaman upload dapat meminta saran tag ke layanan autotagger eksternal (<https://github.com/yukariin/autotagger>). Aplikasi mengirim gambar ke `<AUTOTAGGER_URL>/evaluate`.

1. Jalankan layanan autotagger sesuai petunjuk di repository-nya. Bawaan aplikasi mengarah ke `http://127.0.0.1:5000`.
2. Jika alamat atau batas waktunya berbeda, tambahkan ke `.env` (kedua baris ini belum ada di `.env.example`):

```dotenv
   AUTOTAGGER_URL=http://127.0.0.1:5000
   AUTOTAGGER_TIMEOUT=30
```

3. Bersihkan cache konfigurasi:

```bash
   php artisan config:clear
```

Jika autotagger tidak berjalan, upload **tetap bisa dipakai**: aplikasi hanya menampilkan pesan bahwa autotagger tidak terjangkau, dan tag diketik manual.

### 12.2 Batas ukuran upload PHP (100 MB)

Aplikasi menerima file hingga **100 MB**, tetapi PHP bawaan biasanya membatasi jauh lebih kecil. Cari file konfigurasi yang dipakai:

```bash
php --ini
```

Buka `php.ini` yang ditunjukkan, ubah nilai berikut, lalu restart `php artisan serve` (atau web server Anda):

```ini
upload_max_filesize = 100M
post_max_size = 100M
```

### 12.3 Menjadikan akun sendiri sebagai admin atau owner

Daftar lewat `/register`, lalu naikkan role dari terminal:

```bash
php artisan users:make-admin email-anda@contoh.com
php artisan users:make-owner email-anda@contoh.com
```

Detail kedua perintah ada di [Command-list.md](Command-list.md#perintah-artisan-khusus-proyek).

## 13. Jalur cepat

Jika MySQL sudah berjalan, database `ImageGallery` sudah dibuat, dan MySQL Anda memakai `root` tanpa kata sandi (sesuai `.env.example`), skrip `setup` di `composer.json` merangkum beberapa langkah sekaligus:

```bash
composer run setup
php artisan db:seed
php artisan storage:link
php artisan serve
```

`composer run setup` menjalankan: `composer install`, menyalin `.env` bila belum ada, `key:generate`, `migrate --force`, `npm install --ignore-scripts`, dan `npm run build`.

Jika pengaturan database Anda berbeda, ikuti langkah 3 sampai 9 secara manual.

## 14. Reset database

**Hapus semua tabel, buat ulang, dan isi data awal** (semua data lama hilang):

```bash
php artisan migrate:fresh --seed
```

**Hanya mengembalikan akun contoh dan role** tanpa menghapus data lain (aman diulang):

```bash
php artisan db:seed --class=AccountSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
```

> [!WARNING]
> `AccountSeeder` mengatur ulang kata sandi dan role keempat akun contoh ke nilai bawaan. Jika sudah ada owner lain, `owner@gmail.com` dilewati karena aplikasi hanya mengizinkan satu owner.

## 15. Mengatasi masalah umum

| Gejala | Penyebab | Solusi |
| --- | --- | --- |
| `Unable to locate file in Vite manifest` | Aset frontend belum dibuild | Jalankan `npm run build`, atau `npm run dev` di terminal terpisah |
| `No application encryption key has been generated` | `APP_KEY` kosong | `php artisan key:generate` |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL belum berjalan atau host/port salah | Nyalakan MySQL, periksa `DB_HOST` dan `DB_PORT` di `.env` |
| `SQLSTATE[HY000] [1049] Unknown database` | Database belum dibuat atau namanya beda | Buat database `ImageGallery` (langkah 5), samakan dengan `DB_DATABASE` |
| `Access denied for user` | Username/kata sandi MySQL salah | Perbaiki `DB_USERNAME` dan `DB_PASSWORD`, lalu `php artisan config:clear` |
| Gambar yang diunggah tidak tampil | `storage:link` belum dijalankan atau `APP_URL` tidak cocok | `php artisan storage:link`, dan samakan `APP_URL` dengan alamat yang dibuka di browser |
| Upload file besar gagal | Batas `upload_max_filesize` / `post_max_size` PHP | Lihat [12.2](#122-batas-ukuran-upload-php-100-mb) |
| `/admin` menampilkan 403 | Akun bukan owner/admin, atau role admin kehilangan `access_admin_panel` | Masuk sebagai `owner@gmail.com`, atau jalankan `php artisan users:make-admin <email>` |
| Perubahan `.env` tidak berpengaruh | Konfigurasi masih di-cache | `php artisan optimize:clear` |
| Perubahan permission tidak terbaca | Cache permission Spatie | `php artisan permission:cache-reset` |
| Error izin tulis pada `storage` atau `bootstrap/cache` (Linux/macOS) | Izin folder kurang | `chmod -R 775 storage bootstrap/cache` |
| Port 8000 sudah dipakai | Aplikasi lain memakai port tersebut | `php artisan serve --port=8001`, lalu ubah `APP_URL` menjadi `http://localhost:8001` |
| Jumlah post pada tag tidak sesuai | Kolom `post_count` tidak sinkron | `php artisan tags:recalculate-counts` |
| `Class "..." not found` setelah menambah file | Autoload belum diperbarui | `composer dump-autoload` |

Perintah lengkap untuk semua kebutuhan di atas ada di [Command-list.md](Command-list.md).