<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\OrangTua;
use Illuminate\Http\Request;

class OrtuAnakController extends Controller
{
    // ambil biodata orang tua yang sedang login (kalau belum ada, dibuatkan baris kosong)
    private function ortuSaya()
    {
        return OrangTua::firstOrCreate(['user_id' => auth()->id()]);
    }

    // aturan isian form anak
    // $idAnak diisi saat mengubah data, supaya NIK anak itu sendiri tidak dianggap kembar
    private function aturan($idAnak = null)
    {
        $unikNik = 'unique:anak,nik' . ($idAnak ? ',' . $idAnak : '');

        return [
            'nama' => 'required|string|max:255',
            'nik' => 'required|digits:16|' . $unikNik,
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            // anak harus balita (di bawah 5 tahun) dan tidak boleh tanggal masa depan
            'tanggal_lahir' => 'required|date|before_or_equal:today|after_or_equal:'
                . now()->subYears(5)->format('Y-m-d'),
            // berat dan tinggi lahir boleh kosong
            'berat_lahir' => 'nullable|numeric|min:0.5|max:9',
            'tinggi_lahir' => 'nullable|numeric|min:20|max:70',
        ];
    }

    // pesan khusus untuk aturan batas usia
    private function pesan()
    {
        return [
            'tanggal_lahir.after_or_equal' => 'Anak harus berusia di bawah 5 tahun (balita).',
        ];
    }

    // daftar anak milik orang tua yang sedang login
    public function index()
    {
        $ortu = $this->ortuSaya();
        $daftar = $ortu->anak()->orderBy('nama')->get();

        return view('ortu.anak.index', compact('ortu', 'daftar'));
    }

    // tampilkan form tambah anak
    public function create()
    {
        // biodata orang tua harus lengkap dulu, kalau belum diarahkan ke halaman biodata
        if (!$this->ortuSaya()->lengkap()) {
            return redirect()->route('ortu.biodata.edit');
        }

        return view('ortu.anak.form', ['anak' => new Anak()]);
    }

    // simpan anak baru
    public function store(Request $request)
    {
        $ortu = $this->ortuSaya();

        if (!$ortu->lengkap()) {
            return redirect()->route('ortu.biodata.edit');
        }

        $data = $request->validate($this->aturan(), $this->pesan());

        // anak ini otomatis milik orang tua yang sedang login
        $data['orang_tua_id'] = $ortu->id;
        Anak::create($data);

        return redirect()->route('ortu.anak.index')
            ->with('sukses', 'Data anak berhasil ditambahkan.');
    }

    // tampilkan form ubah data anak
    public function edit($id)
    {
        // hanya anak milik sendiri yang bisa dibuka, kalau bukan akan muncul 404
        $anak = $this->ortuSaya()->anak()->findOrFail($id);

        return view('ortu.anak.form', compact('anak'));
    }

    // simpan perubahan data anak
    public function update(Request $request, $id)
    {
        $anak = $this->ortuSaya()->anak()->findOrFail($id);

        $data = $request->validate($this->aturan($anak->id), $this->pesan());
        $anak->update($data);

        return redirect()->route('ortu.anak.index')
            ->with('sukses', 'Data anak berhasil diperbarui.');
    }
}
