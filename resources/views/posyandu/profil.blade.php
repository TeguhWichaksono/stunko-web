{{-- halaman profil posyandu: tampil data saja, form muncul kalau tombol Edit ditekan --}}
@extends('adminlte::page')

@section('title', 'Profil Posyandu')

@section('content_header')
    <h1>Profil Posyandu</h1>
@stop

@section('content')
    {{-- pesan sukses setelah menyimpan --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    {{-- peringatan kalau profil belum lengkap --}}
    @if (!$profil->lengkap())
        <div class="alert alert-warning">
            Profil belum lengkap. Lengkapi semua isian di bawah supaya posyandu Anda
            muncul di daftar pilihan orang tua.
        </div>
    @endif

    <div class="card">
        @if ($modeEdit)
            {{-- MODE EDIT: tampilkan form --}}
            <form action="{{ route('posyandu.profil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Kepala Posyandu</label>
                        <input type="text" name="nama_kepala"
                               value="{{ old('nama_kepala', $profil->nama_kepala) }}"
                               class="form-control @error('nama_kepala') is-invalid @enderror">
                        @error('nama_kepala')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" name="no_hp"
                               value="{{ old('no_hp', $profil->no_hp) }}"
                               class="form-control @error('no_hp') is-invalid @enderror">
                        @error('no_hp')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kelurahan</label>
                        <input type="text" name="kelurahan"
                               value="{{ old('kelurahan', $profil->kelurahan) }}"
                               class="form-control @error('kelurahan') is-invalid @enderror">
                        @error('kelurahan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Wilayah yang Dicakup</label>
                        <input type="text" name="wilayah"
                               value="{{ old('wilayah', $profil->wilayah) }}"
                               placeholder="Contoh: RW 01 - RW 03"
                               class="form-control @error('wilayah') is-invalid @enderror">
                        @error('wilayah')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Alamat Posyandu</label>
                        <textarea name="alamat" rows="3"
                                  class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $profil->alamat) }}</textarea>
                        @error('alamat')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>

                    {{-- tombol Batal hanya muncul kalau profil sudah pernah lengkap --}}
                    @if ($profil->lengkap())
                        <a href="{{ route('posyandu.profil.edit') }}" class="btn btn-secondary">Batal</a>
                    @endif
                </div>
            </form>
        @else
            {{-- MODE LIHAT: tampilkan data saja, tidak bisa diubah --}}
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 220px">Nama Kepala Posyandu</th>
                        <td>{{ $profil->nama_kepala }}</td>
                    </tr>
                    <tr>
                        <th>No. HP</th>
                        <td>{{ $profil->no_hp }}</td>
                    </tr>
                    <tr>
                        <th>Kelurahan</th>
                        <td>{{ $profil->kelurahan }}</td>
                    </tr>
                    <tr>
                        <th>Wilayah yang Dicakup</th>
                        <td>{{ $profil->wilayah }}</td>
                    </tr>
                    <tr>
                        <th>Alamat Posyandu</th>
                        <td>{{ $profil->alamat }}</td>
                    </tr>
                </table>
            </div>

            <div class="card-footer">
                {{-- tombol Edit membuka halaman yang sama dengan ?edit=1 --}}
                <a href="{{ route('posyandu.profil.edit', ['edit' => 1]) }}" class="btn btn-warning">Edit</a>
            </div>
        @endif
    </div>
@stop
