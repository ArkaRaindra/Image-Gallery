[README](README.md) · [How to run](How-to-run.md) · **Command list**

---

# Command List

Kumpulan perintah yang dipakai di proyek **Image Gallery**. Semua perintah dijalankan dari folder root proyek (yang berisi `artisan`).

## Daftar isi

- [Setup & instalasi](#setup--instalasi)
- [Menjalankan aplikasi](#menjalankan-aplikasi)
- [Database](#database)
- [Perintah Artisan khusus proyek](#perintah-artisan-khusus-proyek)
- [Role & permission (Spatie)](#role--permission-spatie)
- [Storage](#storage)
- [Frontend (NPM / Vite)](#frontend-npm--vite)
- [Pengujian & kualitas kode](#pengujian--kualitas-kode)
- [Cache & optimasi](#cache--optimasi)
- [Inspeksi & debugging](#inspeksi--debugging)
- [Membuat file baru (generator)](#membuat-file-baru-generator)
- [Sintaks pencarian tag](#sintaks-pencarian-tag)

---

## Setup & instalasi

| Perintah | Fungsi |
| --- | --- |
| `composer install` | Memasang semua dependensi PHP ke `vendor/` |
| `cp .env.example .env` | Membuat file konfigurasi (Windows: `copy .env.example .env`) |
| `php artisan key:generate` | Membuat `APP_KEY` di `.env` |
| `npm install` | Memasang dependensi frontend ke `node_modules/` |
| `composer run setup` | Jalur cepat: install, salin `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build` |
| `composer dump-autoload` | Memperbarui autoload class setelah menambah/memindah file |

> Langkah lengkap berurutan ada di [How-to-run.md](How-to-run.md).

## Menjalankan aplikasi

| Perintah | Fungsi |
| --- | --- |
| `composer run dev` | Menjalankan mode pengembangan lewat skrip `dev` di `composer.json` (memanggil `php artisan dev`) |
| `php artisan serve` | Menjalankan server Laravel di `http://127.0.0.1:8000` |
| `php artisan serve --port=8001` | Menjalankan di port lain (ubah juga `APP_URL` di `.env`) |
| `npm run dev` | Menjalankan Vite dengan *hot reload* |
| `npm run build` | Membuild aset produksi ke `public/build` |
| `php artisan queue:work` | Menjalankan queue worker (driver `database`). Belum wajib karena proyek belum memiliki job |

Contoh alur pengembangan dengan dua terminal:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

## Database

| Perintah | Fungsi |
| --- | --- |
| `php artisan migrate` | Menjalankan migrasi yang belum dijalankan |
| `php artisan migrate:status` | Melihat status tiap migrasi |
| `php artisan migrate:rollback` | Membatalkan batch migrasi terakhir |
| `php artisan migrate:fresh` | Menghapus semua tabel lalu migrasi ulang |
| `php artisan migrate:fresh --seed` | Sama seperti di atas, lalu mengisi data awal |
| `php artisan db:seed` | Menjalankan `DatabaseSeeder` (user uji + 4 akun role + role/permission) |
| `php artisan db:seed --class=AccountSeeder` | Membuat/mengatur ulang akun `owner`, `admin`, `moderator`, `member` @gmail.com (kata sandi `password`) |
| `php artisan db:seed --class=RolesAndPermissionsSeeder` | Membuat role & permission sistem dan menautkannya ke semua pengguna |

> [!WARNING]
> `migrate:fresh` dan `migrate:rollback` **menghapus data**. Jangan dipakai pada database yang berisi data penting.

> `php artisan db:seed` hanya aman dijalankan sekali pada database baru, karena membuat `test@example.com` dengan email unik. Untuk mengulang sebagian, pakai dua perintah `--class` di atas.

## Perintah Artisan khusus proyek

Tiga perintah ini didefinisikan di `app/Console/Commands/`.

### `users:make-admin`

Menaikkan (atau menurunkan) pengguna menjadi **admin**.

```bash
php artisan users:make-admin admin@gmail.com
```

| Aspek | Keterangan |
| --- | --- |
| Argumen | `email` — email pengguna yang sudah terdaftar |
| Hasil | Role pengguna menjadi `admin` |
| Ditolak jika | Email tidak ditemukan, atau pengguna adalah **owner** (owner tidak bisa diubah ke admin; pindahkan kepemilikan dulu dengan `users:make-owner`) |

### `users:make-owner`

Menjadikan pengguna sebagai **owner**. Owner lama otomatis menjadi **admin**, karena aplikasi hanya mengizinkan satu owner.

```bash
php artisan users:make-owner owner@gmail.com

# Tanpa pertanyaan konfirmasi
php artisan users:make-owner owner@gmail.com --force
```

| Aspek | Keterangan |
| --- | --- |
| Argumen | `email` — email pengguna yang sudah terdaftar |
| Opsi | `--force` — lewati konfirmasi saat sudah ada owner lain |
| Hasil | Pengguna menjadi owner; owner sebelumnya menjadi admin |
| Catatan | Jika pengguna itu sudah owner, perintah hanya memberi tahu dan selesai |

### `tags:recalculate-counts`

Menghitung ulang kolom `post_count` pada semua tag berdasarkan data pivot sebenarnya. Pakai bila jumlah post pada tag tampak tidak sesuai.

```bash
php artisan tags:recalculate-counts
```

## Role & permission (Spatie)

| Perintah | Fungsi |
| --- | --- |
| `php artisan db:seed --class=RolesAndPermissionsSeeder` | Membuat/menyinkronkan role dan permission sistem |
| `php artisan permission:cache-reset` | Membersihkan cache permission Spatie |

Role pengguna dapat diubah lewat terminal (`users:make-admin`, `users:make-owner`) atau lewat panel admin pada resource **User Roles** di `/admin`.

## Storage

| Perintah | Fungsi |
| --- | --- |
| `php artisan storage:link` | Membuat tautan `public/storage` → `storage/app/public` agar gambar yang diunggah tampil |
| `chmod -R 775 storage bootstrap/cache` | Memberi izin tulis pada folder cache dan upload (Linux/macOS) |

## Frontend (NPM / Vite)

| Perintah | Fungsi |
| --- | --- |
| `npm install` | Memasang dependensi (skrip pasca-install dinonaktifkan oleh `.npmrc`) |
| `npm run dev` | Vite *dev server* dengan hot reload |
| `npm run build` | Build produksi ke `public/build` |

## Pengujian & kualitas kode

| Perintah | Fungsi |
| --- | --- |
| `composer test` | Membersihkan cache konfigurasi lalu menjalankan seluruh tes |
| `php artisan test` | Menjalankan seluruh tes |
| `php artisan test --compact` | Menjalankan tes dengan keluaran ringkas |
| `php artisan test --filter=NamaTes` | Menjalankan tes tertentu saja |
| `php artisan test tests/Feature/RolesTest.php` | Menjalankan satu file tes |
| `vendor/bin/phpunit` | Memanggil PHPUnit langsung |
| `vendor/bin/pint` | Merapikan gaya kode semua file PHP (Laravel Pint) |
| `vendor/bin/pint --dirty` | Merapikan hanya file yang berubah menurut Git |

> Tes memakai SQLite in-memory (`phpunit.xml`), jadi ekstensi PHP `pdo_sqlite` harus aktif. Tes tidak menyentuh database MySQL Anda.

## Cache & optimasi

| Perintah | Fungsi |
| --- | --- |
| `php artisan optimize:clear` | Membersihkan semua cache (konfigurasi, route, view, event) |
| `php artisan config:clear` | Membersihkan cache konfigurasi (jalankan setelah mengubah `.env`) |
| `php artisan cache:clear` | Membersihkan cache aplikasi (driver `database`) |
| `php artisan view:clear` | Membersihkan cache view Blade |
| `php artisan route:clear` | Membersihkan cache route |
| `php artisan optimize` | Menyimpan cache konfigurasi, route, dan view (untuk produksi) |
| `php artisan filament:upgrade` | Memperbarui aset dan cache Filament (sudah otomatis dijalankan Composer setelah autoload dump) |

## Inspeksi & debugging

| Perintah | Fungsi |
| --- | --- |
| `php artisan about` | Ringkasan lingkungan: versi PHP/Laravel, driver, dan status cache |
| `php artisan list` | Menampilkan semua perintah Artisan |
| `php artisan <perintah> --help` | Menampilkan opsi suatu perintah |
| `php artisan route:list` | Menampilkan semua route |
| `php artisan route:list --path=wiki` | Memfilter route berdasarkan path |
| `php artisan route:list --except-vendor` | Menyembunyikan route bawaan paket |
| `php artisan config:show database` | Menampilkan konfigurasi database yang aktif |
| `php artisan tinker` | Konsol interaktif untuk mencoba kode dalam konteks aplikasi |
| `php artisan pail` | Membaca log aplikasi secara langsung (*real time*) |
| `composer show --direct` | Menampilkan paket PHP langsung beserta versinya |

Contoh di dalam `php artisan tinker`:

```php
User::count();
User::where('role', 'owner')->first();
Post::count();
Tag::orderByDesc('post_count')->limit(5)->pluck('post_count', 'name');
```

> Di dalam tinker, tambahkan namespace penuh jika class tidak dikenali, misalnya `App\Models\User::count();`.

## Membuat file baru (generator)

Gunakan generator Artisan agar struktur file mengikuti konvensi Laravel. Tambahkan `--no-interaction` bila dijalankan lewat skrip.

| Perintah | Fungsi |
| --- | --- |
| `php artisan make:model NamaModel -mfs` | Model beserta migrasi, factory, dan seeder |
| `php artisan make:migration nama_migrasi` | Migrasi baru |
| `php artisan make:controller NamaController` | Controller baru |
| `php artisan make:command NamaPerintah` | Perintah Artisan baru |
| `php artisan make:policy NamaPolicy --model=NamaModel` | Policy baru |
| `php artisan make:test NamaTest --phpunit` | Feature test baru |
| `php artisan make:test NamaTest --phpunit --unit` | Unit test baru |
| `php artisan make:filament-resource NamaModel` | Resource Filament baru untuk panel admin |

## Sintaks pencarian tag

Dipakai pada kolom pencarian di halaman **Posts**. Kata dipisahkan **spasi**; semua kondisi digabung dengan logika **DAN**.

| Sintaks | Arti | Contoh |
| --- | --- | --- |
| `tag` | Post yang memiliki tag tersebut | `landscape` |
| `tag1 tag2` | Post yang memiliki **kedua** tag | `landscape sunset` |
| `-tag` | Kecualikan post yang memiliki tag tersebut | `landscape -night` |
| `awalan*` | Wildcard: cocokkan tag yang diawali/mengandung pola | `blue_*` |
| `rating:general` | Filter rating: `general`, `sensitive`, `questionable`, `explicit` | `rating:general` |
| `user:nama` | Post yang diunggah pengguna tertentu (spasi pada nama ditulis `_`) | `user:Admin` |
| `fav:nama` | Post yang difavoritkan pengguna tertentu (spasi ditulis `_`) | `fav:Member` |
| `parent:none` | Post tanpa parent | `parent:none` |
| `parent:any` | Post yang memiliki parent | `parent:any` |
| `parent:123` | Post nomor 123 beserta anak-anaknya | `parent:123` |
| `child:none` | Post tanpa anak | `child:none` |
| `child:any` | Post yang memiliki anak | `child:any` |

Contoh gabungan:

```text
landscape -night rating:general user:Admin parent:none
```

Artinya: post bertag `landscape`, tanpa tag `night`, rating `general`, diunggah oleh `Admin`, dan bukan turunan dari post lain.

> Hanya post yang sudah **di-approve** yang muncul di hasil pencarian publik.