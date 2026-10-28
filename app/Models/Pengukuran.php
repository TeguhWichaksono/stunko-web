<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengukuran extends Model
{
    use HasFactory;

    // nama tabel kita 'pengukuran' (bukan 'pengukurans' seperti tebakan Laravel)
    protected $table = 'pengukuran';

    // kolom yang boleh diisi
    // kolom yang boleh diisi
    protected $fillable = [
        'anak_id',
        'posyandu_id',
        'tanggal_ukur',
        'periode',
        'usia_bulan',
        'berat',
        'tinggi',
        'lila',
        'cara_ukur',
        'z_bb_u',
        'z_tb_u',
        'z_bb_tb',
        'status_stunting',
        'status_gizi',
        'skor',
        'keputusan',
        'hasil_api',
    ];

    // tanggal dibaca sebagai tanggal, hasil_api dibaca sebagai array (aslinya teks JSON)
    protected $casts = [
        'tanggal_ukur' => 'date',
        'hasil_api' => 'array',
    ];

    // pemeriksaan ini milik satu anak
    public function anak()
    {
        return $this->belongsTo(Anak::class);
    }

    // posyandu yang melakukan pemeriksaan
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }
    // usia anak saat diukur dalam bentuk teks, contoh: "4 Tahun - 8 Bulan - 24 Hari"
    public function getUsiaTeksAttribute()
    {
        $selisih = $this->anak->tanggal_lahir->diff($this->tanggal_ukur);

        return $selisih->y . ' Tahun - ' . $selisih->m . ' Bulan - ' . $selisih->d . ' Hari';
    }
}
