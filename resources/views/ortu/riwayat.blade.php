{{-- halaman riwayat pemeriksaan (orang tua): hanya lihat, tanpa skor dan keputusan bantuan --}}
@extends('adminlte::page')

@section('title', 'Riwayat Pemeriksaan')

@section('content_header')
    <h1>Riwayat Pemeriksaan</h1>
@stop

@section('content')
    @if (!$anak)
        {{-- belum ada anak yang pernah diperiksa --}}
        <div class="alert alert-info">
            Belum ada hasil pemeriksaan. Riwayat akan tampil di sini setelah posyandu memeriksa anak Anda.
        </div>
    @else
        {{-- pilihan anak: satu tombol per anak, jadi anak kembar tinggal klik namanya --}}
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills">
                    @foreach ($daftarAnak as $a)
                        <li class="nav-item">
                            <a class="nav-link {{ $a->id == $anak->id ? 'active' : '' }}"
                                href="{{ route('ortu.riwayat.index', ['anak' => $a->id]) }}">
                                {{ $a->nama }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @php
            // merapikan angka: 0.40 menjadi "0,4", 1.00 menjadi "1"
            $fmt = fn($n, $d) => rtrim(rtrim(number_format(abs($n), $d, ',', ''), '0'), ',');
        @endphp

        {{-- hasil pemeriksaan terakhir --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pemeriksaan Terakhir: {{ $anak->nama }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th style="width: 200px">Tanggal Pemeriksaan</th>
                                <td>{{ $terakhir->tanggal_ukur->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Usia Saat Diperiksa</th>
                                <td>{{ number_format($terakhir->usia_bulan, 1, ',', '') }} bulan</td>
                            </tr>
                            <tr>
                                <th>Berat Badan</th>
                                <td>{{ $fmt($terakhir->berat, 2) }} kg</td>
                            </tr>
                            <tr>
                                <th>Tinggi / Panjang Badan</th>
                                <td>{{ $fmt($terakhir->tinggi, 1) }} cm</td>
                            </tr>
                            <tr>
                                <th>Dibanding Sebelumnya</th>
                                <td>
                                    @if ($selisih)
                                        @php
                                            // kata naik, turun, atau tetap untuk berat dan tinggi
                                            $kata = fn($n) => $n > 0 ? 'naik' : ($n < 0 ? 'turun' : 'tetap');
                                        @endphp
                                        Berat
                                        {{ $kata($selisih['berat']) }}{{ $selisih['berat'] != 0 ? ' ' . $fmt($selisih['berat'], 2) . ' kg' : '' }},
                                        tinggi
                                        {{ $kata($selisih['tinggi']) }}{{ $selisih['tinggi'] != 0 ? ' ' . $fmt($selisih['tinggi'], 1) . ' cm' : '' }}
                                        <br><small class="text-muted">
                                            dibanding pemeriksaan {{ $riwayat->get(1)->tanggal_ukur->format('d-m-Y') }}
                                        </small>
                                    @else
                                        <span class="text-muted">Ini pemeriksaan pertama.</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th style="width: 160px">Status Stunting</th>
                                <td>
                                    @if ($terakhir->status_stunting == 'Stunting')
                                        <span class="badge badge-danger">Stunting</span>
                                    @elseif ($terakhir->status_stunting == 'Berisiko')
                                        <span class="badge badge-warning">Berisiko</span>
                                    @else
                                        <span class="badge badge-success">Normal</span>
                                    @endif
                                    <br><small>{{ data_get($terakhir->hasil_api, 'penjelasan.stunting') }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Status Gizi</th>
                                <td>
                                    @if ($terakhir->status_gizi == 'Gizi Kurang')
                                        <span class="badge badge-danger">Gizi Kurang</span>
                                    @elseif ($terakhir->status_gizi == 'Gizi Lebih')
                                        <span class="badge badge-warning">Gizi Lebih</span>
                                    @else
                                        <span class="badge badge-success">Normal</span>
                                    @endif
                                    <br><small>{{ data_get($terakhir->hasil_api, 'penjelasan.gizi') }}</small>
                                </td>
                            </tr>
                        </table>
                        <p class="text-muted mt-3 mb-0">
                            <small>
                                Hasil ini adalah skrining awal dari data pengukuran. Konsultasikan dengan
                                petugas posyandu atau puskesmas untuk penanganan lebih lanjut.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        {{-- dua grafik perkembangan anak --}}
        <div class="row">
            {{-- grafik z-score: tiga garis dan batas -2 --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Grafik Z-Score</h3>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 320px;">
                            <canvas id="grafikZ"></canvas>
                        </div>
                        <small class="text-muted">
                            Garis putus-putus merah adalah batas -2. Garis yang turun mendekati atau melewati batas
                            perlu diperhatikan. Z-Score hanya ada untuk pemeriksaan baru.
                        </small>
                    </div>
                </div>
            </div>

            {{-- grafik berat dan tinggi badan --}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Grafik Berat dan Tinggi Badan</h3>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 320px;">
                            <canvas id="grafikFisik"></canvas>
                        </div>
                        <small class="text-muted">
                            Garis biru = berat badan (sumbu kiri), garis hijau = tinggi badan (sumbu kanan).
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- tabel riwayat lengkap, terbaru di atas --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Semua Pemeriksaan ({{ $riwayat->count() }} kali)</h3>
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
                                    <small>{{ data_get($p->hasil_api, 'penjelasan.stunting') ?? '-' }}</small>
                                </td>

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
                                    <small>{{ data_get($p->hasil_api, 'penjelasan.gizi') ?? '-' }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop

{{-- grafik memakai Chart.js, dimuat dari internet (versi dikunci 4.4.1) --}}
@section('js')
    {{-- grafik hanya digambar kalau ada anak yang pernah diperiksa --}}
    @if ($anak)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
        <script>
            // data grafik dari controller (tanggal, berat, tinggi, dan z-score)
            const g = @json($grafik);

            // warna tiap garis
            const warna = {
                bb_u: '#007bff',
                tb_u: '#28a745',
                bb_tb: '#fd7e14'
            };

            // grafik 1: z-score BB/U, TB/U, BB/TB, ditambah garis batas -2
            new Chart(document.getElementById('grafikZ'), {
                type: 'line',
                data: {
                    labels: g.label,
                    datasets: [{
                            label: 'BB/U',
                            data: g.z_bb_u,
                            borderColor: warna.bb_u,
                            backgroundColor: warna.bb_u,
                            tension: 0.2,
                            spanGaps: true,
                            pointRadius: 4
                        },
                        {
                            label: 'TB/U',
                            data: g.z_tb_u,
                            borderColor: warna.tb_u,
                            backgroundColor: warna.tb_u,
                            tension: 0.2,
                            spanGaps: true,
                            pointRadius: 4
                        },
                        {
                            label: 'BB/TB',
                            data: g.z_bb_tb,
                            borderColor: warna.bb_tb,
                            backgroundColor: warna.bb_tb,
                            tension: 0.2,
                            spanGaps: true,
                            pointRadius: 4
                        },
                        // garis batas -2 (putus-putus merah, tanpa titik)
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
        </script>
    @endif
@stop
