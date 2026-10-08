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
        // nama tabel dibuat 'posyandu' (bukan 'posyandus')
        Schema::create('posyandu', function (Blueprint $table) {
            $table->id();

            // akun login posyandu (dibuat oleh admin), satu akun satu profil
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            // profil yang dilengkapi sendiri oleh posyandu
            // dibuat boleh kosong dulu karena saat admin membuat akun, profilnya belum diisi
            $table->string('nama_kepala')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('wilayah')->nullable(); // contoh: RW 01 - RW 03
            $table->text('alamat')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posyandu');
    }
};
