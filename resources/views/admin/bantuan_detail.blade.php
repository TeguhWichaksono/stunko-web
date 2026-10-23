{{-- detail satu anak calon penerima bantuan (khusus admin): biodata, orang tua, posyandu, riwayat, dan grafik --}}
@extends('adminlte::page')

@section('title', 'Detail Anak')

@section('content_header')
    <h1>Detail Anak: {{ $anak->nama }}</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    @php
        // singkatan supaya penulisan di bawah lebih pendek
        $o = $anak->orangTua;
        $ps = $anak->posyandu;
        $b = $anak->bantuan;
    @endphp

    <div class="mb-3">
        <a href="{{ route('admin.bantuan.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>

    {{-- ringkasan pemeriksaan terakhir --}}
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Skor Pemeriksaan Terakhir</span>
                    <span class="info-box-number">{{ number_format($terakhir->skor, 1, ',', '') }}</span>
                    <span class="progress-description">Diukur {{ $terakhir->tanggal_ukur->format('d-m-Y') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-ruler-vertical"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Status Stunting</span>
                    <span class="info-box-number">
                        @if ($terakhir->status_stunting == 'Stunting')
                            <span class="badge badge-danger">Stunting</span>
                        @elseif ($terakhir->status_stunting == 'Berisiko')
                            <span class="badge badge-warning">Berisiko</span>
                        @else
                            <span class="badge badge-success">Normal</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-weight"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Status Gizi</span>
                    <span class="info-box-number">
                        @if ($terakhir->status_gizi == 'Gizi Kurang')
                            <span class="badge badge-danger">Gizi Kurang</span>
                        @elseif ($terakhir->status_gizi == 'Gizi Lebih')
                            <span class="badge badge-warning">Gizi Lebih</span>
                        @else
                            <span class="badge badge-success">Normal</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- tiga kartu: biodata anak, orang tua, dan posyandu --}}
    <div class="row">
        {{-- biodata anak --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Biodata Anak</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 140px">Nama</th>
                            <td>{{ $anak->nama }}</td>
                        </tr>
                        <tr>
                            <th>NIK</th>
                            <td>{{ $anak->nik }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <th>TTL</th>
                            <td>{{ $anak->tempat_lahir }}, {{ $anak->tanggal_lahir->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Usia Sekarang</th>
                            <td>{{ intdiv($usiaSekarang, 12) }} tahun {{ $usiaSekarang % 12 }} bulan</td>
                        </tr>
                        <tr>
                            <th>Berat Lahir</th>
                            <td>{{ $anak->berat_lahir ? $anak->berat_lahir + 0 . ' kg' : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tinggi Lahir</th>
                            <td>{{ $anak->tinggi_lahir ? $anak->tinggi_lahir + 0 . ' cm' : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- biodata orang tua dan tombol WhatsApp --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Orang Tua</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 140px">Nama</th>
                            <td>{{ $o->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <th>NIK</th>
                            <td>{{ $o->nik }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $o->no_hp }}</td>
                        </tr>
                        <tr>
                            <th>Pekerjaan</th>
                            <td>{{ $o->pekerjaan }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $o->alamat }}</td>
                        </tr>
                        <tr>
                            <th>RT / RW</th>
                            <td>{{ $o->rt }} / {{ $o->rw }}</td>
                        </tr>
                        <tr>
                            <th>Kelurahan</th>
                            <td>{{ $o->kelurahan }}</td>
                        </tr>
                        <tr>
                            <th>Email Akun</th>
                            <td>{{ $o->user->email }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    {{-- membuka WhatsApp dengan pesan awal; pesan tidak memuat hasil pemeriksaan anak --}}
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                    </a>
                    <small class="text-muted d-block mt-2">
                        Pesan awal hanya mengajak periksa lanjutan, tanpa menyebut hasil pemeriksaan.
                    </small>
                </div>
            </div>
        </div>

        {{-- posyandu asal anak --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Posyandu</h3>
                </div>
                <div class="card-body">
                    @if ($ps)
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th style="width: 140px">Nama</th>
                                <td>{{ $ps->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Kelurahan</th>
                                <td>{{ $ps->kelurahan }}</td>
                            </tr>
                            <tr>
                                <th>Wilayah</th>
                                <td>{{ $ps->wilayah }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $ps->alamat }}</td>
                            </tr>
                            <tr>
                                <th>Kepala</th>
                                <td>{{ $ps->nama_kepala }}</td>
                            </tr>
                            <tr>
                                <th>No. HP</th>
                                <td>{{ $ps->no_hp }}</td>
                            </tr>
                        </table>
                    @else
                        <span class="text-muted">Anak ini belum terhubung dengan posyandu.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- tindak lanjut bantuan: tiga langkah berurutan --}}
    @php
        // status tindak lanjut: baru, dihubungi, berhak, atau tidak
        $st = $b->status ?? 'baru';
    @endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tindak Lanjut Bantuan</h3>
            <span class="ml-3">
                @if ($st == 'baru')
                    <span class="badge badge-secondary">Belum ditindaklanjuti</span>
                @elseif ($st == 'dihubungi')
                    <span class="badge badge-warning">Sudah dihubungi</span>
                @elseif ($st == 'berhak')
                    <span class="badge badge-success">Berhak</span>
                @else
                    <span class="badge badge-dark">Tidak berhak</span>
                @endif
            </span>
        </div>
        <div class="card-body">

            {{-- langkah 1: hubungi orang tua --}}
            <h5>1. Hubungi orang tua</h5>
            @if ($st == 'baru')
                <p class="text-muted">
                    Hubungi orang tua lewat tombol WhatsApp di atas atau lewat telepon, lalu ajak periksa lanjutan
                    ke dokter spesialis anak. Setelah itu catat di sini.
                </p>
                <form action="{{ route('admin.bantuan.hubungi', $anak->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Catatan <small class="text-muted">- boleh kosong</small></label>
                        <textarea name="catatan_hubungi" rows="2" class="form-control @error('catatan_hubungi') is-invalid @enderror"
                            placeholder="Contoh: dihubungi lewat WhatsApp, orang tua bersedia periksa hari Senin.">{{ old('catatan_hubungi') }}</textarea>
                        @error('catatan_hubungi')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-warning">Catat Sudah Dihubungi</button>
                </form>
            @else
                <p class="mb-0">
                    <i class="fas fa-check-circle text-success"></i>
                    Sudah dihubungi pada {{ $b->dihubungi_pada?->format('d-m-Y H:i') }}.
                    @if ($b->catatan_hubungi)
                        <br><small class="text-muted">Catatan: {{ $b->catatan_hubungi }}</small>
                    @endif
                </p>
            @endif

            <hr>

            {{-- langkah 2: hasil dokter spesialis --}}
            <h5>2. Hasil dokter spesialis anak</h5>
            @if ($st == 'baru')
                <p class="text-muted mb-0">Tersedia setelah orang tua dicatat sudah dihubungi.</p>
            @elseif ($st == 'dihubungi')
                {{-- form ini juga dipakai untuk memperbaiki hasil, selama keputusan belum diambil --}}
                <form action="{{ route('admin.bantuan.hasil', $anak->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Tanggal Pemeriksaan</label>
                        <input type="date" name="tanggal_periksa_spesialis" max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_periksa_spesialis', $b->tanggal_periksa_spesialis?->format('Y-m-d')) }}"
                            class="form-control @error('tanggal_periksa_spesialis') is-invalid @enderror"
                            style="max-width: 250px">
                        @error('tanggal_periksa_spesialis')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Ringkasan Hasil Pemeriksaan Dokter Spesialis</label>
                        <textarea name="hasil_spesialis" rows="3" class="form-control @error('hasil_spesialis') is-invalid @enderror"
                            placeholder="Diagnosis, rekomendasi, dan apakah anak perlu mendapat susu khusus.">{{ old('hasil_spesialis', $b->hasil_spesialis) }}</textarea>
                        @error('hasil_spesialis')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-info">
                        {{ $b->hasil_spesialis ? 'Perbarui Hasil Dokter' : 'Simpan Hasil Dokter' }}
                    </button>
                </form>
            @else
                <p class="mb-0">
                    Diperiksa pada {{ $b->tanggal_periksa_spesialis?->format('d-m-Y') }}.
                    <br>{{ $b->hasil_spesialis }}
                </p>
            @endif

            <hr>

            {{-- langkah 3: keputusan berhak atau tidak berhak --}}
            <h5>3. Keputusan bantuan</h5>
            @if ($st == 'baru')
                <p class="text-muted mb-0">Tersedia setelah orang tua dihubungi dan hasil dokter spesialis dicatat.</p>
            @elseif ($st == 'dihubungi' && !$b->hasil_spesialis)
                <p class="text-muted mb-0">Tersedia setelah hasil dokter spesialis dicatat.</p>
            @elseif ($st == 'dihubungi')
                <p class="text-muted">
                    Putuskan berdasarkan hasil dokter spesialis di atas. Bantuan berupa susu yang
                    harus dengan resep dokter. Setelah keputusan disimpan, data tidak bisa diubah.
                </p>

                {{-- konfirmasi sebelum menyimpan --}}
                <form action="{{ route('admin.bantuan.putuskan', $anak->id) }}" method="POST"
                    onsubmit="return confirm('Simpan keputusan ini? Setelah disimpan, data tidak bisa diubah.')">
                    @csrf

                    @error('keputusan')
                        <div class="text-danger mb-2">{{ $message }}</div>
                    @enderror

                    <div class="row">
                        {{-- pilihan berhak, lengkap dengan jenis bantuan --}}
                        <div class="col-lg-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" id="k_berhak" name="keputusan"
                                    value="berhak" {{ old('keputusan') == 'berhak' ? 'checked' : '' }}>
                                <label for="k_berhak" class="custom-control-label">Berhak menerima bantuan</label>
                            </div>
                            <div class="form-group">
                                <label>Jenis Bantuan</label>
                                @if ($daftarJenis->isEmpty())
                                    <div class="text-muted">Belum ada jenis bantuan. Tambahkan dulu di menu Jenis Bantuan.
                                    </div>
                                @else
                                    <select name="jenis_bantuan_id"
                                        class="form-control @error('jenis_bantuan_id') is-invalid @enderror">
                                        <option value="">-- Pilih jenis bantuan --</option>
                                        @foreach ($daftarJenis as $j)
                                            <option value="{{ $j->id }}"
                                                {{ old('jenis_bantuan_id', $saran->id ?? '') == $j->id ? 'selected' : '' }}>
                                                {{ $j->nama }} (usia {{ $j->usia_min }}-{{ $j->usia_maks }}
                                                bulan){{ $saran && $saran->id == $j->id ? ' - disarankan' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis_bantuan_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                @endif
                            </div>
                        </div>

                        {{-- pilihan tidak berhak, lengkap dengan alasan --}}
                        <div class="col-lg-6">
                            <div class="custom-control custom-radio mb-2">
                                <input class="custom-control-input" type="radio" id="k_tidak" name="keputusan"
                                    value="tidak" {{ old('keputusan') == 'tidak' ? 'checked' : '' }}>
                                <label for="k_tidak" class="custom-control-label">Tidak berhak menerima bantuan</label>
                            </div>
                            <div class="form-group">
                                <label>Alasan <small class="text-muted">- wajib diisi kalau tidak berhak</small></label>
                                <textarea name="alasan_keputusan" rows="3" class="form-control @error('alasan_keputusan') is-invalid @enderror"
                                    placeholder="Contoh: dokter spesialis menyatakan tidak perlu susu khusus.">{{ old('alasan_keputusan') }}</textarea>
                                @error('alasan_keputusan')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Simpan Keputusan</button>
                </form>
            @else
                {{-- keputusan sudah diambil: tampil saja, tidak bisa diubah --}}
                <p class="mb-1">
                    @if ($st == 'berhak')
                        <span class="badge badge-success">Berhak</span>
                        menerima <b>{{ $b->jenisBantuan->nama ?? '-' }}</b>
                    @else
                        <span class="badge badge-dark">Tidak berhak</span>
                        <br>Alasan: {{ $b->alasan_keputusan }}
                    @endif
                </p>
                <small class="text-muted">
                    Diputuskan pada {{ $b->diputuskan_pada?->format('d-m-Y H:i') }}
                    oleh {{ $b->pemutus->name ?? '-' }}. Data terkunci.
                </small>
            @endif
        </div>
    </div>

    {{-- tiga grafik perkembangan --}}
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grafik Z-Score</h3>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 280px;"><canvas id="grafikZ"></canvas></div>
                    <small class="text-muted">Garis putus-putus merah = batas -2. Hanya untuk pemeriksaan baru.</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grafik Berat dan Tinggi</h3>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 280px;"><canvas id="grafikFisik"></canvas></div>
                    <small class="text-muted">Biru = berat (kiri), hijau = tinggi (kanan).</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Grafik Skor</h3>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 280px;"><canvas id="grafikSkor"></canvas></div>
                    <small class="text-muted">Makin tinggi, makin diprioritaskan (skala 0-100).</small>
                </div>
            </div>
        </div>
    </div>

    {{-- riwayat pemeriksaan lengkap, terbaru di atas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pemeriksaan ({{ $riwayat->count() }} kali)</h3>
            <small class="text-muted ml-3">Z-Score merah = di bawah -2. Tanda "-" = pemeriksaan lama (belum ada
                z-score).</small>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Tanggal Ukur</th>
                        <th>Usia (bln)</th>
                        <th>Berat (kg)</th>
                        <th>Tinggi (cm)</th>
                        <th>Cara Ukur</th>
                        <th>LiLA (cm)</th>
                        <th>BB/U</th>
                        <th>Z BB/U</th>
                        <th>TB/U</th>
                        <th>Z TB/U</th>
                        <th>BB/TB</th>
                        <th>Z BB/TB</th>
                        <th>Status Stunting</th>
                        <th>Keterangan Stunting</th>
                        <th>Status Gizi</th>
                        <th>Keterangan Gizi</th>
                        <th>Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayat as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">{{ $p->tanggal_ukur->format('d-m-Y') }}</td>
                            <td>{{ number_format($p->usia_bulan, 1) }}</td>
                            <td>{{ $p->berat + 0 }}</td>
                            <td>{{ $p->tinggi + 0 }}</td>
                            <td>{{ $p->cara_ukur ? ucfirst($p->cara_ukur) : '-' }}</td>
                            <td>{{ $p->lila !== null ? $p->lila + 0 : '-' }}</td>

                            {{-- BB/U: kategori lalu angka z-score --}}
                            <td class="text-nowrap">{{ data_get($p->hasil_api, 'kategori_zscore.bb_u') ?? '-' }}</td>
                            <td
                                class="text-nowrap {{ $p->z_bb_u !== null && $p->z_bb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_u !== null ? number_format($p->z_bb_u, 2, ',', '.') : '-' }}
                            </td>

                            {{-- TB/U: kategori lalu angka z-score --}}
                            <td class="text-nowrap">{{ data_get($p->hasil_api, 'kategori_zscore.tb_u') ?? '-' }}</td>
                            <td
                                class="text-nowrap {{ $p->z_tb_u !== null && $p->z_tb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_tb_u !== null ? number_format($p->z_tb_u, 2, ',', '.') : '-' }}
                            </td>

                            {{-- BB/TB: kategori lalu angka z-score --}}
                            <td class="text-nowrap">{{ data_get($p->hasil_api, 'kategori_zscore.bb_tb') ?? '-' }}</td>
                            <td
                                class="text-nowrap {{ $p->z_bb_tb !== null && $p->z_bb_tb < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_tb !== null ? number_format($p->z_bb_tb, 2, ',', '.') : '-' }}
                            </td>

                            {{-- status stunting lalu keterangannya --}}
                            <td>
                                @if ($p->status_stunting == 'Stunting')
                                    <span class="badge badge-danger">Stunting</span>
                                @elseif ($p->status_stunting == 'Berisiko')
                                    <span class="badge badge-warning">Berisiko</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td style="min-width: 230px">
                                <small>{{ data_get($p->hasil_api, 'penjelasan.stunting') ?? '-' }}</small></td>

                            {{-- status gizi lalu keterangannya --}}
                            <td>
                                @if ($p->status_gizi == 'Gizi Kurang')
                                    <span class="badge badge-danger">Gizi Kurang</span>
                                @elseif ($p->status_gizi == 'Gizi Lebih')
                                    <span class="badge badge-warning">Gizi Lebih</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td style="min-width: 230px">
                                <small>{{ data_get($p->hasil_api, 'penjelasan.gizi') ?? '-' }}</small></td>

                            {{-- skor (hanya admin yang melihat) --}}
                            <td class="text-nowrap"><b>{{ number_format($p->skor, 1, ',', '') }}</b></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

{{-- grafik memakai Chart.js, dimuat dari internet (versi dikunci 4.4.1) --}}
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <script>
        // data grafik dari controller (tanggal, berat, tinggi, skor, dan z-score)
        const g = @json($grafik);

        // grafik 1: z-score BB/U, TB/U, BB/TB, ditambah garis batas -2
        new Chart(document.getElementById('grafikZ'), {
            type: 'line',
            data: {
                labels: g.label,
                datasets: [{
                        label: 'BB/U',
                        data: g.z_bb_u,
                        borderColor: '#007bff',
                        backgroundColor: '#007bff',
                        tension: 0.2,
                        spanGaps: true,
                        pointRadius: 4
                    },
                    {
                        label: 'TB/U',
                        data: g.z_tb_u,
                        borderColor: '#28a745',
                        backgroundColor: '#28a745',
                        tension: 0.2,
                        spanGaps: true,
                        pointRadius: 4
                    },
                    {
                        label: 'BB/TB',
                        data: g.z_bb_tb,
                        borderColor: '#fd7e14',
                        backgroundColor: '#fd7e14',
                        tension: 0.2,
                        spanGaps: true,
                        pointRadius: 4
                    },
                    {
                        label: 'Batas -2',
                        data: g.label.map(() => -2),
                        borderColor: '#dc3545',
                        borderDash: [6, 6],
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        suggestedMin: -4,
                        suggestedMax: 3,
                        title: {
                            display: true,
                            text: 'Z-Score'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // grafik 2: berat (sumbu kiri) dan tinggi (sumbu kanan)
        new Chart(document.getElementById('grafikFisik'), {
            type: 'line',
            data: {
                labels: g.label,
                datasets: [{
                        label: 'Berat (kg)',
                        data: g.berat,
                        borderColor: '#007bff',
                        backgroundColor: '#007bff',
                        yAxisID: 'y',
                        tension: 0.2,
                        pointRadius: 4
                    },
                    {
                        label: 'Tinggi (cm)',
                        data: g.tinggi,
                        borderColor: '#28a745',
                        backgroundColor: '#28a745',
                        yAxisID: 'y1',
                        tension: 0.2,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Berat (kg)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Tinggi (cm)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // grafik 3: skor tiap pemeriksaan (skala 0-100)
        new Chart(document.getElementById('grafikSkor'), {
            type: 'line',
            data: {
                labels: g.label,
                datasets: [{
                    label: 'Skor',
                    data: g.skor,
                    borderColor: '#dc3545',
                    backgroundColor: '#dc3545',
                    tension: 0.2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Skor'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@stop
