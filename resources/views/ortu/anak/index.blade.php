{{-- halaman daftar anak milik orang tua --}}
@extends('adminlte::page')

@section('title', 'Data Anak')

@section('content_header')
    <h1>Data Anak</h1>
@stop

@section('content')
    {{-- pesan sukses setelah menambah atau mengubah data anak --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    {{-- kalau biodata orang tua belum lengkap, anak belum bisa ditambah --}}
    @if (!$ortu->lengkap())
        <div class="alert alert-warning">
            Lengkapi <a href="{{ route('ortu.biodata.edit') }}">Biodata Orang Tua</a>
            dulu sebelum menambah data anak.
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            {{-- tombol Tambah Anak aktif hanya kalau biodata sudah lengkap --}}
            @if ($ortu->lengkap())
                <a href="{{ route('ortu.anak.create') }}" class="btn btn-primary">Tambah Anak</a>
            @else
                <button class="btn btn-primary" disabled>Tambah Anak</button>
            @endif
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada anak, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $a)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->nama }}</td>
                            <td>{{ $a->nik }}</td>
                            <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td>{{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                            <td>
                                <a href="{{ route('ortu.anak.edit', $a->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data anak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
