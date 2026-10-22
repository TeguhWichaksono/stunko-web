{{-- halaman daftar jenis bantuan (khusus admin) --}}
@extends('adminlte::page')

@section('title', 'Jenis Bantuan')

@section('content_header')
    <h1>Jenis Bantuan</h1>
@stop

@section('content')
    {{-- pesan sukses setelah menambah atau mengubah --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.jenis-bantuan.create') }}" class="btn btn-primary">Tambah Jenis Bantuan</a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Nama Bantuan</th>
                        <th>Rentang Usia Sasaran</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $j)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><b>{{ $j->nama }}</b></td>
                            <td class="text-nowrap">{{ $j->usia_min }} - {{ $j->usia_maks }} bulan</td>
                            <td style="min-width: 300px"><small>{{ $j->keterangan ?: '-' }}</small></td>
                            <td>
                                <a href="{{ route('admin.jenis-bantuan.edit', $j->id) }}"
                                    class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada jenis bantuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted">
            Rentang usia dipakai sistem untuk menyarankan jenis bantuan yang sesuai dengan usia anak.
            Bantuan yang sudah pernah dipakai tidak bisa dihapus supaya riwayat keputusan tetap utuh.
        </div>
    </div>
@stop
