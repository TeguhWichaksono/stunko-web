<?php

namespace App\Http\Controllers;

use App\Models\Pengukuran;
use App\Models\Posyandu;
use Illuminate\Http\Request;

class PosyanduRiwayatController extends Controller
{
    // riwayat pemeriksaan yang dilakukan posyandu ini, bisa disaring per bulan dan dicari per nama
    public function index(Request $request)
    {
        $posyandu = Posyandu::firstOrCreate(['user_id' => auth()->id()]);

        // profil belum lengkap: arahkan ke halaman profil dulu
        if (!$posyandu->lengkap()) {
            return redirect()->route('posyandu.profil.edit');
        }

        // bulan format 2026-10 (kosong = semua bulan), cari = kata yang diketik
        $request->validate([
            'bulan' => 'nullable|date_format:Y-m',
            'cari' => 'nullable|string|max:100',
        ]);
        $bulan = $request->input('bulan');
        $cari = $request->input('cari');

        // hanya pemeriksaan milik posyandu ini
        $query = Pengukuran::with('anak.orangTua')->where('posyandu_id', $posyandu->id);

        // saring berdasarkan bulan yang dipilih
        if ($bulan) {
            $query->where('periode', $bulan);
        }

        // cari berdasarkan nama anak atau nama orang tua
        if ($cari) {
            $query->whereHas('anak', function ($a) use ($cari) {
                $a->where('nama', 'like', '%' . $cari . '%')
                    ->orWhereHas('orangTua', function ($o) use ($cari) {
                        $o->where('nama_lengkap', 'like', '%' . $cari . '%');
                    });
            });
        }

        // yang terbaru di atas, dibatasi 300 baris supaya halaman tetap ringan
        $daftar = $query->orderByDesc('tanggal_ukur')->limit(300)->get();

        return view('posyandu.riwayat', compact('daftar', 'bulan', 'cari'));
    }
}
