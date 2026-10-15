{{-- dashboard posyandu: kartu ringkasan dan dua tabel kerja harian --}}
@extends('adminlte::page')

@section('title', 'Dashboard Posyandu')

@section('content_header')
    <h1>Dashboard Posyandu</h1>
@stop

@section('content')
    <p class="text-muted">
        Selamat datang, {{ Auth::user()->name }}. Data bulan {{ now()->locale('id')->translatedFormat('F Y') }}.
        Angka status dihitung dari pemeriksaan terbaru tiap anak.
    </p>

    {{-- kartu ringkasan --}}
    <div class="row">
        {{-- anak terdaftar --}}
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-child"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Anak Terdaftar</span>
                    <span class="info-box-number">{{ $jumlahAnak }}</span>
                </div>
            </div>
        </div>

        {{-- sudah diperiksa bulan ini, lengkap dengan batang persen --}}
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-stethoscope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Diperiksa Bulan Ini</span>
                    <span class="info-box-number">{{ $sudahBulanIni }} dari {{ $jumlahAnak }}</span>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $persenBulanIni }}%"></div>
                    </div>
                    <span class="progress-description">{{ $persenBulanIni }}% sudah diperiksa</span>
                </div>
            </div>
        </div>

        {{-- jumlah anak stunting --}}
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-ruler-vertical"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Stunting</span>
                    <span class="info-box-number">{{ $donatStunting['Stunting'] }}</span>
                </div>
            </div>
        </div>

        {{-- jumlah anak gizi kurang --}}
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-weight"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Gizi Kurang</span>
                    <span class="info-box-number">{{ $donatGizi['Gizi Kurang'] }}</span>
                </div>
            </div>
        </div>

        {{-- pengajuan yang menunggu persetujuan, dengan tautan ke halamannya --}}
        <div class="col-md-4 col-lg">
            <a href="{{ route('posyandu.pengajuan.index') }}" class="text-dark">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pengajuan Menunggu</span>
                        <span class="info-box-number">{{ $pengajuanMenunggu }}</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- dua tabel kerja harian --}}
    <div class="row">
        {{-- anak yang belum diperiksa bulan ini --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Belum Diperiksa Bulan Ini</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama Anak</th>
                                <th>Usia</th>
                                <th>Orang Tua</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($belumDiperiksa as $a)
                                @php
                                    // usia anak sekarang dalam tahun dan bulan
                                    $bulanLahir = $a->tanggal_lahir->diffInMonths(now());
                                @endphp
                                <tr>
                                    <td>{{ $a->nama }}</td>
                                    <td class="text-nowrap">{{ intdiv($bulanLahir, 12) }} th {{ $bulanLahir % 12 }} bln
                                    </td>
                                    <td>{{ $a->orangTua->nama_lengkap }}</td>
                                    <td>
                                        <a href="{{ route('posyandu.pemeriksaan.create', $a->id) }}"
                                            class="btn btn-sm btn-primary">Periksa</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        @if ($jumlahAnak == 0)
                                            Belum ada anak terdaftar.
                                        @else
                                            Semua anak sudah diperiksa bulan ini.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted">
                    Menampilkan paling banyak 10 anak. Daftar lengkap ada di menu Pemeriksaan Anak.
                </div>
            </div>
        </div>

        {{-- anak yang perlu perhatian: pemeriksaan terakhirnya Stunting atau Gizi Kurang --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Perlu Perhatian</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama Anak</th>
                                <th>Terakhir Diukur</th>
                                <th>Stunting</th>
                                <th>Gizi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perluPerhatian as $p)
                                <tr>
                                    <td>{{ $p->anak->nama }}</td>
                                    <td class="text-nowrap">{{ $p->tanggal_ukur->format('d-m-Y') }}</td>
                                    <td>
                                        @if ($p->status_stunting == 'Stunting')
                                            <span class="badge badge-danger">Stunting</span>
                                        @elseif ($p->status_stunting == 'Berisiko')
                                            <span class="badge badge-warning">Berisiko</span>
                                        @else
                                            <span class="badge badge-success">Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($p->status_gizi == 'Gizi Kurang')
                                            <span class="badge badge-danger">Gizi Kurang</span>
                                        @elseif ($p->status_gizi == 'Gizi Lebih')
                                            <span class="badge badge-warning">Gizi Lebih</span>
                                        @else
                                            <span class="badge badge-success">Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('posyandu.anak.detail', $p->anak_id) }}"
                                            class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada anak yang perlu perhatian khusus.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted">
                    Anak yang pemeriksaan terakhirnya Stunting atau Gizi Kurang (paling banyak 10).
                </div>
            </div>
        </div>
    </div>
@stop
