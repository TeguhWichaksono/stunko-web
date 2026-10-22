<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class PosyanduPengajuanController extends Controller
{
    // ambil profil posyandu yang sedang login (kalau belum ada, dibuatkan baris kosong)
    private function posyanduSaya()
    {
        return Posyandu::firstOrCreate(['user_id' => auth()->id()]);
    }

    // ambil satu anak yang diajukan ke posyandu ini, selain itu otomatis 404
    private function anakDiajukan($id)
    {
        return Anak::where('posyandu_id', $this->posyanduSaya()->id)
            ->where('status', '!=', 'belum')
            ->findOrFail($id);
    }

    // daftar anak yang diajukan ke posyandu ini, bisa dicari dan disaring per status
    public function index(Request $request)
    {
        $posyandu = $this->posyanduSaya();

        // profil belum lengkap: arahkan ke halaman profil dulu
        if (!$posyandu->lengkap()) {
            return redirect()->route('posyandu.profil.edit');
        }

        $request->validate([
            'cari' => 'nullable|string|max:100',
            'status' => 'nullable|in:menunggu,diterima,ditolak',
        ]);
        $cari = $request->input('cari');
        $status = $request->input('status');

        // semua anak yang diajukan ke posyandu ini (yang belum diajukan tidak ikut)
        $query = Anak::with('orangTua')
            ->where('posyandu_id', $posyandu->id)
            ->where('status', '!=', 'belum');

        // saring berdasarkan status yang dipilih
        if ($status) {
            $query->where('status', $status);
        }

        // cari berdasarkan nama anak, NIK anak, atau nama orang tua
        if ($cari) {
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', '%' . $cari . '%')
                    ->orWhere('nik', 'like', '%' . $cari . '%')
                    ->orWhereHas('orangTua', function ($o) use ($cari) {
                        $o->where('nama_lengkap', 'like', '%' . $cari . '%');
                    });
            });
        }

        // menunggu paling atas, lalu diterima, lalu ditolak
        $daftar = $query->orderByRaw("FIELD(status, 'menunggu', 'diterima', 'ditolak')")
            ->orderBy('nama')
            ->get();

        // jumlah anak per status (untuk ditampilkan di atas daftar)
        $hitung = Anak::where('posyandu_id', $posyandu->id)
            ->where('status', '!=', 'belum')
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('posyandu.pengajuan', compact('daftar', 'cari', 'status', 'hitung'));
    }

    // halaman detail satu anak: biodata anak, biodata orang tua, dan keputusan
    public function detail($id)
    {
        $anak = $this->anakDiajukan($id);
        $anak->load('orangTua.user');

        // anak lain milik orang tua yang sama, sebagai bahan pertimbangan
        $saudara = $anak->orangTua->anak()
            ->where('id', '!=', $anak->id)
            ->with('posyandu.user')
            ->orderBy('nama')
            ->get();

        return view('posyandu.pengajuan_detail', compact('anak', 'saudara'));
    }

    // terima satu anak
    public function terima($id)
    {
        $anak = $this->anakDiajukan($id);

        // hanya yang masih menunggu yang bisa diputuskan
        if ($anak->status !== 'menunggu') {
            return redirect()->route('posyandu.pengajuan.index')
                ->with('galat', 'Pengajuan ini sudah diproses.');
        }

        $anak->update(['status' => 'diterima', 'catatan' => null]);

        return redirect()->route('posyandu.pengajuan.index')
            ->with('sukses', $anak->nama . ' diterima.');
    }

    // tolak satu anak, alasan wajib diisi supaya orang tua tahu apa yang perlu diperbaiki
    public function tolak(Request $request, $id)
    {
        $anak = $this->anakDiajukan($id);

        if ($anak->status !== 'menunggu') {
            return redirect()->route('posyandu.pengajuan.index')
                ->with('galat', 'Pengajuan ini sudah diproses.');
        }

        $request->validate([
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Isi alasan penolakan supaya orang tua tahu apa yang perlu diperbaiki.',
        ]);

        $anak->update(['status' => 'ditolak', 'catatan' => $request->catatan]);

        return redirect()->route('posyandu.pengajuan.index')
            ->with('sukses', $anak->nama . ' ditolak.');
    }
}
