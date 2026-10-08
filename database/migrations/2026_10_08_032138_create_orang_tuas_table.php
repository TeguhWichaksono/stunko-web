<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // nama tabel dibuat 'orang_tua' (bukan 'orang_tuas')
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();

            // akun login orang tua, satu akun satu biodata
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // biodata orang tua, boleh kosong dulu karena baru diisi setelah login pertama
            $table->string('nama_lengkap')->nullable();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('no_hp', 20)->nullable();
            $table->string('pekerjaan')->nullable();

            // alamat, dipakai untuk menyocokkan wilayah dengan posyandu
            $table->text('alamat')->nullable();
            $table->string('rt', 3)->nullable();
            $table->string('rw', 3)->nullable();
            $table->string('kelurahan')->nullable();

            // posyandu pilihan dan status pengajuannya
            // status: menunggu, diterima, atau ditolak
            $table->foreignId('posyandu_id')->nullable()->constrained('posyandu')->nullOnDelete();
            $table->string('status_pengajuan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};
