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
        // daftar jenis bantuan (susu) beserta rentang usia sasarannya, diisi sendiri oleh admin
        Schema::create('jenis_bantuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedSmallInteger('usia_min'); // usia terkecil dalam bulan
            $table->unsignedSmallInteger('usia_maks'); // usia terbesar dalam bulan
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // catatan tindak lanjut bantuan: satu baris untuk tiap anak
        Schema::create('bantuan', function (Blueprint $table) {
            $table->id();

            // anak yang ditindaklanjuti (satu anak satu catatan)
            $table->foreignId('anak_id')->unique()->constrained('anak')->cascadeOnDelete();

            // status: baru, dihubungi, berhak, atau tidak
            $table->string('status', 15)->default('baru');

            // saat orang tua dihubungi untuk periksa lanjutan ke dokter spesialis
            $table->dateTime('dihubungi_pada')->nullable();
            $table->text('catatan_hubungi')->nullable();

            // hasil pemeriksaan dokter spesialis anak
            $table->date('tanggal_periksa_spesialis')->nullable();
            $table->text('hasil_spesialis')->nullable();

            // keputusan akhir: berhak (dengan jenis bantuan) atau tidak berhak (dengan alasan)
            $table->foreignId('jenis_bantuan_id')->nullable()->constrained('jenis_bantuan')->nullOnDelete();
            $table->text('alasan_keputusan')->nullable();
            $table->dateTime('diputuskan_pada')->nullable();
            $table->foreignId('diputuskan_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        // bantuan dihapus dulu karena bergantung pada jenis_bantuan
        Schema::dropIfExists('bantuan');
        Schema::dropIfExists('jenis_bantuan');
    }
};
