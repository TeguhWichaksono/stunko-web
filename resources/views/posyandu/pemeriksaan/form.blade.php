{{-- form pemeriksaan satu anak (khusus posyandu) --}}
@extends('adminlte::page')

@section('title', 'Periksa Anak')

@section('content_header')
    <h1>Periksa Anak</h1>
@stop

@section('content')
    {{-- pesan gagal, misalnya FastAPI tidak menyala --}}
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    {{-- identitas anak yang diperiksa --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Anak</h3>
        </div>
        <div class="card-body">
            <table class="table table-borderless mb-0">
                <tr>
                    <th style="width: 220px">Nama</th>
                    <td>{{ $anak->nama }}</td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td>{{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Lahir</th>
                    <td>{{ $anak->tanggal_lahir->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <th>Orang Tua</th>
                    <td>{{ $anak->orangTua->nama_lengkap }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- form pengukuran --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Pengukuran</h3>
        </div>
        <form action="{{ route('posyandu.pemeriksaan.store', $anak->id) }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="form-group">
                    <label>Tanggal Ukur</label>
                    <input type="date" name="tanggal_ukur" max="{{ date('Y-m-d') }}"
                        value="{{ old('tanggal_ukur', date('Y-m-d')) }}"
                        class="form-control @error('tanggal_ukur') is-invalid @enderror">
                    @error('tanggal_ukur')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Berat Badan (kg)</label>
                    <input type="number" step="0.01" name="berat" value="{{ old('berat') }}"
                        class="form-control @error('berat') is-invalid @enderror">
                    @error('berat')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Tinggi / Panjang Badan (cm)</label>
                    <input type="number" step="0.1" name="tinggi" value="{{ old('tinggi') }}"
                        class="form-control @error('tinggi') is-invalid @enderror">
                    @error('tinggi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- cara ukur tinggi badan, wajib diisi --}}
                <div class="form-group">
                    <label>Cara Ukur</label>
                    <select name="cara_ukur" class="form-control @error('cara_ukur') is-invalid @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="terlentang" {{ old('cara_ukur') == 'terlentang' ? 'selected' : '' }}>Terlentang (berbaring)</option>
                        <option value="berdiri" {{ old('cara_ukur') == 'berdiri' ? 'selected' : '' }}>Berdiri</option>
                    </select>
                    <small class="text-muted">
                        Di bawah 24 bulan idealnya terlentang, 24 bulan ke atas berdiri.
                        Pilih sesuai cara pengukuran yang sebenarnya supaya z-score dikoreksi.
                    </small>
                    @error('cara_ukur')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label>LiLA (cm) <small class="text-muted">- boleh kosong</small></label>
                    <input type="number" step="0.1" name="lila" value="{{ old('lila') }}"
                        class="form-control @error('lila') is-invalid @enderror">
                    @error('lila')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Pemeriksaan</button>
                <a href="{{ route('posyandu.pemeriksaan.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@stop
