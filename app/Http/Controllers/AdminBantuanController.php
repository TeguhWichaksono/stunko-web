<?php

namespace App\Http\Controllers;

use App\Models\Pengukuran;
use App\Models\Posyandu;
use Illuminate\Http\Request;
use App\Models\Anak;
use App\Models\JenisBantuan;
use App\Models\Bantuan;

class AdminBantuanController extends Controller
{
    // daftar calon penerima bantuan: pemeriksaan terbaru tiap anak yang skornya mencapai skor minimal
    public function index(Request $request)
    {
        $request->validate([
            'cari' => 'nullable|string|max:100',
            'posyandu' => 'nullable|integer',
            'status' => 'nullable|in:baru,dihubungi,berhak,tidak',
            'min' => 'nullable|numeric|min:0|max:100',
        ]);
        $cari = $request->input('cari');
        $posyanduId = $request->input('posyandu');
        $status = $request->input('status');

        // skor minimal, kalau tidak diisi dipakai 50
        $min = $request->filled('min') ? (float) $request->input('min') : 50;

        // semua pemeriksaan beserta anak, orang tua, posyandu, dan catatan bantuannya
        $query = Pengukuran::with([
            'anak.orangTua',
            'anak.posyandu.user',
            'anak.bantuan.jenisBantuan',
        ]);

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

        // status tindak lanjut anak: baru, dihubungi, berhak, atau tidak
        // kalau belum ada catatan bantuan, berarti masih 'baru'
        $statusDari = fn($p) => $p->anak->bantuan->status ?? 'baru';

        // hanya anak yang memenuhi skor minimal, atau yang sudah ditindaklanjuti
        // (supaya anak yang sudah diproses tidak hilang dari daftar)
        $terbaru = $terbaru->filter(function ($p) use ($min, $statusDari) {
            return (float) $p->skor >= $min || $statusDari($p) != 'baru';
        });

        // ringkasan jumlah (dihitung sebelum disaring berdasarkan status)
        $ringkasan = [
            'total' => $terbaru->count(),
            'baru' => $terbaru->filter(fn($p) => $statusDari($p) == 'baru')->count(),
            'dihubungi' => $terbaru->filter(fn($p) => $statusDari($p) == 'dihubungi')->count(),
            'berhak' => $terbaru->filter(fn($p) => $statusDari($p) == 'berhak')->count(),
            'tidak' => $terbaru->filter(fn($p) => $statusDari($p) == 'tidak')->count(),
        ];

        // saring berdasarkan status tindak lanjut yang dipilih
        if ($status) {
            $terbaru = $terbaru->filter(fn($p) => $statusDari($p) == $status);
        }

        // urutan: baru, dihubungi, berhak, tidak berhak; di tiap kelompok skor tertinggi di atas
        $urutan = ['baru' => 0, 'dihubungi' => 1, 'berhak' => 2, 'tidak' => 3];
        $daftar = $terbaru
            ->sortBy(fn($p) => sprintf('%d-%08.2f', $urutan[$statusDari($p)], 100 - (float) $p->skor))
            ->values();

        // daftar posyandu untuk pilihan penyaring
        $daftarPosyandu = Posyandu::with('user')->get()->sortBy(fn($p) => $p->user->name);

        return view('admin.bantuan', compact(
            'daftar',
            'ringkasan',
            'daftarPosyandu',
            'cari',
            'posyanduId',
            'status',
            'min'
        ));
    }

    // halaman detail satu anak: biodata, orang tua, posyandu, riwayat pemeriksaan, dan tindak lanjut
    public function detail($id)
    {
        // hanya anak yang pernah diperiksa, selain itu 404
        $anak = Anak::with(['orangTua.user', 'posyandu.user', 'bantuan.jenisBantuan', 'bantuan.pemutus'])
            ->has('pengukuran')
            ->findOrFail($id);

        // riwayat dari yang lama ke yang baru (untuk grafik), dan sebaliknya (untuk tabel)
        $urutLama = $anak->pengukuran()->orderBy('tanggal_ukur')->get();
        $riwayat = $urutLama->sortByDesc('tanggal_ukur')->values();
        $terakhir = $riwayat->first();

        // usia anak sekarang dalam bulan penuh
        $usiaSekarang = $anak->tanggal_lahir->diffInMonths(now());

        // daftar jenis bantuan, dan saran yang cocok dengan usia anak sekarang
        $daftarJenis = JenisBantuan::orderBy('usia_min')->get();
        $saran = $daftarJenis->first(fn($j) => $j->cocokUntuk($usiaSekarang));

        // tautan WhatsApp ke orang tua: nomor diubah ke format 62..., pesan awal tidak memuat hasil pemeriksaan
        $hp = preg_replace('/\D+/', '', (string) $anak->orangTua->no_hp);
        if (str_starts_with($hp, '0')) {
            $hp = '62' . substr($hp, 1);
        }
        $pesan = 'Yth. Bapak/Ibu ' . $anak->orangTua->nama_lengkap . ', kami dari puskesmas. '
            . 'Berdasarkan pemeriksaan di ' . ($anak->posyandu ? $anak->posyandu->user->name : 'posyandu')
            . ', ananda ' . $anak->nama . ' perlu dipantau lebih lanjut. '
            . 'Mohon berkenan melakukan pemeriksaan lanjutan ke dokter spesialis anak. '
            . 'Informasi lebih lanjut bisa menghubungi kami. Terima kasih.';
        $waLink = 'https://wa.me/' . $hp . '?text=' . rawurlencode($pesan);

        // data untuk grafik (urut dari lama ke baru), z-score kosong diubah jadi null
        $grafik = [
            'label' => $urutLama->map(fn($p) => $p->tanggal_ukur->format('d-m-Y'))->all(),
            'berat' => $urutLama->map(fn($p) => (float) $p->berat)->all(),
            'tinggi' => $urutLama->map(fn($p) => (float) $p->tinggi)->all(),
            'skor' => $urutLama->map(fn($p) => (float) $p->skor)->all(),
            'z_bb_u' => $urutLama->map(fn($p) => $p->z_bb_u !== null ? (float) $p->z_bb_u : null)->all(),
            'z_tb_u' => $urutLama->map(fn($p) => $p->z_tb_u !== null ? (float) $p->z_tb_u : null)->all(),
            'z_bb_tb' => $urutLama->map(fn($p) => $p->z_bb_tb !== null ? (float) $p->z_bb_tb : null)->all(),
        ];

        return view('admin.bantuan_detail', compact(
            'anak',
            'riwayat',
            'terakhir',
            'usiaSekarang',
            'daftarJenis',
            'saran',
            'waLink',
            'grafik'
        ));
    }
    // ambil satu anak yang pernah diperiksa, selain itu 404
    private function anakBantuan($id)
    {
        return Anak::has('pengukuran')->findOrFail($id);
    }

