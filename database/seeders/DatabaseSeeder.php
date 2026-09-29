<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Departments
        $departmentNames = [
            'Adm',
            'Esr',
            'Corpu',
            'Part Bkj',
            'Part Biu',
            'Svc Sims',
            'Svc Biu',
            'UT School',
            'Magang',
        ];

        foreach ($departmentNames as $nama) {
            Department::firstOrCreate(['nama' => $nama]);
        }

        // Categories
        $categories = [
            ['nama' => 'Multimedia', 'deskripsi' => 'Peralatan presentasi, audio, dan dokumentasi.'],
            ['nama' => 'Teknik', 'deskripsi' => 'Peralatan ukur dan perkakas teknik.'],
            ['nama' => 'Tablet', 'deskripsi' => 'Tablet untuk media belajar.'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['nama' => $cat['nama']], $cat);
        }

        $multimediaId = Category::where('nama', 'Multimedia')->firstOrFail()->id;
        $teknikId = Category::where('nama', 'Teknik')->firstOrFail()->id;
        $tabletId = Category::where('nama', 'Tablet')->firstOrFail()->id;

        // 12 items contoh
        $items = [
            [
                'kode_aset' => 'PRJ-001',
                'nama' => 'Proyektor Epson EB-X49',
                'category_id' => $multimediaId,
                'merk' => 'Epson',
                'foto' => null,
                'jumlah_total' => 4,
                'jumlah_tersedia' => 4,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Proyektor 3600 lumens untuk presentasi.',
            ],
            [
                'kode_aset' => 'PRJ-002',
                'nama' => 'Proyektor BenQ MX560',
                'category_id' => $multimediaId,
                'merk' => 'BenQ',
                'foto' => null,
                'jumlah_total' => 2,
                'jumlah_tersedia' => 2,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Proyektor cadangan untuk kelas.',
            ],
            [
                'kode_aset' => 'SPR-001',
                'nama' => 'Speaker Portable JBL PartyBox',
                'category_id' => $multimediaId,
                'merk' => 'JBL',
                'foto' => null,
                'jumlah_total' => 3,
                'jumlah_tersedia' => 3,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Speaker portable untuk acara.',
            ],
            [
                'kode_aset' => 'KMR-001',
                'nama' => 'Kamera Mirrorless Canon EOS M50',
                'category_id' => $multimediaId,
                'merk' => 'Canon',
                'foto' => null,
                'jumlah_total' => 2,
                'jumlah_tersedia' => 2,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Kamera dokumentasi kegiatan.',
            ],
            [
                'kode_aset' => 'MIC-001',
                'nama' => 'Mic Wireless Saramonic',
                'category_id' => $multimediaId,
                'merk' => 'Saramonic',
                'foto' => null,
                'jumlah_total' => 5,
                'jumlah_tersedia' => 5,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Mic wireless clip-on.',
            ],
            [
                'kode_aset' => 'MTT-001',
                'nama' => 'Multitester Digital Sanwa EM7000',
                'category_id' => $teknikId,
                'merk' => 'Sanwa',
                'foto' => null,
                'jumlah_total' => 6,
                'jumlah_tersedia' => 6,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Multitester digital untuk praktik elektrik.',
            ],
            [
                'kode_aset' => 'MCM-001',
                'nama' => 'Micrometer Digital Mitutoyo 0-25mm',
                'category_id' => $teknikId,
                'merk' => 'Mitutoyo',
                'foto' => null,
                'jumlah_total' => 6,
                'jumlah_tersedia' => 6,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Micrometer untuk pengukuran presisi.',
            ],
            [
                'kode_aset' => 'BOR-001',
                'nama' => 'Bor Listrik Bosch GSB 13 RE',
                'category_id' => $teknikId,
                'merk' => 'Bosch',
                'foto' => null,
                'jumlah_total' => 3,
                'jumlah_tersedia' => 3,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Bor listrik 13mm.',
            ],
            [
                'kode_aset' => 'GDA-001',
                'nama' => 'Gerinda Tangan Makita 9553HN',
                'category_id' => $teknikId,
                'merk' => 'Makita',
                'foto' => null,
                'jumlah_total' => 3,
                'jumlah_tersedia' => 3,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Gerinda tangan 4 inch.',
            ],
            [
                'kode_aset' => 'TBL-001',
                'nama' => 'Tablet Belajar Samsung Galaxy Tab A9',
                'category_id' => $tabletId,
                'merk' => 'Samsung',
                'foto' => null,
                'jumlah_total' => 10,
                'jumlah_tersedia' => 10,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Tablet untuk media belajar siswa.',
            ],
            [
                'kode_aset' => 'TBL-002',
                'nama' => 'Tablet Belajar Xiaomi Redmi Pad SE',
                'category_id' => $tabletId,
                'merk' => 'Xiaomi',
                'foto' => null,
                'jumlah_total' => 10,
                'jumlah_tersedia' => 10,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Tablet cadangan untuk kelas.',
            ],
            [
                'kode_aset' => 'LAY-001',
                'nama' => 'Layar Proyektor Tripod 96 inch',
                'category_id' => $multimediaId,
                'merk' => 'Tripod Screen',
                'foto' => null,
                'jumlah_total' => 2,
                'jumlah_tersedia' => 2,
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'deskripsi' => 'Layar proyektor portable tripod.',
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['kode_aset' => $item['kode_aset']], $item);
        }

        // Users
        $admId = Department::where('nama', 'Adm')->firstOrFail()->id;
        $svcSimsId = Department::where('nama', 'Svc Sims')->firstOrFail()->id;
        $magangId = Department::where('nama', 'Magang')->firstOrFail()->id;

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@pinjamtools.test',
                'password' => Hash::make('password'),
                'no_hp' => '081111111111',
                'department_id' => $admId,
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@pinjamtools.test',
                'password' => Hash::make('password'),
                'no_hp' => '082222222222',
                'department_id' => $admId,
                'role' => 'admin',
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@pinjamtools.test',
                'password' => Hash::make('password'),
                'no_hp' => '083333333333',
                'department_id' => $svcSimsId,
                'role' => 'supervisor',
            ],
            [
                'name' => 'Peminjam',
                'email' => 'peminjam@pinjamtools.test',
                'password' => Hash::make('password'),
                'no_hp' => '084444444444',
                'department_id' => $magangId,
                'role' => 'peminjam',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
