{{-- halaman daftar akun posyandu (khusus admin) --}}
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
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $p->created_at->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada akun posyandu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

