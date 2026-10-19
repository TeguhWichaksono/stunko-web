<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\OrangTua;
use App\Models\Posyandu;
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

    // daftar anak milik orang tua yang sedang login, beserta posyandu dan statusnya
    public function index()
    {
        $ortu = $this->ortuSaya();
        $daftar = $ortu->anak()->with('posyandu.user')->orderBy('nama')->get();

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

    // simpan anak baru (statusnya 'belum', belum diajukan ke posyandu mana pun)
    public function store(Request $request)
    {
        $ortu = $this->ortuSaya();

        if (!$ortu->lengkap()) {
            return redirect()->route('ortu.biodata.edit');
        }

        $data = $request->validate($this->aturan(), $this->pesan());

        // anak ini otomatis milik orang tua yang sedang login
        $data['orang_tua_id'] = $ortu->id;
        $data['status'] = 'belum';
        Anak::create($data);

        return redirect()->route('ortu.anak.index')
            ->with('sukses', 'Data anak berhasil ditambahkan. Klik Ajukan untuk memilih posyandu.');
    }

    // tampilkan form ubah data anak
    public function edit($id)
    {
        // hanya anak milik sendiri yang bisa dibuka, kalau bukan akan muncul 404
        $anak = $this->ortuSaya()->anak()->findOrFail($id);

        // data hanya boleh diubah kalau belum diajukan atau sudah ditolak
        if (!in_array($anak->status, ['belum', 'ditolak'])) {
            return redirect()->route('ortu.anak.index')
                ->with('galat', 'Data anak yang sedang diproses atau sudah diterima tidak bisa diubah.');
        }

        return view('ortu.anak.form', compact('anak'));
    }

    // simpan perubahan data anak
    public function update(Request $request, $id)
    {
        $anak = $this->ortuSaya()->anak()->findOrFail($id);

        if (!in_array($anak->status, ['belum', 'ditolak'])) {
            return redirect()->route('ortu.anak.index')
                ->with('galat', 'Data anak yang sedang diproses atau sudah diterima tidak bisa diubah.');
        }

        $data = $request->validate($this->aturan($anak->id), $this->pesan());
        $anak->update($data);

        return redirect()->route('ortu.anak.index')
            ->with('sukses', 'Data anak berhasil diperbarui.');
    }

    // halaman pilih posyandu untuk satu anak
    public function formAjukan($id)
    {
        $ortu = $this->ortuSaya();
        $anak = $ortu->anak()->findOrFail($id);

        // hanya anak yang belum diajukan atau ditolak yang boleh diajukan
        if (!in_array($anak->status, ['belum', 'ditolak'])) {
            return redirect()->route('ortu.posyandu.index')
                ->with('galat', 'Anak ini sudah diajukan atau sudah diterima.');
        }

        // biodata orang tua harus lengkap dulu
        if (!$ortu->lengkap()) {
            return redirect()->route('ortu.biodata.edit');
        }

        // daftar posyandu: hanya yang profilnya sudah lengkap
        $daftar = Posyandu::with('user')
            ->whereNotNull('nama_kepala')
            ->whereNotNull('no_hp')
            ->whereNotNull('kelurahan')
            ->whereNotNull('wilayah')
            ->whereNotNull('alamat')
            ->get()
            ->sortBy(fn($p) => $p->user->name);

        // pilihan awal: posyandu dari anak lain yang sudah diterima (bisa diganti)
        $pilihanAwal = $ortu->anak()
            ->where('status', 'diterima')
            ->whereNotNull('posyandu_id')
            ->value('posyandu_id');

        return view('ortu.anak.ajukan', compact('ortu', 'anak', 'daftar', 'pilihanAwal'));
    }

    // kirim pengajuan: simpan posyandu pilihan dan ubah status anak jadi 'menunggu'
    public function ajukan(Request $request, $id)
    {
        $ortu = $this->ortuSaya();
        $anak = $ortu->anak()->findOrFail($id);

        if (!in_array($anak->status, ['belum', 'ditolak'])) {
            return redirect()->route('ortu.posyandu.index')
                ->with('galat', 'Anak ini sudah diajukan atau sudah diterima.');
        }

        if (!$ortu->lengkap()) {
            return redirect()->route('ortu.biodata.edit');
        }

        $request->validate([
            'posyandu_id' => 'required|exists:posyandu,id',
        ], [
            'posyandu_id.required' => 'Pilih posyandu dulu.',
        ]);

        // posyandu yang profilnya belum lengkap tidak boleh dipilih
        $posyandu = Posyandu::findOrFail($request->posyandu_id);
        if (!$posyandu->lengkap()) {
            return back()->with('galat', 'Posyandu tersebut belum bisa dipilih.');
        }

        // simpan pilihan, status jadi menunggu, alasan penolakan sebelumnya dikosongkan
        $anak->update([
            'posyandu_id' => $posyandu->id,
            'status' => 'menunggu',
            'catatan' => null,
        ]);

        return redirect()->route('ortu.posyandu.index')
            ->with('sukses', 'Pengajuan untuk ' . $anak->nama . ' berhasil dikirim. Tunggu persetujuan dari posyandu.');
    }
}
