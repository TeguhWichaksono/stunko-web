<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\Posyandu;
use Illuminate\Http\Request;

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

        // anak_count = jumlah anak, anak_menunggu = jumlah anak yang belum diverifikasi
        $daftar = OrangTua::withCount([
                'anak',
                'anak as anak_menunggu' => function ($q) {
                    $q->where('status', 'menunggu');
                },
            ])
            ->where('posyandu_id', $posyandu->id)
            ->orderByRaw("FIELD(status_pengajuan, 'menunggu', 'diterima', 'ditolak')")
            ->orderBy('nama_lengkap')
            ->get();

        return view('posyandu.pengajuan', compact('daftar'));
    }

    // halaman detail: biodata orang tua dan semua anaknya, dicek dulu sebelum diputuskan
    public function detail($id)
    {
        $posyandu = $this->posyanduSaya();

        // hanya pengajuan yang masuk ke posyandu ini, selain itu 404
        $ortu = OrangTua::with(['user', 'anak' => function ($q) {
                $q->orderBy('nama');
            }])
            ->where('posyandu_id', $posyandu->id)
            ->findOrFail($id);

        return view('posyandu.pengajuan_detail', compact('ortu'));
    }

    // simpan keputusan: terima (per anak) atau tolak seluruh pengajuan
    public function proses(Request $request, $id)
    {
        $posyandu = $this->posyanduSaya();

        $ortu = OrangTua::with('anak')
            ->where('posyandu_id', $posyandu->id)
            ->findOrFail($id);

        $request->validate([
            'aksi' => 'required|in:terima,tolak',
            'catatan' => 'nullable|string|max:500',
            'keputusan' => 'nullable|array',
            'keputusan.*' => 'in:terima,tolak',
        ]);

        $aksi = $request->input('aksi');
        $catatan = $request->input('catatan');

        // ---- tolak seluruh pengajuan ----
        if ($aksi === 'tolak') {
            // hanya pengajuan yang masih menunggu yang bisa ditolak
            if ($ortu->status_pengajuan !== 'menunggu') {
                return back()->with('galat', 'Pengajuan ini sudah diproses.');
            }
            // alasan wajib diisi supaya orang tua tahu apa yang perlu diperbaiki
            if (!$request->filled('catatan')) {
                return back()->withInput()->withErrors([
                    'catatan' => 'Isi alasan penolakan supaya orang tua tahu apa yang perlu diperbaiki.',
                ]);
            }

            $ortu->update(['status_pengajuan' => 'ditolak', 'catatan_pengajuan' => $catatan]);

            return redirect()->route('posyandu.pengajuan.index')
                ->with('sukses', 'Pengajuan ditolak.');
        }

        // ---- terima: putuskan tiap anak yang masih menunggu ----
        if (!in_array($ortu->status_pengajuan, ['menunggu', 'diterima'])) {
            return back()->with('galat', 'Pengajuan ini tidak bisa diproses.');
        }

        $anakMenunggu = $ortu->anak->where('status', 'menunggu');
        if ($anakMenunggu->isEmpty()) {
            return back()->with('galat', 'Tidak ada anak yang perlu diverifikasi.');
        }

        // kelompokkan anak berdasarkan pilihan posyandu
        $keputusan = $request->input('keputusan', []);
        $terimaId = [];
        $tolakId = [];
        foreach ($anakMenunggu as $a) {
            $pilih = $keputusan[$a->id] ?? null;
            if ($pilih === 'terima') {
                $terimaId[] = $a->id;
            }
            if ($pilih === 'tolak') {
                $tolakId[] = $a->id;
            }
        }

        // semua anak yang menunggu harus sudah dipilih
        if (count($terimaId) + count($tolakId) < $anakMenunggu->count()) {
            return back()->withInput()->with('galat', 'Pilih Terima atau Tolak untuk setiap anak.');
        }

        // anak yang ditolak wajib ada alasannya
        if ($tolakId && !$request->filled('catatan')) {
            return back()->withInput()->withErrors([
                'catatan' => 'Isi alasan karena ada anak yang ditolak.',
            ]);
        }

        // minimal satu anak diterima (sekarang atau sebelumnya)
        $sudahDiterima = $ortu->anak->where('status', 'diterima')->count();
        if (!$terimaId && $sudahDiterima == 0) {
            return back()->withInput()->with('galat',
                'Minimal satu anak harus diterima. Kalau semua anak ditolak, gunakan tombol Tolak Pengajuan.');
        }

        // simpan keputusan tiap anak
        foreach ($anakMenunggu as $a) {
            if (in_array($a->id, $terimaId)) {
                $a->update(['status' => 'diterima', 'catatan' => null]);
            } else {
                $a->update(['status' => 'ditolak', 'catatan' => $catatan]);
            }
        }

        // pengajuan keluarga dinyatakan diterima
        $ortu->update(['status_pengajuan' => 'diterima', 'catatan_pengajuan' => null]);

        return redirect()->route('posyandu.pengajuan.index')
            ->with('sukses', 'Keputusan tersimpan: ' . count($terimaId) . ' anak diterima, '
                . count($tolakId) . ' anak ditolak.');
    }
}
