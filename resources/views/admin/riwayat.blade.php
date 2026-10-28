{{-- riwayat pemeriksaan seluruh posyandu (khusus admin): urutan kolom mengikuti format Excel puskesmas --}}
@extends('adminlte::page')

@section('title', 'Riwayat Pemeriksaan')

@section('content_header')
    <h1>Riwayat Pemeriksaan</h1>
@stop

{{-- kolom Nama tetap terlihat di kiri saat tabel digeser ke samping --}}
@section('css')
    <style>
        .kolom-nama {
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #fff;
            box-shadow: inset -1px 0 0 #dee2e6;
        }

        /* teks panjang (alamat dan keterangan) dipotong jadi satu baris supaya baris tabel tidak tinggi */
        .sel-ringkas {
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@stop

@section('content')
    {{-- wilayah dan puskesmas diambil dari config/stunko.php, sama untuk semua baris --}}
    @php
        $prov = config('stunko.provinsi');
        $kab = config('stunko.kabupaten');
        $kec = config('stunko.kecamatan');
        $pusk = config('stunko.puskesmas');
    @endphp

    {{-- form filter: dikirim lewat alamat (GET) --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.riwayat.index') }}" method="GET" class="form-inline">
                {{-- pilih bulan pemeriksaan --}}
                <label class="mr-2 mb-2">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" class="form-control mr-3 mb-2">

                {{-- pilih posyandu --}}
                <label class="mr-2 mb-2">Posyandu</label>
                <select name="posyandu" class="form-control mr-3 mb-2">
                    <option value="">Semua posyandu</option>
                    @foreach ($daftarPosyandu as $ps)
                        <option value="{{ $ps->id }}" {{ $posyanduId == $ps->id ? 'selected' : '' }}>
                            {{ $ps->user->name }}
                        </option>
                    @endforeach
                </select>

                {{-- cari nama anak atau nama orang tua --}}
                <input type="text" name="cari" value="{{ $cari }}" class="form-control mr-2 mb-2"
                    style="min-width: 240px" placeholder="Cari nama anak atau orang tua">

                <button type="submit" class="btn btn-primary mr-2 mb-2">Tampilkan</button>
                <a href="{{ route('admin.riwayat.index') }}" class="btn btn-secondary mr-2 mb-2">Reset</a>

                {{-- unduh Excel sesuai filter yang sedang dipakai di halaman ini --}}
                <a href="{{ route('admin.riwayat.unduh', request()->query()) }}" class="btn btn-success mb-2">
                    <i class="fas fa-file-excel"></i> Unduh Excel
                </a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menampilkan <b>{{ $daftar->count() }}</b> dari <b>{{ $total }}</b> data</span>
            <small class="text-muted ml-3">
                Urutan kolom sama dengan Excel puskesmas, hasil prediksi sistem ada di paling kanan.
                Z-Score merah = di bawah -2. Arahkan kursor ke teks yang terpotong untuk melihat selengkapnya.
            </small>
            @if ($total > $daftar->count())
                <div class="text-warning mt-1">
                    Tampilan dibatasi {{ $daftar->count() }} data terbaru. Persempit filter, atau unduh Excel untuk
                    semuanya.
                </div>
            @endif
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>NIK</th>
                        <th class="kolom-nama">Nama</th>
                        <th>JK</th>
                        <th>Tgl Lahir</th>
                        <th>BB Lahir</th>
                        <th>TB Lahir</th>
                        <th>Nama Ortu</th>
                        <th>Prov</th>
                        <th>Kab/Kota</th>
                        <th>Kec</th>
                        <th>Puskesmas</th>
                        <th>Desa/Kel</th>
                        <th>Posyandu</th>
                        <th>RT</th>
                        <th>RW</th>
                        <th>Alamat</th>
                        <th>Usia Saat Ukur</th>
                        <th>Tanggal Pengukuran</th>
                        <th>Berat</th>
                        <th>Tinggi</th>
                        <th>Cara Ukur</th>
                        <th>LiLA</th>
                        <th>BB/U</th>
                        <th>ZS BB/U</th>
                        <th>TB/U</th>
                        <th>ZS TB/U</th>
                        <th>BB/TB</th>
                        <th>ZS BB/TB</th>
                        <th>Status Stunting</th>
                        <th>Keterangan Stunting</th>
                        <th>Status Gizi</th>
                        <th>Keterangan Gizi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau tidak ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $p)
                        @php
                            // singkatan supaya penulisan di bawah lebih pendek
                            $a = $p->anak;
                            $o = $a->orangTua;
                        @endphp
                        <tr class="text-nowrap">
                            {{-- identitas anak dan orang tua --}}
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->nik }}</td>
                            <td class="kolom-nama">{{ $a->nama }}</td>
                            <td>{{ $a->jenis_kelamin }}</td>
                            <td>{{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                            <td>{{ $a->berat_lahir ? $a->berat_lahir + 0 : '-' }}</td>
                            <td>{{ $a->tinggi_lahir ? $a->tinggi_lahir + 0 : '-' }}</td>
                            <td>{{ $o->nama_lengkap }}</td>

                            {{-- wilayah dan posyandu --}}
                            <td>{{ $prov }}</td>
                            <td>{{ $kab }}</td>
                            <td>{{ $kec }}</td>
                            <td>{{ $pusk }}</td>
                            <td>{{ $o->kelurahan }}</td>
                            <td>{{ $p->posyandu->user->name ?? '-' }}</td>
                            <td>{{ $o->rt }}</td>
                            <td>{{ $o->rw }}</td>
                            <td class="sel-ringkas" title="{{ $o->alamat }}">{{ $o->alamat }}</td>

                            {{-- data pemeriksaan --}}
                            <td>{{ $p->usia_teks }}</td>
                            <td>{{ $p->tanggal_ukur->format('d-m-Y') }}</td>
                            <td>{{ $p->berat + 0 }}</td>
                            <td>{{ $p->tinggi + 0 }}</td>
                            <td>{{ $p->cara_ukur ? ucfirst($p->cara_ukur) : '-' }}</td>
                            <td>{{ $p->lila !== null ? $p->lila + 0 : '-' }}</td>

                            {{-- BB/U: kategori lalu angka z-score --}}
                            <td>{{ data_get($p->hasil_api, 'kategori_zscore.bb_u') ?? '-' }}</td>
                            <td class="{{ $p->z_bb_u !== null && $p->z_bb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_u !== null ? number_format($p->z_bb_u, 2, ',', '.') : '-' }}
                            </td>

                            {{-- TB/U: kategori lalu angka z-score --}}
                            <td>{{ data_get($p->hasil_api, 'kategori_zscore.tb_u') ?? '-' }}</td>
                            <td class="{{ $p->z_tb_u !== null && $p->z_tb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_tb_u !== null ? number_format($p->z_tb_u, 2, ',', '.') : '-' }}
                            </td>

                            {{-- BB/TB: kategori lalu angka z-score --}}
                            <td>{{ data_get($p->hasil_api, 'kategori_zscore.bb_tb') ?? '-' }}</td>
                            <td
                                class="{{ $p->z_bb_tb !== null && $p->z_bb_tb < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_tb !== null ? number_format($p->z_bb_tb, 2, ',', '.') : '-' }}
                            </td>

                            {{-- tambahan dari sistem: status stunting (hasil ML) lalu keterangannya --}}
                            <td>
                                @if ($p->status_stunting == 'Stunting')
                                    <span class="badge badge-danger">Stunting</span>
                                @elseif ($p->status_stunting == 'Berisiko')
                                    <span class="badge badge-warning">Berisiko</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td class="sel-ringkas" title="{{ data_get($p->hasil_api, 'penjelasan.stunting') }}">
                                <small>{{ data_get($p->hasil_api, 'penjelasan.stunting') ?? '-' }}</small>
                            </td>

                            {{-- tambahan dari sistem: status gizi (hasil ML) lalu keterangannya --}}
                            <td>
                                @if ($p->status_gizi == 'Gizi Kurang')
                                    <span class="badge badge-danger">Gizi Kurang</span>
                                @elseif ($p->status_gizi == 'Gizi Lebih')
                                    <span class="badge badge-warning">Gizi Lebih</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td class="sel-ringkas" title="{{ data_get($p->hasil_api, 'penjelasan.gizi') }}">
                                <small>{{ data_get($p->hasil_api, 'penjelasan.gizi') ?? '-' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="33" class="text-center">Tidak ada data yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
