{{-- form tambah akun posyandu (khusus admin) --}}
@extends('adminlte::page')

@section('title', 'Tambah Posyandu')

@section('content_header')
    <h1>Tambah Posyandu</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.posyandu.store') }}" method="POST">
            {{-- @csrf wajib ada di setiap form, kalau tidak akan error 419 --}}
            @csrf

            <div class="card-body">
                {{-- nama posyandu --}}
                <div class="form-group">
                    <label>Nama Posyandu</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- email untuk login --}}
                <div class="form-group">
                    <label>Email (untuk login)</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- password --}}
                <div class="form-group">
                    <label>Password (minimal 8 karakter)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- ulangi password --}}
                <div class="form-group">
                    <label>Ulangi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.posyandu.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop
