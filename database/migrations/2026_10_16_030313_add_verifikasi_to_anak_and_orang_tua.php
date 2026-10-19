<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // status verifikasi tiap anak: menunggu, diterima, atau ditolak
        Schema::table('anak', function (Blueprint $table) {
            $table->string('status', 10)->default('menunggu')->after('tinggi_lahir');
            $table->text('catatan')->nullable()->after('status');
        });

        // catatan dari posyandu untuk orang tua (alasan penolakan)
        Schema::table('orang_tua', function (Blueprint $table) {
            $table->text('catatan_pengajuan')->nullable()->after('status_pengajuan');
        });

        // anak dari orang tua yang sudah diterima sebelum fitur ini dianggap sudah diterima
        $idDiterima = DB::table('orang_tua')->where('status_pengajuan', 'diterima')->pluck('id');
        DB::table('anak')->whereIn('orang_tua_id', $idDiterima)->update(['status' => 'diterima']);
    }

    public function down(): void
    {
        Schema::table('anak', function (Blueprint $table) {
            $table->dropColumn(['status', 'catatan']);
        });

        Schema::table('orang_tua', function (Blueprint $table) {
            $table->dropColumn('catatan_pengajuan');
        });
    }
};
