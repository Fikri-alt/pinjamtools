# PinjamTools Corpu

Aplikasi web peminjaman tools Corpu Batukajang (Multimedia & Teknik): katalog, pengajuan 3 langkah,
approval admin, serah terima (handover), pengembalian, dashboard monitoring, laporan, dan manajemen
user/role. Dibangun dengan Laravel 12 + MySQL.

## Syarat

- PHP >= 8.2 (ekstensi: pdo_mysql, mbstring, openssl, dll - lihat `composer.json`)
- Composer 2
- MySQL/MariaDB (XAMPP: pastikan service MySQL jalan)

## Setup lokal

```bash
cp .env.example .env
# sesuaikan DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD di .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka `http://localhost:8000`.

## Akun bawaan (password: `password`)

| Email | Role |
|---|---|
| superadmin@pinjamtools.test | super_admin |
| admin@pinjamtools.test | admin |
| supervisor@pinjamtools.test | supervisor |
| peminjam@pinjamtools.test | peminjam |

## Peran

- **Admin**: approval, handover, return, inventaris, kasus kerusakan.
- **Supervisor**: read-only - dashboard, antrean (lihat), laporan.
- **Super admin**: semua + kelola users.
- **Peminjam**: katalog, ajukan (tanpa login bisa, cukup isi data diri), cek status via kode `PJM-...`.

## Perintah terjadwal

```bash
php artisan loans:tandai-terlambat   # tandai loan lewat jatuh tempo
php artisan loans:kirim-pengingat    # pengingat H-1 & H-0
```

## Catatan keamanan

Jangan commit file sensitif: `.env*` (kecuali `.env.example`), `*.sqlite`,
`vendor/`, `node_modules/`, dan file upload di `storage/app/public/` - semuanya
sudah tercakup di `.gitignore`.
