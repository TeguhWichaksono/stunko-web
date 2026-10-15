{{-- halaman informasi anak (khusus posyandu): daftar semua anak, ada kolom cari --}}
@extends('adminlte::page')

@section('title', 'Informasi Anak')

@section('content_header')
    <h1>Informasi Anak</h1>
@stop

@section('content')
    {{-- form cari: dikirim lewat alamat (GET) --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('posyandu.informasi.index') }}" method="GET" class="form-inline">
                <input type="text" name="cari" value="{{ $cari }}"
                       class="form-control mr-2 mb-2" style="min-width: 300px"
                       placeholder="Cari nama anak, NIK anak, atau nama orang tua">

                <button type="submit" class="btn btn-primary mr-2 mb-2">Cari</button>
                <a href="{{ route('posyandu.informasi.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menampilkan <b>{{ $daftar->count() }}</b> anak</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>NIK</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Usia</th>
                        <th>Orang Tua</th>
                        <th>No. HP</th>
                        <th>Jumlah Pemeriksaan</th>
                        <th>Pemeriksaan Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau tidak ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $a)
                        @php
                            // usia anak sekarang dalam tahun dan bulan
                            $bulanLahir = $a->tanggal_lahir->diffInMonths(now());
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->nama }}</td>
                            <td>{{ $a->nik }}</td>
                            <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="text-nowrap">{{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                            <td class="text-nowrap">{{ intdiv($bulanLahir, 12) }} th {{ $bulanLahir % 12 }} bln</td>
                            <td>{{ $a->orangTua->nama_lengkap }}</td>
                            <td>{{ $a->orangTua->no_hp }}</td>
                            <td>{{ $a->total_periksa }}</td>
                            <td class="text-nowrap">
                                {{-- strip kalau belum pernah diperiksa --}}
                                @if ($a->terakhir)
                                    {{ \Illuminate\Support\Carbon::parse($a->terakhir)->format('d-m-Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('posyandu.anak.detail', $a->id) }}" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada anak yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
