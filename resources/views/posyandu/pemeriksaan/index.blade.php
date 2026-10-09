{{-- halaman daftar anak yang siap diperiksa (khusus posyandu) --}}
@extends('adminlte::page')

@section('title', 'Pemeriksaan Anak')

@section('content_header')
    <h1>Pemeriksaan Anak</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Orang Tua</th>
                        <th>Pemeriksaan Terakhir</th>
                        <th>Bulan Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada anak, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $a)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->nama }}</td>
                            <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td>{{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                            <td>{{ $a->orangTua->nama_lengkap }}</td>
                            <td>
                                {{-- tanggal pemeriksaan terakhir, strip kalau belum pernah diperiksa --}}
                                @if ($a->terakhir)
                                    {{ \Illuminate\Support\Carbon::parse($a->terakhir)->format('d-m-Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($a->bulan_ini)
                                    <span class="badge badge-success">Sudah diperiksa</span>
                                @else
                                    <span class="badge badge-secondary">Belum</span>
                                @endif
                            </td>
                            <td>
                                {{-- tombol Periksa mati kalau bulan ini sudah diperiksa --}}
                                @if ($a->bulan_ini)
                                    <button class="btn btn-sm btn-primary" disabled>Periksa</button>
                                @else
                                    <a href="{{ route('posyandu.pemeriksaan.create', $a->id) }}"
                                       class="btn btn-sm btn-primary">Periksa</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                Belum ada anak. Anak muncul di sini setelah pengajuan orang tuanya diterima.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
