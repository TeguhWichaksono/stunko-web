{{-- form jenis bantuan: dipakai untuk tambah baru dan untuk mengubah data (khusus admin) --}}
@extends('adminlte::page')

@section('title', $jenis->exists ? 'Ubah Jenis Bantuan' : 'Tambah Jenis Bantuan')

@section('content_header')
    <h1>{{ $jenis->exists ? 'Ubah Jenis Bantuan' : 'Tambah Jenis Bantuan' }}</h1>
@stop

@section('content')
    <div class="card">
        {{-- kalau sudah ada di database, form dikirim ke update; kalau belum, ke store --}}
        <form
            action="{{ $jenis->exists ? route('admin.jenis-bantuan.update', $jenis->id) : route('admin.jenis-bantuan.store') }}"
            method="POST">
            @csrf
            @if ($jenis->exists)
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="form-group">
                    <label>Nama Bantuan</label>
                    <input type="text" name="nama" value="{{ old('nama', $jenis->nama) }}"
                        class="form-control @error('nama') is-invalid @enderror">
                    @error('nama')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- rentang usia sasaran, usia minimal dan maksimal berdampingan --}}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Usia Minimal (bulan)</label>
                        <input type="number" name="usia_min" min="0" max="60"
                            value="{{ old('usia_min', $jenis->usia_min) }}"
                            class="form-control @error('usia_min') is-invalid @enderror">
                        @error('usia_min')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Usia Maksimal (bulan)</label>
                        <input type="number" name="usia_maks" min="0" max="60"
                            value="{{ old('usia_maks', $jenis->usia_maks) }}"
                            class="form-control @error('usia_maks') is-invalid @enderror">
                        @error('usia_maks')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Keterangan <small class="text-muted">- boleh kosong</small></label>
                    <textarea name="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $jenis->keterangan) }}</textarea>
                    @error('keterangan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.jenis-bantuan.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop
