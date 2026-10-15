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
        Schema::table('pengukuran', function (Blueprint $table) {
            // cara ukur tinggi badan: terlentang atau berdiri (boleh kosong)
            $table->string('cara_ukur', 10)->nullable()->after('lila');

            // z-score WHO dari FastAPI (kosong untuk pemeriksaan lama yang dibuat sebelum fitur ini)
            $table->decimal('z_bb_u', 5, 2)->nullable()->after('cara_ukur');
            $table->decimal('z_tb_u', 5, 2)->nullable()->after('z_bb_u');
            $table->decimal('z_bb_tb', 5, 2)->nullable()->after('z_tb_u');
        });
    }

    public function down(): void
    {
        Schema::table('pengukuran', function (Blueprint $table) {
            $table->dropColumn(['cara_ukur', 'z_bb_u', 'z_tb_u', 'z_bb_tb']);
        });
    }
};
