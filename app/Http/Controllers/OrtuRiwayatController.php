<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use Illuminate\Http\Request;

class OrtuRiwayatController extends Controller
{
    // riwayat pemeriksaan anak milik orang tua yang sedang login (hanya lihat)
    public function index(Request $request)
    {
        $ortu = OrangTua::firstOrCreate(['user_id' => auth()->id()]);

        // hanya anak yang sudah pernah diperiksa
        $daftarAnak = $ortu->anak()->has('pengukuran')->orderBy('nama')->get();

        // anak yang dipilih lewat alamat (?anak=3), kalau tidak ada pakai anak pertama
        // hanya anak milik sendiri yang bisa dipilih
        $anak = $daftarAnak->firstWhere('id', (int) $request->input('anak')) ?? $daftarAnak->first();

        // belum ada anak yang pernah diperiksa
        if (!$anak) {
            return view('ortu.riwayat', [
                'daftarAnak' => $daftarAnak,
                'anak' => null,
            ]);
        }

        // riwayat dari yang lama ke yang baru (untuk grafik), dan sebaliknya (untuk tabel)
        $urutLama = $anak->pengukuran()->orderBy('tanggal_ukur')->get();
        $riwayat = $urutLama->sortByDesc('tanggal_ukur')->values();

        // pemeriksaan terakhir dan sebelumnya
        $terakhir = $riwayat->first();
        $sebelumnya = $riwayat->get(1);

        // selisih berat dan tinggi dengan pemeriksaan sebelumnya
        $selisih = null;
        if ($sebelumnya) {
            $selisih = [
                'berat' => round($terakhir->berat - $sebelumnya->berat, 2),
                'tinggi' => round($terakhir->tinggi - $sebelumnya->tinggi, 1),
            ];
        }

        // data untuk grafik (urut dari lama ke baru), z-score kosong diubah jadi null
        $grafik = [
            'label' => $urutLama->map(fn ($p) => $p->tanggal_ukur->format('d-m-Y'))->all(),
            'berat' => $urutLama->map(fn ($p) => (float) $p->berat)->all(),
            'tinggi' => $urutLama->map(fn ($p) => (float) $p->tinggi)->all(),
            'z_bb_u' => $urutLama->map(fn ($p) => $p->z_bb_u !== null ? (float) $p->z_bb_u : null)->all(),
            'z_tb_u' => $urutLama->map(fn ($p) => $p->z_tb_u !== null ? (float) $p->z_tb_u : null)->all(),
            'z_bb_tb' => $urutLama->map(fn ($p) => $p->z_bb_tb !== null ? (float) $p->z_bb_tb : null)->all(),
        ];

        return view('ortu.riwayat', compact(
            'daftarAnak',
            'anak',
            'riwayat',
            'terakhir',
            'selisih',
            'grafik'
        ));
    }
}
