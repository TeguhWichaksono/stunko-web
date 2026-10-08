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
        // nama tabel dibuat 'pengukuran' (bukan 'pengukurans')
        Schema::create('pengukuran', function (Blueprint $table) {
            $table->id();

            // anak yang diperiksa dan posyandu yang memeriksa
            $table->foreignId('anak_id')->constrained('anak')->cascadeOnDelete();
            $table->foreignId('posyandu_id')->constrained('posyandu');

            // data pengukuran
            $table->date('tanggal_ukur');
            $table->string('periode', 7); // bulan pemeriksaan, contoh: 2026-10
            $table->decimal('usia_bulan', 5, 2); // dihitung otomatis dari tanggal lahir
            $table->decimal('berat', 5, 2); // kg
            $table->decimal('tinggi', 5, 1); // cm
            $table->decimal('lila', 4, 1)->nullable(); // cm, boleh kosong

            // hasil prediksi dari FastAPI
            $table->string('status_stunting');
            $table->string('status_gizi');
            $table->decimal('skor', 5, 1);
            $table->string('keputusan');
            $table->json('hasil_api'); // seluruh jawaban API (probabilitas tiap kelas)

            $table->timestamps();

            // satu anak hanya boleh satu pemeriksaan per bulan
            $table->unique(['anak_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengukuran');
    }
};
