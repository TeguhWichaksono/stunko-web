<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anak extends Model
{
    use HasFactory;

    // nama tabel kita 'anak' (bukan 'anaks' seperti tebakan Laravel)
    protected $table = 'anak';

    // kolom yang boleh diisi dari form
    protected $fillable = [
        'orang_tua_id',
        'posyandu_id',
        'nama',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'berat_lahir',
        'tinggi_lahir',
        'status',
        'catatan',
    ];

    // tanggal lahir dibaca sebagai tanggal, jadi mudah ditampilkan (d-m-Y)
    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    // anak ini milik satu orang tua
    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class);
    }

    // posyandu yang dipilih untuk anak ini (kosong kalau belum diajukan)
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }

    // satu anak punya banyak pemeriksaan (satu per bulan)
    public function pengukuran()
    {
        return $this->hasMany(Pengukuran::class);
    }
}
