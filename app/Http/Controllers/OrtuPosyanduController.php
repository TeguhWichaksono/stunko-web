<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class OrtuPosyanduController extends Controller
{
    // ambil biodata orang tua yang sedang login (kalau belum ada, dibuatkan baris kosong)
    private function ortuSaya()
    {
        return OrangTua::firstOrCreate(['user_id' => auth()->id()]);
    }

    // syarat mengajukan: biodata lengkap dan minimal satu anak sudah terisi
    private function syaratTerpenuhi($ortu)
    {
        return $ortu->lengkap() && $ortu->anak()->count() > 0;
    }

    // halaman pilih posyandu dan status pengajuan
    public function index()
    {
        $ortu = $this->ortuSaya();
        $syarat = $this->syaratTerpenuhi($ortu);

        // daftar posyandu: hanya yang profilnya sudah lengkap
        $daftar = Posyandu::with('user')
            ->whereNotNull('nama_kepala')
            ->whereNotNull('no_hp')
            ->whereNotNull('kelurahan')
            ->whereNotNull('wilayah')
            ->whereNotNull('alamat')
            ->get()
            ->sortBy(fn($p) => $p->user->name);

        // muat posyandu yang sedang diajukan (kalau ada) beserta nama akunnya
        $ortu->load('posyandu.user');

        return view('ortu.posyandu', compact('ortu', 'syarat', 'daftar'));
    }

    // kirim pengajuan ke posyandu yang dipilih
    public function ajukan(Request $request)
    {
        $ortu = $this->ortuSaya();

        // syarat belum terpenuhi: kembalikan ke halaman pilih posyandu
        if (!$this->syaratTerpenuhi($ortu)) {
            return redirect()->route('ortu.posyandu.index');
        }

        // tidak boleh mengajukan lagi kalau masih menunggu atau sudah diterima
        if (in_array($ortu->status_pengajuan, ['menunggu', 'diterima'])) {
            return redirect()->route('ortu.posyandu.index')
                ->with('galat', 'Anda sudah memiliki pengajuan yang aktif.');
        }

        // posyandu yang dipilih harus ada di tabel
        $request->validate([
            'posyandu_id' => 'required|exists:posyandu,id',
        ]);

        // posyandu yang profilnya belum lengkap tidak boleh dipilih
        $posyandu = Posyandu::findOrFail($request->posyandu_id);
        if (!$posyandu->lengkap()) {
            return redirect()->route('ortu.posyandu.index')
                ->with('galat', 'Posyandu tersebut belum bisa dipilih.');
        }

        // simpan pilihan dan tandai statusnya Menunggu
        $ortu->update([
            'posyandu_id' => $posyandu->id,
            'status_pengajuan' => 'menunggu',
        ]);

        return redirect()->route('ortu.posyandu.index')
            ->with('sukses', 'Pengajuan berhasil dikirim. Tunggu persetujuan dari posyandu.');
    }
}
