<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp', 25)->nullable()->after('email');
            $table->foreignId('department_id')->nullable()->after('no_hp')->constrained()->nullOnDelete();
            $table->enum('role', ['peminjam', 'admin', 'supervisor', 'super_admin'])->default('peminjam')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['no_hp', 'role']);
        });
    }
};
