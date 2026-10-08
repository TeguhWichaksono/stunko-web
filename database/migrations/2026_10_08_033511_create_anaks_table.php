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
        // nama tabel dibuat 'anak' (bukan 'anaks')
        Schema::create('anak', function (Blueprint $table) {
            $table->id();

            // orang tua pemilik anak ini
            $table->foreignId('orang_tua_id')->constrained('orang_tua')->cascadeOnDelete();

            // biodata anak (wajib)
            $table->string('nama');
            $table->string('nik', 16)->unique();
            $table->string('jenis_kelamin', 1); // L atau P
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');

            // berat dan tinggi saat lahir (boleh kosong)
            $table->decimal('berat_lahir', 4, 2)->nullable(); // kg
            $table->decimal('tinggi_lahir', 4, 1)->nullable(); // cm

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anak');
    }
};