    // kembali ke halaman detail anak dengan sebuah pesan (jenis: 'sukses' atau 'galat')
    private function kembali($id, $jenis, $pesan)
    {
        return redirect()->route('admin.bantuan.detail', $id)->with($jenis, $pesan);
    }

    // langkah 1: catat bahwa orang tua sudah dihubungi untuk periksa lanjutan ke dokter spesialis
    public function hubungi(Request $request, $id)
    {
        $anak = $this->anakBantuan($id);
        $b = Bantuan::firstOrCreate(['anak_id' => $anak->id], ['status' => 'baru']);

        // setelah ada keputusan, data terkunci
        if (!in_array($b->status, ['baru', 'dihubungi'])) {
            return $this->kembali($anak->id, 'galat', 'Keputusan sudah diambil, data tidak bisa diubah.');
        }

        $request->validate(['catatan_hubungi' => 'nullable|string|max:1000']);

        $b->update([
            'status' => 'dihubungi',
            'dihubungi_pada' => now(),
            'catatan_hubungi' => $request->catatan_hubungi,
        ]);

        return $this->kembali($anak->id, 'sukses', 'Tercatat: orang tua sudah dihubungi.');
    }

    // langkah 2: catat tanggal dan hasil pemeriksaan dokter spesialis anak
    public function hasil(Request $request, $id)
    {
        $anak = $this->anakBantuan($id);
        $b = Bantuan::where('anak_id', $anak->id)->first();

        // hasil dokter baru bisa dicatat setelah orang tua dihubungi, dan sebelum ada keputusan
        if (!$b || $b->status !== 'dihubungi') {
            return $this->kembali($anak->id, 'galat', 'Hasil dokter hanya bisa dicatat setelah orang tua dihubungi dan sebelum ada keputusan.');
        }

        $data = $request->validate([
            'tanggal_periksa_spesialis' => 'required|date|before_or_equal:today',
            'hasil_spesialis' => 'required|string|max:2000',
        ], [
            'tanggal_periksa_spesialis.required' => 'Isi tanggal pemeriksaan dokter spesialis.',
            'hasil_spesialis.required' => 'Isi ringkasan hasil pemeriksaan dokter spesialis.',
        ]);

        $b->update($data);

        return $this->kembali($anak->id, 'sukses', 'Hasil dokter spesialis tersimpan.');
    }

    // langkah 3: putuskan berhak atau tidak berhak, berdasarkan hasil dokter spesialis
    public function putuskan(Request $request, $id)
    {
        $anak = $this->anakBantuan($id);
        $b = Bantuan::where('anak_id', $anak->id)->first();

        if (!$b || $b->status !== 'dihubungi') {
            return $this->kembali($anak->id, 'galat', 'Catat dulu bahwa orang tua sudah dihubungi, atau keputusan sudah pernah diambil.');
        }

        // keputusan hanya boleh diambil setelah ada hasil dokter spesialis
        if (!$b->hasil_spesialis) {
            return $this->kembali($anak->id, 'galat', 'Catat hasil dokter spesialis dulu sebelum memutuskan.');
        }

        $data = $request->validate([
            'keputusan' => 'required|in:berhak,tidak',
            'jenis_bantuan_id' => 'required_if:keputusan,berhak|nullable|exists:jenis_bantuan,id',
            'alasan_keputusan' => 'required_if:keputusan,tidak|nullable|string|max:1000',
        ], [
            'keputusan.required' => 'Pilih Berhak atau Tidak berhak.',
            'jenis_bantuan_id.required_if' => 'Pilih jenis bantuan untuk anak yang berhak.',
            'alasan_keputusan.required_if' => 'Isi alasan kalau anak dinyatakan tidak berhak.',
        ]);

        $berhak = $data['keputusan'] === 'berhak';

        $b->update([
            'status' => $berhak ? 'berhak' : 'tidak',
            // jenis bantuan hanya disimpan kalau berhak
            'jenis_bantuan_id' => $berhak ? $data['jenis_bantuan_id'] : null,
            'alasan_keputusan' => $data['alasan_keputusan'] ?? null,
            'diputuskan_pada' => now(),
            'diputuskan_oleh' => auth()->id(),
        ]);

        return $this->kembali($anak->id, 'sukses', $berhak
            ? $anak->nama . ' dinyatakan berhak menerima bantuan.'
            : $anak->nama . ' dinyatakan tidak berhak menerima bantuan.');
    }
}
