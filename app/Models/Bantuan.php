<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bantuan extends Model
{
    use HasFactory;

    // nama tabel kita 'bantuan' (bukan 'bantuans' seperti tebakan Laravel)
    protected $table = 'bantuan';

    // kolom yang boleh diisi
    protected $fillable = [
        'anak_id',
        'status',
        'dihubungi_pada',
        'catatan_hubungi',
        'tanggal_periksa_spesialis',
        'hasil_spesialis',
        'jenis_bantuan_id',
        'alasan_keputusan',
        'diputuskan_pada',
        'diputuskan_oleh',
    ];

    // tanggal dan waktu dibaca sebagai tanggal supaya mudah ditampilkan
    protected $casts = [
        'dihubungi_pada' => 'datetime',
        'tanggal_periksa_spesialis' => 'date',
        'diputuskan_pada' => 'datetime',
    ];

    // catatan ini milik satu anak
    public function anak()
    {
        return $this->belongsTo(Anak::class);
    }

    // jenis bantuan yang diberikan (kosong kalau belum diputuskan atau tidak berhak)
    public function jenisBantuan()
    {
        return $this->belongsTo(JenisBantuan::class, 'jenis_bantuan_id');
    }

    // akun admin yang memutuskan
    public function pemutus()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }
}
