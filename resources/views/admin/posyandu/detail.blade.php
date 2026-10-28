{{-- detail satu posyandu (khusus admin): biodata, ringkasan angka, dan daftar anaknya --}}
@extends('adminlte::page')

@section('title', 'Detail Posyandu')

@section('content_header')
    <h1>Detail Posyandu: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.posyandu.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>

    {{-- peringatan kalau profil belum lengkap --}}
    @if (!$profil || !$profil->lengkap())
        <div class="alert alert-warning">
            Profil posyandu ini belum lengkap, jadi belum muncul di pilihan orang tua.
            Hubungi posyandu ({{ $user->email }}) untuk melengkapi profilnya.
        </div>
    @endif

    <div class="row">
        {{-- data akun --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Akun</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 140px">Nama</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $user->created_at->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Profil</th>
                            <td>
                                @if ($profil && $profil->lengkap())
                                    <span class="badge badge-success">Lengkap</span>
                                @else
                                    <span class="badge badge-warning">Belum lengkap</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- biodata posyandu --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Biodata Posyandu</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th style="width: 140px">Kelurahan</th>
                                    <td>{{ $profil->kelurahan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Wilayah</th>
                                    <td>{{ $profil->wilayah ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $profil->alamat ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th style="width: 140px">Kepala Posyandu</th>
                                    <td>{{ $profil->nama_kepala ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No. HP</th>
                                    <td>{{ $profil->no_hp ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ringkasan angka --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-child"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Anak Diterima</span>
                    <span class="info-box-number">{{ $ringkasan['diterima'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Menunggu Keputusan</span>
                    <span class="info-box-number">{{ $ringkasan['menunggu'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ditolak</span>
                    <span class="info-box-number">{{ $ringkasan['ditolak'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-stethoscope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Diperiksa Bulan Ini</span>
                    <span class="info-box-number">{{ $ringkasan['diperiksa'] }} dari {{ $ringkasan['diterima'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- daftar anak yang sudah diterima di posyandu ini --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Anak ({{ $anak->count() }})</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Jenis Kelamin</th>
                        <th>Usia</th>
                        <th>Orang Tua</th>
                        <th>Pemeriksaan Terakhir</th>
                        <th>Status Stunting</th>
                        <th>Status Gizi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada anak, tampilkan tulisan kosong --}}
                    @forelse ($anak as $a)
                        @php
                            // usia anak sekarang dalam tahun dan bulan, dan pemeriksaan terbarunya
                            $bulanLahir = $a->tanggal_lahir->diffInMonths(now());
                            $p = $terbaru->get($a->id);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->nama }}</td>
                            <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="text-nowrap">{{ intdiv($bulanLahir, 12) }} th {{ $bulanLahir % 12 }} bln</td>
                            <td>
                                {{ $a->orangTua->nama_lengkap }}
                                <br><small class="text-muted">{{ $a->orangTua->no_hp }}</small>
                            </td>

                            {{-- pemeriksaan terakhir, strip kalau belum pernah diperiksa --}}
                            <td class="text-nowrap">{{ $p ? $p->tanggal_ukur->format('d-m-Y') : '-' }}</td>
                            <td>
                                @if (!$p)
                                    -
                                @elseif ($p->status_stunting == 'Stunting')
                                    <span class="badge badge-danger">Stunting</span>
                                @elseif ($p->status_stunting == 'Berisiko')
                                    <span class="badge badge-warning">Berisiko</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td>
                                @if (!$p)
                                    -
                                @elseif ($p->status_gizi == 'Gizi Kurang')
                                    <span class="badge badge-danger">Gizi Kurang</span>
                                @elseif ($p->status_gizi == 'Gizi Lebih')
                                    <span class="badge badge-warning">Gizi Lebih</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>

                            {{-- detail anak hanya ada kalau anak sudah pernah diperiksa --}}
                            <td>
                                @if ($p)
                                    <a href="{{ route('admin.bantuan.detail', $a->id) }}"
                                        class="btn btn-sm btn-info">Detail</a>
                                @else
                                    <small class="text-muted">Belum diperiksa</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada anak yang diterima di posyandu ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
