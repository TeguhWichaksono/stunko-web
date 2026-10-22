<?php

namespace Database\Seeders;

use App\Models\JenisBantuan;
use Illuminate\Database\Seeder;

class JenisBantuanSeeder extends Seeder
{
    // mengisi dua jenis bantuan sesuai tulisan di kemasan
    // updateOrCreate: kalau dijalankan dua kali, datanya diperbarui (tidak dobel)
    public function run(): void
    {
        JenisBantuan::updateOrCreate(
            ['nama' => 'SGM Ananda Gain 100'],
            [
                'usia_min' => 0,
                'usia_maks' => 12,
                'keterangan' => 'Pangan olahan untuk keperluan medis khusus (400 g). '
                    . 'Untuk dukungan nutrisi anak berisiko gagal tumbuh, gizi kurang, atau gizi buruk. '
                    . 'Harus dengan resep dokter.',
            ]
        );

        JenisBantuan::updateOrCreate(
            ['nama' => 'SGM Eksplor Gain Optigrow 1+'],
            [
                'usia_min' => 13,
                'usia_maks' => 60,
                'keterangan' => 'Pangan olahan untuk keperluan medis khusus, varian Vanila (400 g), untuk anak di atas 1 tahun. '
                    . 'Untuk dukungan nutrisi anak berisiko gagal tumbuh, gizi kurang, atau gizi buruk. '
                    . 'Harus dengan resep dokter.',
            ]
        );
    }
}
