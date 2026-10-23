**README** · [How to run](How-to-run.md) · [Command list](Command-list.md)

---

# Image Gallery

![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-5-FDAE4B)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-8-646CFF?logo=vite&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-database-4479A1?logo=mysql&logoColor=white)

## Dokumentasi

| Dokumen | Isi |
| --- | --- |
| [How-to-run.md](How-to-run.md) | Langkah instalasi dan menjalankan proyek dari nol, plus solusi masalah umum |
| [Command-list.md](Command-list.md) | Daftar perintah (Composer, Artisan, NPM, tes) dan sintaks pencarian tag |

## Fitur

### Situs publik

- **Posts & pencarian tag** — galeri bergaya thumbnail dengan 24 post per halaman, hover panel pada thumbnail, dan sintaks pencarian (`tag`, `-tag`, `rating:`, `user:`, `fav:`, `parent:`, `child:`, wildcard `*`).
- **Upload** — gambar (`jpg`, `jpeg`, `png`, `gif`, `webp`) dan video `mp4`, maksimal **100 MB**. File duplikat ditolak berdasarkan MD5. Tag baru bisa diberi kategori (`general`, `artist`, `character`, `copyright`, `meta`).
- **Autotagger (opsional)** — saran tag otomatis dari layanan autotagger eksternal. Jika layanan tidak aktif, tag tetap bisa diketik manual.
- **Parent / child / sibling** — relasi antar post dengan kotak info dan thumbnail berborder warna.
- **Favorites** — simpan post favorit dan lihat daftarnya.
- **Comments** — komentar dengan balasan, vote, dan unggah gambar; halaman riwayat dan pencarian komentar.
- **Notes** — catatan pada gambar lengkap dengan riwayat versi dan fitur *revert*.
- **Wiki** — halaman wiki per tag dan halaman wiki bebas, dengan riwayat perubahan dan tampilan *diff*.
- **Artists** — halaman `/artists` dengan pencarian (nama, URL, deleted, has tag), form New/Edit artist (nama, other names, group name, URLs), riwayat perubahan beserta diff (`/artist_versions`), dan daftar URL (`/artist_urls`, dengan dropdown nama artist yang hanya mencari tag artist). Nama artist di tabel membuka halaman wiki tag-nya.
- **Akun & profil** — statistik pengguna, ganti avatar, kartu profil saat hover nama pengguna.

### Panel admin (`/admin`)

- Resource **Posts**, **Tags**, **Users**, **User Roles**, **Roles**, **Permissions**, dan **Deletion Requests**.
- Approve post yang diunggah anggota (bulk action *Approve*).
- Dashboard dengan statistik galeri dan grafik: post & komentar per hari, 5 pengguna teraktif, serta perbandingan tag per kategori.
- Notifikasi panel (database notifications, polling 30 detik).

## Role & hak akses

Role disimpan di kolom `role` pada tabel `users` (sebagai sumber peringkat), lalu disinkronkan ke role [Spatie Permission](https://spatie.be/docs/laravel-permission) yang membawa permission.

| Role | Level | Kemampuan utama |
| --- | :---: | --- |
| **Owner** | 4 | Hanya boleh ada **satu**. Selalu bisa masuk `/admin`, memegang semua permission, menghapus post/komentar langsung. |
| **Admin** | 3 | Menghapus post/komentar langsung, meng-approve post, memutuskan *deletion request*. Masuk `/admin` selama role admin memegang permission `access_admin_panel`. |
| **Moderator** | 2 | Tidak bisa menghapus langsung; mengajukan **deletion request** dengan alasan, lalu admin/owner memutuskan. Tidak bisa masuk `/admin`. |
| **Member** | 1 | Upload (menunggu approval admin), komentar, favorit, notes, wiki. Tidak bisa masuk `/admin`. |

> [!NOTE]
> Post yang diunggah admin/owner langsung tampil. Post dari moderator dan member berstatus *pending* sampai di-approve di panel admin.

Cara mengubah role pengguna lewat terminal ada di [Command-list.md](Command-list.md#perintah-artisan-khusus-proyek).

## Teknologi

| Lapisan | Teknologi |
| --- | --- |
| Backend | PHP 8.4+, Laravel 13 |
| Panel admin | Filament 5 |
| Role & permission | spatie/laravel-permission |
| Frontend | Blade, Tailwind CSS 4, Vite 8 |
| Database | MySQL (bawaan `.env.example`); SQLite in-memory untuk tes |
| Antrean / cache / session | Driver `database` |
| Kualitas kode | Laravel Pint, PHPUnit 12 |

## Struktur folder

```text
Image-Gallery/
├── app/
│   ├── Console/Commands/     # users:make-admin, users:make-owner, tags:recalculate-counts
│   ├── Filament/             # Resources & Widgets panel admin
│   ├── Http/Controllers/     # Controller situs publik
│   ├── Models/               # Post, Tag, User, Comment, Note, WikiPage, ...
│   ├── Policies/             # Policy Role, Permission, User
│   ├── Providers/Filament/   # AdminPanelProvider (path /admin)
│   ├── Services/             # PostSearchService (sintaks pencarian)
│   └── Support/              # Permissions, TagColors, formatter wiki/notes
├── database/
│   ├── migrations/           # Skema tabel
│   └── seeders/              # AccountSeeder, RolesAndPermissionsSeeder
├── resources/
│   ├── css/ js/              # Aset Vite (Tailwind)
│   └── views/                # Template Blade
├── routes/web.php            # Seluruh route situs publik
├── tests/                    # Unit & Feature test
├── How-to-run.md
└── Command-list.md
```

## Mulai cepat

Ringkasan singkat (detail dan penjelasan tiap langkah ada di [How-to-run.md](How-to-run.md)):

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
# buat database MySQL bernama ImageGallery, lalu sesuaikan DB_* di .env
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Buka <http://localhost:8000>. Panel admin ada di <http://localhost:8000/admin>.

## Akun contoh (hasil seeder)

Setelah `php artisan db:seed`, tersedia akun berikut dengan kata sandi **`password`**:

| Email | Role |
| --- | --- |
| `owner@gmail.com` | Owner |
| `admin@gmail.com` | Admin |
| `moderator@gmail.com` | Moderator |
| `member@gmail.com` | Member |
| `test@example.com` | Member (user uji dari factory) |

> [!WARNING]
> Akun contoh hanya untuk pengembangan lokal. Hapus atau ganti kata sandinya sebelum aplikasi dipasang di server yang dapat diakses publik.

## Pengujian

```bash
composer test
```

Tes memakai SQLite in-memory (lihat `phpunit.xml`), jadi ekstensi PHP `pdo_sqlite` harus aktif. Perintah tes lainnya ada di [Command-list.md](Command-list.md#pengujian--kualitas-kode).