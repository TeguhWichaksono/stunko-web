<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\Posyandu;

class PosyanduPengajuanController extends Controller
{
    // ambil profil posyandu yang sedang login (kalau belum ada, dibuatkan baris kosong)
    private function posyanduSaya()
    {
        return Posyandu::firstOrCreate(['user_id' => auth()->id()]);
    }

    // daftar pengajuan yang masuk ke posyandu ini
    public function index()
    {
        $posyandu = $this->posyanduSaya();

        // profil belum lengkap: arahkan ke halaman profil dulu
        if (!$posyandu->lengkap()) {
            return redirect()->route('posyandu.profil.edit');
        }

        // semua pengajuan untuk posyandu ini: menunggu paling atas, lalu diterima, lalu ditolak
        $daftar = OrangTua::withCount('anak')
            ->where('posyandu_id', $posyandu->id)
            ->orderByRaw("FIELD(status_pengajuan, 'menunggu', 'diterima', 'ditolak')")
            ->orderBy('nama_lengkap')
            ->get();

        return view('posyandu.pengajuan', compact('daftar'));
    }

    // terima pengajuan
    public function terima($id)
    {
        return $this->ubahStatus($id, 'diterima', 'Pengajuan diterima.');
    }

    // tolak pengajuan
    public function tolak($id)
    {
        return $this->ubahStatus($id, 'ditolak', 'Pengajuan ditolak.');
    }

    // ubah status pengajuan (dipakai oleh terima dan tolak)
    private function ubahStatus($id, $status, $pesan)
    {
        $posyandu = $this->posyanduSaya();

        // hanya pengajuan yang masuk ke posyandu ini yang bisa diproses, selain itu 404
        $ortu = OrangTua::where('posyandu_id', $posyandu->id)->findOrFail($id);

        // hanya pengajuan yang masih menunggu yang bisa diproses
        if ($ortu->status_pengajuan !== 'menunggu') {
            return redirect()->route('posyandu.pengajuan.index')
                ->with('galat', 'Pengajuan ini sudah diproses.');
        }

        $ortu->update(['status_pengajuan' => $status]);

        return redirect()->route('posyandu.pengajuan.index')
            ->with('sukses', $pesan);
    }
}
