<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset', 50)->unique();
            $table->string('nama', 150);
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('merk', 100)->nullable();
            $table->string('foto', 255)->nullable();
            $table->unsignedInteger('jumlah_total')->default(1);
            $table->unsignedInteger('jumlah_tersedia')->default(1);
            $table->enum('kondisi', ['Baik', 'Rusak', 'Perbaikan'])->default('Baik');
            $table->enum('status', ['Tersedia', 'Dipinjam', 'Perbaikan'])->default('Tersedia');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
