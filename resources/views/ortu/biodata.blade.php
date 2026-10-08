{{-- halaman biodata orang tua: tampil data saja, form muncul kalau tombol Edit ditekan --}}
@extends('adminlte::page')

@section('title', 'Biodata Orang Tua')

@section('content_header')
    <h1>Biodata Orang Tua</h1>
@stop

@section('content')
    {{-- pesan sukses setelah menyimpan --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    {{-- peringatan kalau biodata belum lengkap --}}
    @if (!$ortu->lengkap())
        <div class="alert alert-warning">
            Biodata belum lengkap. Lengkapi semua isian di bawah untuk melanjutkan.
        </div>
    @endif

    <div class="card">
        @if ($modeEdit)
            {{-- MODE EDIT: tampilkan form --}}
            <form action="{{ route('ortu.biodata.update') }}" method="POST">
                {{-- @csrf wajib ada di setiap form, @method('PUT') karena route-nya memakai PUT --}}
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $ortu->nama_lengkap) }}"
                            class="form-control @error('nama_lengkap') is-invalid @enderror">
                        @error('nama_lengkap')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>NIK (16 digit)</label>
                        <input type="text" name="nik" maxlength="16" value="{{ old('nik', $ortu->nik) }}"
                            class="form-control @error('nik') is-invalid @enderror">
                        @error('nik')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp', $ortu->no_hp) }}"
                            class="form-control @error('no_hp') is-invalid @enderror">
                        @error('no_hp')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $ortu->pekerjaan) }}"
                            class="form-control @error('pekerjaan') is-invalid @enderror">
                        @error('pekerjaan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $ortu->alamat) }}</textarea>
                        @error('alamat')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- RT dan RW berdampingan --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>RT</label>
                            <input type="text" name="rt" maxlength="3" value="{{ old('rt', $ortu->rt) }}"
                                class="form-control @error('rt') is-invalid @enderror">
                            @error('rt')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>RW</label>
                            <input type="text" name="rw" maxlength="3" value="{{ old('rw', $ortu->rw) }}"
                                class="form-control @error('rw') is-invalid @enderror">
                            @error('rw')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kelurahan</label>
                        <input type="text" name="kelurahan" value="{{ old('kelurahan', $ortu->kelurahan) }}"
                            class="form-control @error('kelurahan') is-invalid @enderror">
                        @error('kelurahan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>

                    {{-- tombol Batal hanya muncul kalau biodata sudah pernah lengkap --}}
                    @if ($ortu->lengkap())
                        <a href="{{ route('ortu.biodata.edit') }}" class="btn btn-secondary">Batal</a>
                    @endif
                </div>
            </form>
        @else
            {{-- MODE LIHAT: tampilkan data saja, tidak bisa diubah --}}
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 220px">Nama Lengkap</th>
                        <td>{{ $ortu->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td>{{ $ortu->nik }}</td>
                    </tr>
                    <tr>
                        <th>No. HP</th>
                        <td>{{ $ortu->no_hp }}</td>
                    </tr>
                    <tr>
                        <th>Pekerjaan</th>
                        <td>{{ $ortu->pekerjaan }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>{{ $ortu->alamat }}</td>
                    </tr>
                    <tr>
                        <th>RT / RW</th>
                        <td>{{ $ortu->rt }} / {{ $ortu->rw }}</td>
                    </tr>
                    <tr>
                        <th>Kelurahan</th>
                        <td>{{ $ortu->kelurahan }}</td>
                    </tr>
                </table>
            </div>

            <div class="card-footer">
                {{-- tombol Edit membuka halaman yang sama dengan ?edit=1 --}}
                <a href="{{ route('ortu.biodata.edit', ['edit' => 1]) }}" class="btn btn-warning">Edit</a>
            </div>
        @endif
    </div>
@stop
