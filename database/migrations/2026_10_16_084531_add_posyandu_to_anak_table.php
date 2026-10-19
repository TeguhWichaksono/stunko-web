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
        // posyandu pilihan untuk tiap anak (kosong sebelum diajukan)
        Schema::table('anak', function (Blueprint $table) {
            $table->foreignId('posyandu_id')->nullable()->after('orang_tua_id')
                ->constrained('posyandu')->nullOnDelete();
        });

        // anak baru mulai dari status 'belum' (belum diajukan), bukan 'menunggu'
        DB::statement("ALTER TABLE anak ALTER COLUMN status SET DEFAULT 'belum'");

        // pindahkan data lama: posyandu dan status orang tua disalin ke tiap anaknya
        foreach (DB::table('orang_tua')->get() as $o) {
            // orang tua yang belum pernah mengajukan: anaknya berstatus 'belum'
            $status = $o->status_pengajuan ?: 'belum';

            DB::table('anak')->where('orang_tua_id', $o->id)->update([
                'posyandu_id' => $o->posyandu_id,
                'status' => $status,
                'catatan' => $status === 'ditolak' ? $o->catatan_pengajuan : null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('anak', function (Blueprint $table) {
            $table->dropForeign(['posyandu_id']);
            $table->dropColumn('posyandu_id');
        });

        DB::statement("ALTER TABLE anak ALTER COLUMN status SET DEFAULT 'menunggu'");
    }
};
