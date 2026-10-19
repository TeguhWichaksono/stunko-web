<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;

class OrtuPosyanduController extends Controller
{
    // ambil biodata orang tua yang sedang login (kalau belum ada, dibuatkan baris kosong)
    private function ortuSaya()
    {
        return OrangTua::firstOrCreate(['user_id' => auth()->id()]);
    }

    // halaman Pilih Posyandu: daftar anak beserta posyandu, status, dan tombol ajukan per anak
    public function index()
    {
        $ortu = $this->ortuSaya();

        // semua anak milik orang tua ini, beserta posyandu pilihannya (kalau sudah ada)
        $daftar = $ortu->anak()->with('posyandu.user')->orderBy('nama')->get();

        return view('ortu.posyandu', compact('ortu', 'daftar'));
    }
}
