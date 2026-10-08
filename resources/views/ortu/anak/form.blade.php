{{-- form anak: dipakai untuk tambah anak baru dan untuk mengubah data anak --}}
@extends('adminlte::page')

@section('title', $anak->exists ? 'Ubah Data Anak' : 'Tambah Anak')

@section('content_header')
    <h1>{{ $anak->exists ? 'Ubah Data Anak' : 'Tambah Anak' }}</h1>
@stop

@section('content')
    <div class="card">
        {{-- kalau anak sudah ada di database, form dikirim ke update; kalau belum, ke store --}}
        <form action="{{ $anak->exists ? route('ortu.anak.update', $anak->id) : route('ortu.anak.store') }}" method="POST">
            @csrf
            @if ($anak->exists)
                @method('PUT')
            @endif

            <div class="card-body">
                <div class="form-group">
                    <label>Nama Anak</label>
                    <input type="text" name="nama" value="{{ old('nama', $anak->nama) }}"
                        class="form-control @error('nama') is-invalid @enderror">
                    @error('nama')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>NIK Anak (16 digit)</label>
                    <input type="text" name="nik" maxlength="16" value="{{ old('nik', $anak->nik) }}"
                        class="form-control @error('nik') is-invalid @enderror">
                    @error('nik')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('jenis_kelamin', $anak->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                            Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $anak->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                            Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $anak->tempat_lahir) }}"
                        class="form-control @error('tempat_lahir') is-invalid @enderror">
                    @error('tempat_lahir')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" max="{{ date('Y-m-d') }}"
                        value="{{ old('tanggal_lahir', $anak->tanggal_lahir?->format('Y-m-d')) }}"
                        class="form-control @error('tanggal_lahir') is-invalid @enderror">
                    @error('tanggal_lahir')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- berat dan tinggi lahir boleh dikosongkan --}}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Berat Lahir (kg) <small class="text-muted">- boleh kosong</small></label>
                        <input type="number" step="0.01" name="berat_lahir"
                            value="{{ old('berat_lahir', $anak->berat_lahir) }}"
                            class="form-control @error('berat_lahir') is-invalid @enderror">
                        @error('berat_lahir')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Tinggi Lahir (cm) <small class="text-muted">- boleh kosong</small></label>
                        <input type="number" step="0.1" name="tinggi_lahir"
                            value="{{ old('tinggi_lahir', $anak->tinggi_lahir) }}"
                            class="form-control @error('tinggi_lahir') is-invalid @enderror">
                        @error('tinggi_lahir')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('ortu.anak.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop
