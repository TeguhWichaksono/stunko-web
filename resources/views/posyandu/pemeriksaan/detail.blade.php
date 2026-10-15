{{-- halaman detail anak (khusus posyandu): biodata anak, biodata orang tua, dan riwayat pemeriksaan --}}
@extends('adminlte::page')

@section('title', 'Detail Anak')

@section('content_header')
    <h1>Detail Anak</h1>
@stop

@section('content')
    {{-- hitung usia anak sekarang dalam tahun dan bulan --}}
    @php
        $bulanLahir = $anak->tanggal_lahir->diffInMonths(now());
    @endphp

    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>

        {{-- tombol Periksa mati kalau bulan ini sudah diperiksa --}}
        @if ($sudahBulanIni)
            <button class="btn btn-primary" disabled>Sudah diperiksa bulan ini</button>
        @else
            <a href="{{ route('posyandu.pemeriksaan.create', $anak->id) }}" class="btn btn-primary">Periksa Sekarang</a>
        @endif
    </div>

    <div class="row">
        {{-- biodata anak --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Biodata Anak</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px">Nama</th>
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
                            <th>Tempat, Tanggal Lahir</th>
                            <td>{{ $anak->tempat_lahir }}, {{ $anak->tanggal_lahir->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Usia Sekarang</th>
                            <td>{{ intdiv($bulanLahir, 12) }} tahun {{ $bulanLahir % 12 }} bulan</td>
                        </tr>
                        <tr>
                            <th>Berat Lahir</th>
                            <td>{{ $anak->berat_lahir ? ($anak->berat_lahir + 0) . ' kg' : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tinggi Lahir</th>
                            <td>{{ $anak->tinggi_lahir ? ($anak->tinggi_lahir + 0) . ' cm' : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- biodata orang tua --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Biodata Orang Tua</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px">Nama Lengkap</th>
                            <td>{{ $anak->orangTua->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <th>NIK</th>
                            <td>{{ $anak->orangTua->nik }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $anak->orangTua->no_hp }}</td>
                        </tr>
                        <tr>
                            <th>Pekerjaan</th>
                            <td>{{ $anak->orangTua->pekerjaan }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $anak->orangTua->alamat }}</td>
                        </tr>
                        <tr>
                            <th>RT / RW</th>
                            <td>{{ $anak->orangTua->rt }} / {{ $anak->orangTua->rw }}</td>
                        </tr>
                        <tr>
                            <th>Kelurahan</th>
                            <td>{{ $anak->orangTua->kelurahan }}</td>
                        </tr>
                        <tr>
                            <th>Email Akun</th>
                            <td>{{ $anak->orangTua->user->email }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- riwayat pemeriksaan anak ini --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pemeriksaan ({{ $riwayat->count() }} kali)</h3>
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
                    {{-- kalau belum pernah diperiksa, tampilkan tulisan kosong --}}
                    @forelse ($riwayat as $p)
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
                            <td class="text-nowrap {{ $p->z_bb_u !== null && $p->z_bb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_u !== null ? number_format($p->z_bb_u, 2, ',', '.') : '-' }}
                            </td>

                            {{-- TB/U: kategori lalu angka z-score --}}
                            <td class="text-nowrap">{{ data_get($p->hasil_api, 'kategori_zscore.tb_u') ?? '-' }}</td>
                            <td class="text-nowrap {{ $p->z_tb_u !== null && $p->z_tb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_tb_u !== null ? number_format($p->z_tb_u, 2, ',', '.') : '-' }}
                            </td>

                            {{-- BB/TB: kategori lalu angka z-score --}}
                            <td class="text-nowrap">{{ data_get($p->hasil_api, 'kategori_zscore.bb_tb') ?? '-' }}</td>
                            <td class="text-nowrap {{ $p->z_bb_tb !== null && $p->z_bb_tb < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_tb !== null ? number_format($p->z_bb_tb, 2, ',', '.') : '-' }}
                            </td>

                            {{-- status stunting (hasil ML) lalu keterangannya --}}
                            <td>
                                @if ($p->status_stunting == 'Stunting')
                                    <span class="badge badge-danger">Stunting</span>
                                @elseif ($p->status_stunting == 'Berisiko')
                                    <span class="badge badge-warning">Berisiko</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td style="min-width: 230px"><small>{{ data_get($p->hasil_api, 'penjelasan.stunting') ?? '-' }}</small></td>

                            {{-- status gizi (hasil ML) lalu keterangannya --}}
                            <td>
                                @if ($p->status_gizi == 'Gizi Kurang')
                                    <span class="badge badge-danger">Gizi Kurang</span>
                                @elseif ($p->status_gizi == 'Gizi Lebih')
                                    <span class="badge badge-warning">Gizi Lebih</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td style="min-width: 230px"><small>{{ data_get($p->hasil_api, 'penjelasan.gizi') ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center">Anak ini belum pernah diperiksa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
