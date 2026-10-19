<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    use HasFactory;

    // nama tabel kita 'orang_tua' (bukan 'orang_tuas' seperti tebakan Laravel)
    protected $table = 'orang_tua';

    // kolom yang boleh diisi dari form
    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nik',
        'no_hp',
        'pekerjaan',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'posyandu_id',
        'status_pengajuan',
        'catatan_pengajuan',
    ];

    // biodata ini milik satu akun user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // posyandu yang dipilih orang tua
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }

    // satu orang tua bisa punya banyak anak
    public function anak()
    {
        return $this->hasMany(Anak::class);
    }

    // cek apakah biodata orang tua sudah terisi semua (true = lengkap)
    public function lengkap(): bool
    {
        return $this->nama_lengkap && $this->nik && $this->no_hp && $this->pekerjaan
            && $this->alamat && $this->rt && $this->rw && $this->kelurahan;
    }
}
