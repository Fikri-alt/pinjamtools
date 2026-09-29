<?php

use App\Http\Controllers\Admin\DamageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\LoanAdminController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanFlowController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// ===== HALAMAN PUBLIK =====
Route::get('/', [PublicController::class, 'beranda'])->name('beranda');
Route::get('/katalog', [PublicController::class, 'katalog'])->name('katalog');
Route::get('/barang/{id}', [PublicController::class, 'detail'])->name('barang.detail');

// ===== KERANJANG (session) =====
Route::get('/keranjang', [LoanFlowController::class, 'keranjang'])->name('keranjang');
Route::post('/keranjang/{id}', [LoanFlowController::class, 'tambahKeranjang'])->name('keranjang.tambah');
Route::delete('/keranjang/{id}', [LoanFlowController::class, 'hapusKeranjang'])->name('keranjang.hapus');

// ===== ALUR PINJAM 3 STEP =====
Route::get('/pinjam/step1', [LoanFlowController::class, 'step1'])->name('pinjam.step1');
Route::post('/pinjam/step1', [LoanFlowController::class, 'storeStep1'])->name('pinjam.step1.post');
Route::get('/pinjam/step2', [LoanFlowController::class, 'step2'])->name('pinjam.step2');
Route::post('/pinjam/step2', [LoanFlowController::class, 'storeStep2'])->name('pinjam.step2.post');
Route::get('/pinjam/step3', [LoanFlowController::class, 'step3'])->name('pinjam.step3');
Route::post('/pinjam/step3', [LoanFlowController::class, 'storeStep3'])->name('pinjam.step3.post');
Route::get('/pinjam/sukses/{kode}', [LoanFlowController::class, 'sukses'])->name('pinjam.sukses');

// ===== CEK STATUS =====
Route::get('/status', [LoanFlowController::class, 'cekStatus'])->name('status.form');
Route::post('/status', [LoanFlowController::class, 'cariStatus'])->name('status.cari');
Route::get('/status/{kode}', [LoanFlowController::class, 'showStatus'])->name('status.show');

// ===== AUTH =====
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== ADMIN: monitoring (read-only untuk supervisor) =====
// Admin: approve, handover, return, inventaris, kasus rusak.
// Supervisor: read-only — dashboard, antrean (lihat), aktif, riwayat, kerusakan (lihat), laporan.
// Super admin: semua + users & master data.
Route::middleware(['auth', 'role:admin,supervisor,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Lihat pengajuan / aktif / riwayat / detail
    Route::get('/pengajuan', [LoanAdminController::class, 'antrean'])->name('pengajuan');
    Route::get('/loans/aktif', [LoanAdminController::class, 'aktif'])->name('loans.aktif');
    Route::get('/loans/riwayat', [LoanAdminController::class, 'riwayat'])->name('loans.riwayat');
    Route::get('/loans/{id}', [LoanAdminController::class, 'show'])->name('loans.show')->whereNumber('id');

    // Lihat inventaris & kerusakan (aksi dibatasi grup operasional)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show')->whereNumber('item');
    Route::get('/damages', [DamageController::class, 'index'])->name('damages.index');
    Route::get('/damages/{id}', [DamageController::class, 'show'])->name('damages.show')->whereNumber('id');

    // Laporan (semua boleh lihat & ekspor)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/csv', [ReportController::class, 'exportCsv'])->name('reports.csv');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
});

// ===== ADMIN: operasional (admin + super_admin saja) =====
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Walk-in
    Route::get('/loans/walkin', [LoanAdminController::class, 'walkinForm'])->name('loans.walkin');
    Route::post('/loans/walkin', [LoanAdminController::class, 'walkinStore'])->name('loans.walkin.store');

    // Aksi loan
    Route::post('/loans/{id}/approve', [LoanAdminController::class, 'approve'])->name('loans.approve')->whereNumber('id');
    Route::post('/loans/{id}/reject', [LoanAdminController::class, 'reject'])->name('loans.reject')->whereNumber('id');
    Route::get('/loans/{id}/handover', [LoanAdminController::class, 'handoverForm'])->name('loans.handover')->whereNumber('id');
    Route::post('/loans/{id}/handover', [LoanAdminController::class, 'handoverStore'])->name('loans.handover.store')->whereNumber('id');
    Route::get('/loans/{id}/return', [LoanAdminController::class, 'returnForm'])->name('loans.return')->whereNumber('id');
    Route::post('/loans/{id}/return', [LoanAdminController::class, 'returnStore'])->name('loans.return.store')->whereNumber('id');
    Route::post('/loans/{id}/hilang', [LoanAdminController::class, 'markLost'])->name('loans.hilang')->whereNumber('id');
    Route::post('/extensions/{extensionId}/keputusan', [LoanAdminController::class, 'extensionApprove'])->name('extensions.keputusan')->whereNumber('extensionId');

    // Inventaris (tambah/ubah/hapus)
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit')->whereNumber('item');
    Route::match(['put', 'patch'], '/items/{item}', [ItemController::class, 'update'])->name('items.update')->whereNumber('item');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy')->whereNumber('item');

    // Kerusakan (tindak lanjut & hapus)
    Route::post('/damages/{id}/tindak-lanjut', [DamageController::class, 'tindakLanjut'])->name('damages.tindak')->whereNumber('id');
    Route::delete('/damages/{id}', [DamageController::class, 'destroy'])->name('damages.destroy')->whereNumber('id');
});

// ===== ADMIN: users & master (super_admin saja) =====
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->whereNumber('id');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update')->whereNumber('id');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy')->whereNumber('id');
});
