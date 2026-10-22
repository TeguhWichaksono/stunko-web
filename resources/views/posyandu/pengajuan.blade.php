{{-- halaman daftar pengajuan per anak (khusus posyandu): cari dan saring per status --}}
@extends('adminlte::page')

@section('title', 'Pengajuan Anak')

@section('content_header')
    <h1>Pengajuan Anak</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    {{-- jumlah anak per status, bisa diklik untuk menyaring --}}
    <div class="mb-3">
        <a href="{{ route('posyandu.pengajuan.index') }}"
           class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-dark' }}">
            Semua ({{ $hitung->sum() }})
        </a>
        <a href="{{ route('posyandu.pengajuan.index', ['status' => 'menunggu']) }}"
           class="btn btn-sm {{ $status == 'menunggu' ? 'btn-warning' : 'btn-outline-warning' }}">
            Menunggu ({{ $hitung->get('menunggu', 0) }})
        </a>
        <a href="{{ route('posyandu.pengajuan.index', ['status' => 'diterima']) }}"
           class="btn btn-sm {{ $status == 'diterima' ? 'btn-success' : 'btn-outline-success' }}">
            Diterima ({{ $hitung->get('diterima', 0) }})
        </a>
        <a href="{{ route('posyandu.pengajuan.index', ['status' => 'ditolak']) }}"
           class="btn btn-sm {{ $status == 'ditolak' ? 'btn-danger' : 'btn-outline-danger' }}">
            Ditolak ({{ $hitung->get('ditolak', 0) }})
        </a>
    </div>

    {{-- form cari: dikirim lewat alamat (GET), status yang dipilih ikut terbawa --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('posyandu.pengajuan.index') }}" method="GET" class="form-inline">
                @if ($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <input type="text" name="cari" value="{{ $cari }}"
                       class="form-control mr-2 mb-2" style="min-width: 300px"
                       placeholder="Cari nama anak, NIK anak, atau nama orang tua">

                <button type="submit" class="btn btn-primary mr-2 mb-2">Cari</button>
                <a href="{{ route('posyandu.pengajuan.index') }}" class="btn btn-secondary mb-2">Reset</a>
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
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Usia</th>
                        <th>Orang Tua</th>
                        <th>No. HP</th>
                        <th>RT / RW</th>
                        <th>Kelurahan</th>
                        <th>Status</th>
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
                            <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="text-nowrap">{{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                            <td class="text-nowrap">{{ intdiv($bulanLahir, 12) }} th {{ $bulanLahir % 12 }} bln</td>
                            <td>{{ $a->orangTua->nama_lengkap }}</td>
                            <td>{{ $a->orangTua->no_hp }}</td>
                            <td class="text-nowrap">{{ $a->orangTua->rt }} / {{ $a->orangTua->rw }}</td>
                            <td>{{ $a->orangTua->kelurahan }}</td>
                            <td>
                                @if ($a->status == 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($a->status == 'diterima')
                                    <span class="badge badge-success">Diterima</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                {{-- keputusan diambil di halaman detail setelah data dicek --}}
                                @if ($a->status == 'menunggu')
                                    <a href="{{ route('posyandu.pengajuan.detail', $a->id) }}"
                                       class="btn btn-sm btn-warning">Periksa &amp; Putuskan</a>
                                @else
                                    <a href="{{ route('posyandu.pengajuan.detail', $a->id) }}"
                                       class="btn btn-sm btn-info">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">Tidak ada pengajuan yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
