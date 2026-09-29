<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 50);
            $table->string('penerima', 150);
            $table->enum('channel', ['email', 'wa'])->default('email');
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->text('pesan');
            $table->enum('status_kirim', ['antri', 'terkirim', 'gagal'])->default('antri');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
