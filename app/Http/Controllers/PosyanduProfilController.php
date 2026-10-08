<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use Illuminate\Http\Request;

class PosyanduProfilController extends Controller
{
    // tampilkan profil milik posyandu yang sedang login
    public function edit()
    {
        // ambil profil milik akun ini, kalau belum ada dibuatkan baris kosong
        $profil = Posyandu::firstOrCreate(['user_id' => auth()->id()]);

        // mode edit aktif kalau tombol Edit ditekan (?edit=1) atau profil belum lengkap
        $modeEdit = request()->boolean('edit') || !$profil->lengkap();

        return view('posyandu.profil', compact('profil', 'modeEdit'));
    }
    // simpan isian profil
    public function update(Request $request)
    {
        // semua isian wajib diisi, nomor HP harus angka 9-15 digit
        $data = $request->validate([
            'nama_kepala' => 'required|string|max:255',
            'no_hp' => 'required|digits_between:9,15',
            'kelurahan' => 'required|string|max:255',
            'wilayah' => 'required|string|max:255',
            'alamat' => 'required|string',
        ]);

        $profil = Posyandu::firstOrCreate(['user_id' => auth()->id()]);
        $profil->update($data);

        return redirect()->route('posyandu.profil.edit')
            ->with('sukses', 'Profil posyandu berhasil disimpan.');
    }
}
