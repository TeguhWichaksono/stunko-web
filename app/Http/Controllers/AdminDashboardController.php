<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;

class AdminDashboardController extends Controller
{
    // dashboard admin (puskesmas): semua angka dihitung di sini, lalu dikirim ke halamannya
    public function index()
    {
        // bulan ini, format 2026-10
        $periodeIni = now()->format('Y-m');

        // skor minimal untuk dianggap perlu ditindaklanjuti
        // (sama dengan nilai awal di halaman Calon Penerima Bantuan)
        $skorMin = 50;

        // anak yang sudah diterima di posyandu mana pun
        $jumlahAnak = Anak::where('status', 'diterima')->count();

        // posyandu: jumlah akun, dan yang profilnya belum lengkap atau belum pernah login
        $akunPosyandu = User::where('role', 'posyandu')->orderBy('name')->get();
        $profil = Posyandu::all()->keyBy('user_id');
        $belumLengkap = $akunPosyandu->filter(function ($u) use ($profil) {
            return !isset($profil[$u->id]) || !$profil[$u->id]->lengkap();
        });
        $jumlahPosyandu = $akunPosyandu->count();

        // pemeriksaan TERBARU tiap anak (supaya anak yang diperiksa berkali-kali tidak terhitung berkali-kali)
        $terbaru = Pengukuran::with(['anak.orangTua', 'anak.posyandu.user', 'anak.bantuan'])
            ->orderByDesc('tanggal_ukur')
            ->get()
            ->groupBy('anak_id')
            ->map(fn($baris) => $baris->first());

        // jumlah anak yang sudah diperiksa bulan ini dan persentasenya
        $sudahBulanIni = Pengukuran::where('periode', $periodeIni)->distinct()->count('anak_id');
        $persenBulanIni = $jumlahAnak > 0 ? min(100, round($sudahBulanIni / $jumlahAnak * 100)) : 0;

        // status tindak lanjut bantuan tiap anak (kalau belum ada catatan, berarti 'baru')
        $statusDari = fn($p) => $p->anak->bantuan->status ?? 'baru';

        // anak yang skornya mencapai minimal dan belum ditindaklanjuti, diurutkan dari skor tertinggi
        $perluTindak = $terbaru
            ->filter(fn($p) => $statusDari($p) == 'baru' && (float) $p->skor >= $skorMin)
            ->sortByDesc(fn($p) => (float) $p->skor)
            ->values();
        $jumlahPerluTindak = $perluTindak->count();
        $prioritas = $perluTindak->take(5);

        // jumlah anak yang sedang diproses dan yang berhak bantuan
        $jumlahDihubungi = $terbaru->filter(fn($p) => $statusDari($p) == 'dihubungi')->count();
        $jumlahBerhak = $terbaru->filter(fn($p) => $statusDari($p) == 'berhak')->count();

        // jumlah anak stunting dan gizi kurang berdasarkan pemeriksaan terbaru
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

        // tren 6 bulan terakhir: jumlah pemeriksaan per status stunting tiap bulan
        $daftarBulan = [];
        for ($i = 5; $i >= 0; $i--) {
            $daftarBulan[] = now()->startOfMonth()->subMonths($i);
        }
        $periodeTren = array_map(fn($b) => $b->format('Y-m'), $daftarBulan);
        $perBulan = Pengukuran::whereIn('periode', $periodeTren)->get()->groupBy('periode');

        $tren = ['label' => [], 'stunting' => [], 'berisiko' => [], 'normal' => []];
        foreach ($daftarBulan as $b) {
            $baris = $perBulan->get($b->format('Y-m'), collect());
            $tren['label'][] = $b->copy()->locale('id')->translatedFormat('M Y');
            $tren['stunting'][] = $baris->where('status_stunting', 'Stunting')->count();
            $tren['berisiko'][] = $baris->where('status_stunting', 'Berisiko')->count();
            $tren['normal'][] = $baris->where('status_stunting', 'Normal')->count();
        }

        // perbandingan antar posyandu: jumlah anak per status stunting (pemeriksaan terbaru)
        $perPosyandu = [];
        foreach ($terbaru->groupBy(fn($p) => $p->anak->posyandu_id) as $baris) {
            $perPosyandu[] = [
                'nama' => $baris->first()->anak->posyandu?->user?->name ?? 'Tanpa posyandu',
                'stunting' => $baris->where('status_stunting', 'Stunting')->count(),
                'berisiko' => $baris->where('status_stunting', 'Berisiko')->count(),
                'normal' => $baris->where('status_stunting', 'Normal')->count(),
            ];
        }
        // posyandu dengan anak stunting terbanyak di atas
        $perPosyandu = collect($perPosyandu)->sortByDesc('stunting')->values();
        $grafikPosyandu = [
            'label' => $perPosyandu->pluck('nama')->all(),
            'stunting' => $perPosyandu->pluck('stunting')->all(),
            'berisiko' => $perPosyandu->pluck('berisiko')->all(),
            'normal' => $perPosyandu->pluck('normal')->all(),
        ];

        return view('admin.dashboard', compact(
            'skorMin',
            'jumlahPosyandu',
            'belumLengkap',
            'jumlahAnak',
            'sudahBulanIni',
            'persenBulanIni',
            'jumlahPerluTindak',
            'prioritas',
            'jumlahDihubungi',
            'jumlahBerhak',
            'donatStunting',
            'donatGizi',
            'tren',
            'grafikPosyandu'
        ));
    }
}
