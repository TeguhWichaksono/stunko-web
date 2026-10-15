{{-- halaman riwayat pemeriksaan (khusus posyandu): cari, pilih bulan, z-score, dan prediksi ML --}}
@extends('adminlte::page')

@section('title', 'Riwayat Pemeriksaan')

@section('content_header')
    <h1>Riwayat Pemeriksaan</h1>
@stop

@section('content')
    {{-- form cari: dikirim lewat alamat (GET), jadi hasilnya bisa di-refresh --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('posyandu.riwayat.index') }}" method="GET" class="form-inline">
                <input type="text" name="cari" value="{{ $cari }}" class="form-control mr-2 mb-2"
                    style="min-width: 260px" placeholder="Cari nama anak atau nama orang tua">

                <input type="month" name="bulan" value="{{ $bulan }}" class="form-control mr-2 mb-2">

                <button type="submit" class="btn btn-primary mr-2 mb-2">Cari</button>
                <a href="{{ route('posyandu.riwayat.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menampilkan <b>{{ $daftar->count() }}</b> data</span>
            <small class="text-muted ml-3">Z-Score merah = di bawah -2. Tanda "-" = pemeriksaan lama (belum ada
                z-score).</small>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    {{-- satu baris judul saja, tanpa kelompok --}}
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Tanggal Ukur</th>
                        <th>Nama Anak</th>
                        <th>Orang Tua</th>
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
                    {{-- kalau tidak ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">{{ $p->tanggal_ukur->format('d-m-Y') }}</td>
                            <td>{{ $p->anak->nama }}</td>
                            <td>{{ $p->anak->orangTua->nama_lengkap }}</td>
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
                            <td style="min-width: 230px">
                                <small>{{ data_get($p->hasil_api, 'penjelasan.stunting') ?? '-' }}</small></td>

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
                            <td style="min-width: 230px">
                                <small>{{ data_get($p->hasil_api, 'penjelasan.gizi') ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" class="text-center">Tidak ada data yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
