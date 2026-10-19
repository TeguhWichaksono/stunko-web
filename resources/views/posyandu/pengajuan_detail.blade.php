{{-- detail pengajuan orang tua (khusus posyandu): dicek dulu sebelum diputuskan --}}
@extends('adminlte::page')

@section('title', 'Detail Pengajuan')

@section('content_header')
    <h1>Detail Pengajuan</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    @php
        // apakah ada anak yang menunggu, dan apakah keputusan masih boleh diambil
        $adaMenunggu = $ortu->anak->where('status', 'menunggu')->isNotEmpty();
        $bisaProses = in_array($ortu->status_pengajuan, ['menunggu', 'diterima']) && $adaMenunggu;
    @endphp

    <div class="mb-3">
        <a href="{{ route('posyandu.pengajuan.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    {{-- alasan penolakan kalau pengajuan sudah ditolak --}}
    @if ($ortu->status_pengajuan == 'ditolak')
        <div class="alert alert-danger">
            Pengajuan ini sudah ditolak. Alasan: {{ $ortu->catatan_pengajuan }}
        </div>
    @endif

    {{-- biodata orang tua --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Biodata Orang Tua</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px">Nama Lengkap</th>
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
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px">Alamat</th>
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
                        <tr>
                            <th>Email Akun</th>
                            <td>{{ $ortu->user->email }}</td>
                        </tr>
                        <tr>
                            <th>Status Pengajuan</th>
                            <td>
                                @if ($ortu->status_pengajuan == 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($ortu->status_pengajuan == 'diterima')
                                    <span class="badge badge-success">Diterima</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- data anak dan keputusan per anak --}}
    <form action="{{ route('posyandu.pengajuan.proses', $ortu->id) }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Anak ({{ $ortu->anak->count() }})</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead>
                        <tr class="text-nowrap">
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Jenis Kelamin</th>
                            <th>Tempat Lahir</th>
                            <th>Tanggal Lahir</th>
                            <th>Usia</th>
                            <th>Berat Lahir</th>
                            <th>Tinggi Lahir</th>
                            <th>Status</th>
                            <th>Keputusan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ortu->anak as $a)
                            @php
                                // usia anak sekarang dalam tahun dan bulan
                                $bulanLahir = $a->tanggal_lahir->diffInMonths(now());
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $a->nama }}</td>
                                <td>{{ $a->nik }}</td>
                                <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $a->tempat_lahir }}</td>
                                <td class="text-nowrap">{{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                                <td class="text-nowrap">{{ intdiv($bulanLahir, 12) }} th {{ $bulanLahir % 12 }} bln</td>
                                <td>{{ $a->berat_lahir ? $a->berat_lahir + 0 . ' kg' : '-' }}</td>
                                <td>{{ $a->tinggi_lahir ? $a->tinggi_lahir + 0 . ' cm' : '-' }}</td>
                                <td>
                                    @if ($a->status == 'menunggu')
                                        <span class="badge badge-warning">Menunggu</span>
                                    @elseif ($a->status == 'diterima')
                                        <span class="badge badge-success">Diterima</span>
                                    @else
                                        <span class="badge badge-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td style="min-width: 160px">
                                    @if ($a->status == 'menunggu' && $bisaProses)
                                        {{-- anak yang belum diverifikasi: posyandu memilih Terima atau Tolak --}}
                                        <select name="keputusan[{{ $a->id }}]" class="form-control form-control-sm">
                                            <option value="">-- Pilih --</option>
                                            <option value="terima"
                                                {{ old('keputusan.' . $a->id) == 'terima' ? 'selected' : '' }}>Terima
                                            </option>
                                            <option value="tolak"
                                                {{ old('keputusan.' . $a->id) == 'tolak' ? 'selected' : '' }}>Tolak
                                            </option>
                                        </select>
                                    @elseif ($a->status == 'ditolak')
                                        <small class="text-muted">{{ $a->catatan }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Belum ada data anak.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- catatan dan tombol keputusan, hanya muncul kalau masih ada yang perlu diputuskan --}}
            @if ($bisaProses)
                <div class="card-body border-top">
                    <div class="form-group mb-0">
                        <label>
                            Catatan / Alasan
                            <small class="text-muted">- wajib diisi kalau ada anak atau pengajuan yang ditolak</small>
                        </label>
                        <textarea name="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="aksi" value="terima" class="btn btn-success">
                        {{ $ortu->status_pengajuan == 'menunggu' ? 'Terima Pengajuan' : 'Simpan Keputusan Anak' }}
                    </button>

                    {{-- tolak seluruh pengajuan hanya untuk pengajuan yang masih menunggu --}}
                    @if ($ortu->status_pengajuan == 'menunggu')
                        <button type="submit" name="aksi" value="tolak" class="btn btn-danger"
                            onclick="return confirm('Yakin menolak seluruh pengajuan ini?')">
                            Tolak Pengajuan
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </form>
@stop
