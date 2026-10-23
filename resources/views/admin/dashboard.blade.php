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
