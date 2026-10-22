<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\Posyandu;

class PosyanduDashboardController extends Controller
{
    // dashboard posyandu: semua angka dihitung di sini, lalu dikirim ke halamannya
    public function index()
    {
        $posyandu = Posyandu::firstOrCreate(['user_id' => auth()->id()]);

        // profil belum lengkap: arahkan ke halaman profil dulu
        if (!$posyandu->lengkap()) {
            return redirect()->route('posyandu.profil.edit');
        }

        // bulan ini, format 2026-10
        $periodeIni = now()->format('Y-m');

        // semua anak yang sudah diterima di posyandu ini
        $anak = Anak::with('orangTua')
            ->where('posyandu_id', $posyandu->id)
            ->where('status', 'diterima')
            ->orderBy('nama')
            ->get();
        $anakIds = $anak->pluck('id');

        // pemeriksaan TERBARU tiap anak (supaya anak yang diperiksa berkali-kali tidak terhitung berkali-kali)
        $terbaru = Pengukuran::with('anak.orangTua')
            ->whereIn('anak_id', $anakIds)
            ->orderByDesc('tanggal_ukur')
            ->get()
            ->groupBy('anak_id')
            ->map(fn ($baris) => $baris->first());

        // anak yang sudah diperiksa bulan ini
        $idDiperiksa = Pengukuran::whereIn('anak_id', $anakIds)
            ->where('periode', $periodeIni)
            ->pluck('anak_id')
            ->unique();

        // angka untuk kartu ringkasan
        $jumlahAnak = $anak->count();
        $sudahBulanIni = $idDiperiksa->count();
        $persenBulanIni = $jumlahAnak > 0 ? round($sudahBulanIni / $jumlahAnak * 100) : 0;

        // jumlah anak yang menunggu persetujuan di posyandu ini
        $pengajuanMenunggu = Anak::where('posyandu_id', $posyandu->id)
            ->where('status', 'menunggu')
            ->count();

        // sebaran status stunting dan status gizi (berdasarkan pemeriksaan terbaru) untuk grafik donat
        $donatStunting = [
            'Stunting' => $terbaru->where('status_stunting', 'Stunting')->count(),
            'Berisiko' => $terbaru->where('status_stunting', 'Berisiko')->count(),
            'Normal' => $terbaru->where('status_stunting', 'Normal')->count(),
        ];
        $donatGizi = [
            'Gizi Kurang' => $terbaru->where('status_gizi', 'Gizi Kurang')->count(),
            'Gizi Lebih' => $terbaru->where('status_gizi', 'Gizi Lebih')->count(),
            'Normal' => $terbaru->where('status_gizi', 'Normal')->count(),
        ];

        // tabel: anak yang belum diperiksa bulan ini (maksimal 10)
        $belumDiperiksa = $anak->whereNotIn('id', $idDiperiksa->all())->take(10);

        // tabel: anak yang pemeriksaan terakhirnya Stunting atau Gizi Kurang (maksimal 10)
        $perluPerhatian = $terbaru
            ->filter(fn ($p) => $p->status_stunting == 'Stunting' || $p->status_gizi == 'Gizi Kurang')
            ->sortByDesc('tanggal_ukur')
            ->take(10);

        // tren 6 bulan terakhir: jumlah anak per status stunting tiap bulan
        $daftarBulan = [];
        for ($i = 5; $i >= 0; $i--) {
            $daftarBulan[] = now()->startOfMonth()->subMonths($i);
        }
        $periodeTren = array_map(fn ($b) => $b->format('Y-m'), $daftarBulan);

        // semua pemeriksaan 6 bulan terakhir, dikelompokkan per bulan
        $perBulan = Pengukuran::whereIn('anak_id', $anakIds)
            ->whereIn('periode', $periodeTren)
            ->get()
            ->groupBy('periode');

        $tren = ['label' => [], 'stunting' => [], 'berisiko' => [], 'normal' => []];
        foreach ($daftarBulan as $b) {
            $baris = $perBulan->get($b->format('Y-m'), collect());
            $tren['label'][] = $b->copy()->locale('id')->translatedFormat('M Y');
            $tren['stunting'][] = $baris->where('status_stunting', 'Stunting')->count();
            $tren['berisiko'][] = $baris->where('status_stunting', 'Berisiko')->count();
            $tren['normal'][] = $baris->where('status_stunting', 'Normal')->count();
        }

        return view('posyandu.dashboard', compact(
            'jumlahAnak',
            'sudahBulanIni',
            'persenBulanIni',
            'pengajuanMenunggu',
            'donatStunting',
            'donatGizi',
            'belumDiperiksa',
            'perluPerhatian',
            'tren'
        ));
    }
}
