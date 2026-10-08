<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posyandu extends Model
{
    use HasFactory;

    // nama tabel kita 'posyandu' (bukan 'posyandus' seperti tebakan Laravel)
    protected $table = 'posyandu';

    // kolom yang boleh diisi dari form
    protected $fillable = [
        'user_id',
        'nama_kepala',
        'no_hp',
        'kelurahan',
        'wilayah',
        'alamat',
    ];

    // profil ini milik satu akun user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // cek apakah semua isian profil sudah terisi (true = lengkap)
    public function lengkap(): bool
    {
        return $this->nama_kepala && $this->no_hp && $this->kelurahan
            && $this->wilayah && $this->alamat;
    }
}
