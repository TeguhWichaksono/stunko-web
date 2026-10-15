<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Services\PrediksiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PosyanduPemeriksaanController extends Controller
{
    // ambil profil posyandu yang sedang login (kalau belum ada, dibuatkan baris kosong)
    private function posyanduSaya()
    {
        return Posyandu::firstOrCreate(['user_id' => auth()->id()]);
    }

    // ambil satu anak, tapi hanya kalau orang tuanya diterima di posyandu ini
    // kalau bukan, otomatis muncul 404
    private function anakSaya($id)
    {
        $posyandu = $this->posyanduSaya();

        return Anak::whereHas('orangTua', function ($q) use ($posyandu) {
            $q->where('posyandu_id', $posyandu->id)
                ->where('status_pengajuan', 'diterima');
        })->findOrFail($id);
    }

    // daftar anak yang siap diperiksa
    public function index()
    {
        $posyandu = $this->posyanduSaya();

        // profil belum lengkap: arahkan ke halaman profil dulu
        if (!$posyandu->lengkap()) {
            return redirect()->route('posyandu.profil.edit');
        }

        $periodeIni = now()->format('Y-m');

        $daftar = Anak::with('orangTua')
            ->whereHas('orangTua', function ($q) use ($posyandu) {
                $q->where('posyandu_id', $posyandu->id)
                    ->where('status_pengajuan', 'diterima');
            })
            // bulan_ini = jumlah pemeriksaan bulan ini (0 atau 1)
            ->withCount(['pengukuran as bulan_ini' => function ($q) use ($periodeIni) {
                $q->where('periode', $periodeIni);
            }])
            // terakhir = tanggal pemeriksaan terakhir
            ->withMax('pengukuran as terakhir', 'tanggal_ukur')
            ->orderBy('nama')
            ->get();

        return view('posyandu.pemeriksaan.index', compact('daftar'));
    }

    // tampilkan form pemeriksaan untuk satu anak
    public function create($id)
    {
        $anak = $this->anakSaya($id);

        return view('posyandu.pemeriksaan.form', compact('anak'));
    }
    // simpan pemeriksaan: hitung usia, kirim ke FastAPI, simpan hasilnya
    public function store(Request $request, $id)
    {
        $anak = $this->anakSaya($id);
        $posyandu = $this->posyanduSaya();

        // batas angka disamakan dengan batas di FastAPI
        $data = $request->validate([
            'tanggal_ukur' => 'required|date|before_or_equal:today|after_or_equal:'
                . $anak->tanggal_lahir->format('Y-m-d'),
            'berat' => 'required|numeric|min:1|max:40',
            'tinggi' => 'required|numeric|min:40|max:130',
            'lila' => 'nullable|numeric|min:5|max:30',
            // cara ukur wajib diisi karena memengaruhi hitungan z-score
            'cara_ukur' => 'required|in:terlentang,berdiri',
        ], [
            'tanggal_ukur.after_or_equal' => 'Tanggal ukur tidak boleh sebelum tanggal lahir anak.',
            'cara_ukur.required' => 'Pilih cara ukur (terlentang atau berdiri).',
        ]);

        // hitung usia anak (dalam bulan) saat diukur: bulan penuh + sisa hari dibagi 30
        $lahir = $anak->tanggal_lahir;
        $ukur = Carbon::parse($data['tanggal_ukur']);
        $bulan = $lahir->diffInMonths($ukur);
        $sisaHari = $lahir->copy()->addMonths($bulan)->diffInDays($ukur);
        $usia = round($bulan + $sisaHari / 30, 2);

        // model hanya dilatih untuk balita (sampai sekitar 60 bulan)
        if ($usia > 60.9) {
            return back()->withInput()->withErrors([
                'tanggal_ukur' => 'Usia anak sudah melewati batas balita (5 tahun).',
            ]);
        }

        // satu anak hanya boleh satu pemeriksaan per bulan
        $periode = $ukur->format('Y-m');
        $sudahAda = Pengukuran::where('anak_id', $anak->id)->where('periode', $periode)->exists();
        if ($sudahAda) {
            return back()->withInput()->withErrors([
                'tanggal_ukur' => 'Anak ini sudah diperiksa pada bulan tersebut.',
            ]);
        }

        // kirim ke FastAPI, kalau gagal tampilkan pesan dan jangan simpan apa pun
        try {
            $hasil = app(PrediksiService::class)->prediksi(
                $anak->jenis_kelamin,
                $usia,
                (float) $data['berat'],
                (float) $data['tinggi'],
                isset($data['lila']) ? (float) $data['lila'] : null,
                $data['cara_ukur'] ?? null
            );
        } catch (Exception $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        // simpan pemeriksaan beserta hasil prediksi dan z-score
        // tanda ?? null supaya tidak error kalau FastAPI belum mengirim z-score
        Pengukuran::create([
            'anak_id' => $anak->id,
            'posyandu_id' => $posyandu->id,
            'tanggal_ukur' => $data['tanggal_ukur'],
            'periode' => $periode,
            'usia_bulan' => $usia,
            'berat' => $data['berat'],
            'tinggi' => $data['tinggi'],
            'lila' => $data['lila'] ?? null,
            'cara_ukur' => $data['cara_ukur'] ?? null,
            'z_bb_u' => $hasil['zscore']['bb_u'] ?? null,
            'z_tb_u' => $hasil['zscore']['tb_u'] ?? null,
            'z_bb_tb' => $hasil['zscore']['bb_tb'] ?? null,
            'status_stunting' => $hasil['status_stunting'],
            'status_gizi' => $hasil['status_gizi'],
            'skor' => $hasil['skor'],
            'keputusan' => $hasil['keputusan'],
            'hasil_api' => $hasil,
        ]);

        // posyandu hanya melihat status stunting dan status gizi, bukan skor atau keputusan bantuan
        return redirect()->route('posyandu.pemeriksaan.index')
            ->with('sukses', 'Pemeriksaan ' . $anak->nama . ' tersimpan. Status stunting: '
                . $hasil['status_stunting'] . ', status gizi: ' . $hasil['status_gizi'] . '.');
    }
}
