{{-- halaman daftar posyandu (khusus admin): daftar ringkas, informasi lengkap ada di halaman Detail --}}
@extends('adminlte::page')

@section('title', 'Kelola Posyandu')

@section('content_header')
    <h1>Kelola Posyandu</h1>
@stop

@section('content')
    {{-- pesan sukses setelah menambah posyandu --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.posyandu.create') }}" class="btn btn-primary">Tambah Posyandu</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Posyandu</th>
                        <th>Email</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada posyandu, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $d)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $d->user->name }}</td>
                            <td>{{ $d->user->email }}</td>
                            <td>{{ $d->user->created_at->format('d-m-Y') }}</td>
                            <td>
                                <a href="{{ route('admin.posyandu.detail', $d->user->id) }}"
                                    class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada akun posyandu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted">
            Klik Detail untuk melihat biodata, status profil, dan daftar anak tiap posyandu.
        </div>
    </div>
@stop
