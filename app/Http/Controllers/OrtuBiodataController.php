<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use Illuminate\Http\Request;

class OrtuBiodataController extends Controller
{
    // tampilkan biodata milik orang tua yang sedang login
    public function edit()
    {
        // ambil biodata milik akun ini, kalau belum ada dibuatkan baris kosong
        $ortu = OrangTua::firstOrCreate(['user_id' => auth()->id()]);

        // mode edit aktif kalau tombol Edit ditekan (?edit=1) atau biodata belum lengkap
        $modeEdit = request()->boolean('edit') || !$ortu->lengkap();

        return view('ortu.biodata', compact('ortu', 'modeEdit'));
    }

    // simpan isian biodata
    public function update(Request $request)
    {
        $ortu = OrangTua::firstOrCreate(['user_id' => auth()->id()]);

        // semua isian wajib diisi
        // NIK harus 16 digit dan tidak boleh sama dengan NIK orang tua lain
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'required|digits:16|unique:orang_tua,nik,' . $ortu->id,
            'no_hp' => 'required|digits_between:9,15',
            'pekerjaan' => 'required|string|max:255',
            'alamat' => 'required|string',
            'rt' => 'required|digits_between:1,3',
            'rw' => 'required|digits_between:1,3',
            'kelurahan' => 'required|string|max:255',
        ]);

        $ortu->update($data);

        return redirect()->route('ortu.biodata.edit')
            ->with('sukses', 'Biodata berhasil disimpan.');
    }
}
