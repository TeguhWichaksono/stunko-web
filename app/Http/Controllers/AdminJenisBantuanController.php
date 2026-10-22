<?php

namespace App\Http\Controllers;

use App\Models\JenisBantuan;
use Illuminate\Http\Request;

class AdminJenisBantuanController extends Controller
{
    // aturan isian form jenis bantuan
    // $id diisi saat mengubah data, supaya nama milik sendiri tidak dianggap kembar
    private function aturan($id = null)
    {
        return [
            'nama' => 'required|string|max:255|unique:jenis_bantuan,nama' . ($id ? ',' . $id : ''),
            // rentang usia sasaran dalam bulan (0 sampai 60 bulan, sesuai batas balita)
            'usia_min' => 'required|integer|min:0|max:60',
            'usia_maks' => 'required|integer|min:0|max:60|gte:usia_min',
            'keterangan' => 'nullable|string|max:1000',
        ];
    }

    // pesan khusus untuk aturan rentang usia
    private function pesan()
    {
        return [
            'usia_maks.gte' => 'Usia maksimal tidak boleh lebih kecil dari usia minimal.',
        ];
    }

    // daftar jenis bantuan, diurutkan dari rentang usia terkecil
    public function index()
    {
        $daftar = JenisBantuan::orderBy('usia_min')->get();

        return view('admin.jenis_bantuan.index', compact('daftar'));
    }

    // tampilkan form tambah jenis bantuan
    public function create()
    {
        return view('admin.jenis_bantuan.form', ['jenis' => new JenisBantuan()]);
    }

    // simpan jenis bantuan baru
    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), $this->pesan());
        JenisBantuan::create($data);

        return redirect()->route('admin.jenis-bantuan.index')
            ->with('sukses', 'Jenis bantuan berhasil ditambahkan.');
    }

    // tampilkan form ubah jenis bantuan
    public function edit($id)
    {
        $jenis = JenisBantuan::findOrFail($id);

        return view('admin.jenis_bantuan.form', compact('jenis'));
    }

    // simpan perubahan jenis bantuan
    public function update(Request $request, $id)
    {
        $jenis = JenisBantuan::findOrFail($id);

        $data = $request->validate($this->aturan($jenis->id), $this->pesan());
        $jenis->update($data);

        return redirect()->route('admin.jenis-bantuan.index')
            ->with('sukses', 'Jenis bantuan berhasil diperbarui.');
    }
}
