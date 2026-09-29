<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pinjam', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_peminjam', 150);
            $table->string('email', 150);
            $table->string('no_hp', 25);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tgl_pinjam');
            $table->date('tgl_rencana_kembali');
            $table->date('tgl_kembali_aktual')->nullable();
            $table->text('tujuan');
            $table->enum('status', ['diajukan','disetujui','ditolak','dibatalkan','dipinjam','terlambat','dikembalikan','dikembalikan_terlambat','hilang'])->default('diajukan');
            $table->boolean('is_long_term')->default(false);
            $table->boolean('is_walkin')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('handover_kondisi')->nullable();
            $table->text('handover_catatan')->nullable();
            $table->string('handover_foto', 255)->nullable();
            $table->dateTime('handover_at')->nullable();
            $table->string('return_kondisi', 50)->nullable();
            $table->text('return_catatan')->nullable();
            $table->string('return_foto', 255)->nullable();
            $table->timestamps();
            $table->index(['status', 'tgl_rencana_kembali']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
