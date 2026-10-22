<?php

namespace App\Http\Controllers;

use App\Models\Pengukuran;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class AdminBantuanController extends Controller
{
    // daftar calon penerima bantuan: pemeriksaan terbaru tiap anak, diurutkan dari skor tertinggi
    public function index(Request $request)
    {
        $request->validate([
            'cari' => 'nullable|string|max:100',
            'posyandu' => 'nullable|integer',
            'keputusan' => 'nullable|in:calon,bukan',
        ]);
        $cari = $request->input('cari');
        $posyanduId = $request->input('posyandu');
        $keputusan = $request->input('keputusan');

        // semua pemeriksaan beserta anak, orang tua, dan posyandunya
        $query = Pengukuran::with(['anak.orangTua', 'anak.posyandu.user']);

        // saring berdasarkan posyandu anak
        if ($posyanduId) {
            $query->whereHas('anak', function ($a) use ($posyanduId) {
                $a->where('posyandu_id', $posyanduId);
            });
        }

        // cari berdasarkan nama anak, NIK anak, atau nama orang tua
        if ($cari) {
            $query->whereHas('anak', function ($a) use ($cari) {
                $a->where(function ($q) use ($cari) {
                    $q->where('nama', 'like', '%' . $cari . '%')
                        ->orWhere('nik', 'like', '%' . $cari . '%')
                        ->orWhereHas('orangTua', function ($o) use ($cari) {
                            $o->where('nama_lengkap', 'like', '%' . $cari . '%');
                        });
                });
            });
        }

        // ambil pemeriksaan TERBARU tiap anak (anak yang diperiksa berkali-kali hanya muncul sekali)
        $terbaru = $query->orderByDesc('tanggal_ukur')
            ->get()
            ->groupBy('anak_id')
            ->map(fn($baris) => $baris->first());

        // ringkasan jumlah (dihitung sebelum disaring berdasarkan keputusan)
        $ringkasan = [
            'total' => $terbaru->count(),
            'calon' => $terbaru->where('keputusan', 'Calon Penerima Bantuan')->count(),
        ];
        $ringkasan['bukan'] = $ringkasan['total'] - $ringkasan['calon'];

        // saring berdasarkan keputusan yang dipilih
        if ($keputusan == 'calon') {
            $terbaru = $terbaru->filter(fn($p) => $p->keputusan == 'Calon Penerima Bantuan');
        } elseif ($keputusan == 'bukan') {
            $terbaru = $terbaru->filter(fn($p) => $p->keputusan != 'Calon Penerima Bantuan');
        }

        // urutkan dari skor tertinggi
        $daftar = $terbaru->sortByDesc(fn($p) => (float) $p->skor)->values();

        // daftar posyandu untuk pilihan penyaring
        $daftarPosyandu = Posyandu::with('user')->get()->sortBy(fn($p) => $p->user->name);

        return view('admin.bantuan', compact(
            'daftar',
            'ringkasan',
            'daftarPosyandu',
            'cari',
            'posyanduId',
            'keputusan'
        ));
    }
}
