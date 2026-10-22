<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBantuan extends Model
{
    use HasFactory;

    // nama tabel kita 'jenis_bantuan' (bukan 'jenis_bantuans' seperti tebakan Laravel)
    protected $table = 'jenis_bantuan';

    // kolom yang boleh diisi dari form
    protected $fillable = [
        'nama',
        'usia_min',
        'usia_maks',
        'keterangan',
    ];

    // cek apakah usia anak (dalam bulan) masuk rentang sasaran jenis bantuan ini
    public function cocokUntuk(float $usiaBulan): bool
    {
        // usia dihitung dalam bulan penuh, misalnya 12,8 bulan dianggap 12 bulan
        $usia = floor($usiaBulan);

        return $usia >= $this->usia_min && $usia <= $this->usia_maks;
    }
}
