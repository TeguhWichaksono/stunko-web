{{-- dashboard admin (puskesmas): kartu ringkasan dan dua tabel kerja --}}
@extends('adminlte::page')

@section('title', 'Dashboard Admin')

@section('content_header')
    <h1>Dashboard Admin (Puskesmas)</h1>
@stop

@section('content')
    <p class="text-muted">
        Selamat datang, {{ Auth::user()->name }}. Data bulan {{ now()->locale('id')->translatedFormat('F Y') }}.
        Angka status dihitung dari pemeriksaan terbaru tiap anak di seluruh posyandu.
    </p>

    {{-- kartu ringkasan --}}
    <div class="row">
        {{-- jumlah akun posyandu --}}
        <div class="col-md-4 col-lg-2">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-clinic-medical"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Posyandu</span>
                    <span class="info-box-number">{{ $jumlahPosyandu }}</span>
                </div>
            </div>
        </div>

        {{-- anak yang sudah diterima di posyandu --}}
        <div class="col-md-4 col-lg-2">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-child"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Anak Terdaftar</span>
                    <span class="info-box-number">{{ $jumlahAnak }}</span>
                </div>
            </div>
        </div>

        {{-- anak yang sudah diperiksa bulan ini, dengan batang persen --}}
        <div class="col-md-4 col-lg-2">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-stethoscope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Diperiksa Bulan Ini</span>
                    <span class="info-box-number">{{ $sudahBulanIni }} dari {{ $jumlahAnak }}</span>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $persenBulanIni }}%"></div>
                    </div>
                    <span class="progress-description">{{ $persenBulanIni }}%</span>
                </div>
            </div>
        </div>

        {{-- jumlah anak stunting menurut pemeriksaan terbaru --}}
        <div class="col-md-4 col-lg-2">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-ruler-vertical"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Anak Stunting</span>
                    <span class="info-box-number">{{ $donatStunting['Stunting'] }}</span>
                </div>
            </div>
        </div>

        {{-- anak yang perlu ditindaklanjuti, tautan ke daftar yang sudah disaring --}}
        <div class="col-md-4 col-lg-2">
            <a href="{{ route('admin.bantuan.index', ['min' => $skorMin, 'status' => 'baru']) }}" class="text-dark">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-phone"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Perlu Ditindaklanjuti</span>
                        <span class="info-box-number">{{ $jumlahPerluTindak }}</span>
                        <span class="progress-description">{{ $jumlahDihubungi }} sedang diproses</span>
                    </div>
                </div>
            </a>
        </div>

        {{-- anak yang sudah dinyatakan berhak bantuan --}}
        <div class="col-md-4 col-lg-2">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-hand-holding-heart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Berhak Bantuan</span>
                    <span class="info-box-number">{{ $jumlahBerhak }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- grafik: dua donat (sebaran status), tren 6 bulan, dan perbandingan antar posyandu --}}
    @php
        // grafik donat hanya digambar kalau sudah ada data pemeriksaan
        $adaData = array_sum($donatStunting) > 0;
        // tinggi grafik per posyandu menyesuaikan jumlah posyandu
        $tinggiPosyandu = max(220, count($grafikPosyandu['label']) * 42 + 70);
    @endphp

    <div class="row">
        {{-- donat status stunting --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sebaran Status Stunting</h3>
                </div>
                <div class="card-body">
                    @if ($adaData)
                        <div style="position: relative; height: 260px;"><canvas id="donatStunting"></canvas></div>
                    @else
                        <div class="text-center text-muted py-5">Belum ada data pemeriksaan.</div>
                    @endif
                    <small class="text-muted">Dihitung dari pemeriksaan terbaru tiap anak.</small>
                </div>
            </div>
        </div>

        {{-- donat status gizi --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sebaran Status Gizi</h3>
                </div>
                <div class="card-body">
                    @if ($adaData)
                        <div style="position: relative; height: 260px;"><canvas id="donatGizi"></canvas></div>
                    @else
                        <div class="text-center text-muted py-5">Belum ada data pemeriksaan.</div>
                    @endif
                    <small class="text-muted">Dihitung dari pemeriksaan terbaru tiap anak.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- tren 6 bulan terakhir --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tren 6 Bulan Terakhir</h3>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;"><canvas id="trenBulan"></canvas></div>
                    <small class="text-muted">Jumlah pemeriksaan tiap bulan berdasarkan status stunting.</small>
                </div>
            </div>
        </div>

        {{-- perbandingan antar posyandu --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Perbandingan Antar Posyandu</h3>
                </div>
                <div class="card-body">
                    @if (count($grafikPosyandu['label']) > 0)
                        <div style="position: relative; height: {{ $tinggiPosyandu }}px;"><canvas
                                id="perPosyandu"></canvas></div>
                    @else
                        <div class="text-center text-muted py-5">Belum ada data pemeriksaan.</div>
                    @endif
                    <small class="text-muted">Jumlah anak per status stunting (pemeriksaan terbaru). Posyandu dengan anak
                        stunting terbanyak di atas.</small>
                </div>
            </div>
        </div>
    </div>

    {{-- dua tabel kerja --}}
    <div class="row">
        {{-- lima anak dengan skor tertinggi yang belum ditindaklanjuti --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Prioritas Tindak Lanjut</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr class="text-nowrap">
                                <th>Nama Anak</th>
                                <th>Usia (bln)</th>
                                <th>Posyandu</th>
                                <th>Orang Tua</th>
                                <th>Skor</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($prioritas as $p)
                                <tr>
                                    <td>{{ $p->anak->nama }}</td>
                                    <td>{{ number_format($p->usia_bulan, 1, ',', '') }}</td>
                                    <td>{{ $p->anak->posyandu ? $p->anak->posyandu->user->name : '-' }}</td>
                                    <td>
                                        {{ $p->anak->orangTua->nama_lengkap }}
                                        <br><small class="text-muted">{{ $p->anak->orangTua->no_hp }}</small>
                                    </td>
                                    <td><b>{{ number_format($p->skor, 1, ',', '') }}</b></td>
                                    <td>
                                        <a href="{{ route('admin.bantuan.detail', $p->anak_id) }}"
                                            class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Tidak ada anak dengan skor minimal {{ $skorMin }} yang belum ditindaklanjuti.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.bantuan.index', ['min' => $skorMin]) }}">Lihat semua calon penerima
                        bantuan</a>
                    <span class="text-muted ml-2">(skor minimal {{ $skorMin }}, paling banyak 5 anak di sini)</span>
                </div>
            </div>
        </div>

        {{-- posyandu yang belum melengkapi profil --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Posyandu Belum Melengkapi Profil</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama Posyandu</th>
                                <th>Email Akun</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($belumLengkap as $u)
                                <tr>
                                    <td>{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Semua posyandu sudah melengkapi profil.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted">
                    Posyandu yang profilnya belum lengkap tidak muncul di pilihan orang tua.
                    Ingatkan mereka untuk mengisi profil.
                </div>
            </div>
        </div>
    </div>
@stop

{{-- grafik memakai Chart.js, dimuat dari internet (versi dikunci 4.4.1) --}}
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <script>
        // warna: merah = stunting/kurang, kuning = berisiko/lebih, hijau = normal
        const merah = '#dc3545',
            kuning = '#ffc107',
            hijau = '#28a745';

        // data donat dari controller
        const donatStunting = {
            labels: @json(array_keys($donatStunting)),
            data: @json(array_values($donatStunting))
        };
        const donatGizi = {
            labels: @json(array_keys($donatGizi)),
            data: @json(array_values($donatGizi))
        };

        // data tren dan perbandingan posyandu dari controller
        const tren = @json($tren);
        const pos = @json($grafikPosyandu);

        // grafik donat status stunting (digambar hanya kalau kotaknya ada)
        if (document.getElementById('donatStunting')) {
            new Chart(document.getElementById('donatStunting'), {
                type: 'doughnut',
                data: {
                    labels: donatStunting.labels,
                    datasets: [{
                        data: donatStunting.data,
                        backgroundColor: [merah, kuning, hijau]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // grafik donat status gizi
        if (document.getElementById('donatGizi')) {
            new Chart(document.getElementById('donatGizi'), {
                type: 'doughnut',
                data: {
                    labels: donatGizi.labels,
                    datasets: [{
                        data: donatGizi.data,
                        backgroundColor: [merah, kuning, hijau]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // grafik tren: batang bertumpuk per bulan
        new Chart(document.getElementById('trenBulan'), {
            type: 'bar',
            data: {
                labels: tren.label,
                datasets: [{
                        label: 'Stunting',
                        data: tren.stunting,
                        backgroundColor: merah
                    },
                    {
                        label: 'Berisiko',
                        data: tren.berisiko,
                        backgroundColor: kuning
                    },
                    {
                        label: 'Normal',
                        data: tren.normal,
                        backgroundColor: hijau
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Jumlah pemeriksaan'
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

        // grafik perbandingan antar posyandu: batang mendatar bertumpuk
        if (document.getElementById('perPosyandu')) {
            new Chart(document.getElementById('perPosyandu'), {
                type: 'bar',
                data: {
                    labels: pos.label,
                    datasets: [{
                            label: 'Stunting',
                            data: pos.stunting,
                            backgroundColor: merah
                        },
                        {
                            label: 'Berisiko',
                            data: pos.berisiko,
                            backgroundColor: kuning
                        },
                        {
                            label: 'Normal',
                            data: pos.normal,
                            backgroundColor: hijau
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            stacked: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
@stop
