<?php

namespace App\Http\Controllers;

use App\Exports\RiwayatPemeriksaanExport;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminRiwayatController extends Controller
{
    // ambil data riwayat sesuai filter (dipakai untuk tampilan dan untuk unduh Excel)
    private function ambilData(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|date_format:Y-m',
            'posyandu' => 'nullable|integer',
            'cari' => 'nullable|string|max:100',
        ]);

        $query = Pengukuran::with(['anak.orangTua', 'posyandu.user']);

        // saring berdasarkan bulan pemeriksaan (format 2026-10)
        if ($request->filled('bulan')) {
            $query->where('periode', $request->input('bulan'));
        }

        // saring berdasarkan posyandu yang melakukan pemeriksaan
        if ($request->filled('posyandu')) {
            $query->where('posyandu_id', $request->input('posyandu'));
        }

        // cari berdasarkan nama anak atau nama orang tua
        if ($request->filled('cari')) {
            $cari = $request->input('cari');
            $query->whereHas('anak', function ($a) use ($cari) {
                $a->where(function ($q) use ($cari) {
                    $q->where('nama', 'like', '%' . $cari . '%')
                      ->orWhereHas('orangTua', function ($o) use ($cari) {
                          $o->where('nama_lengkap', 'like', '%' . $cari . '%');
                      });
                });
            });
        }

        // yang terbaru di atas
        return $query->orderByDesc('tanggal_ukur')->orderBy('anak_id')->get();
    }

    // halaman riwayat pemeriksaan seluruh posyandu
    public function index(Request $request)
    {
        $semua = $this->ambilData($request);

        // tampilan dibatasi 500 baris supaya halaman tetap ringan, Excel memuat semuanya
        $total = $semua->count();
        $daftar = $semua->take(500);

        // daftar posyandu untuk pilihan penyaring
        $daftarPosyandu = Posyandu::with('user')->get()->sortBy(fn ($p) => $p->user->name);

        return view('admin.riwayat', [
            'daftar' => $daftar,
            'total' => $total,
            'daftarPosyandu' => $daftarPosyandu,
            'bulan' => $request->input('bulan'),
            'posyanduId' => $request->input('posyandu'),
            'cari' => $request->input('cari'),
        ]);
    }

    // unduh Excel sesuai filter yang sedang dipilih
    public function unduh(Request $request)
    {
        $data = $this->ambilData($request);

        $bulan = $request->input('bulan') ?: 'semua-bulan';
        $namaFile = 'riwayat-pemeriksaan-' . $bulan . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new RiwayatPemeriksaanExport($data), $namaFile);
    }
}
