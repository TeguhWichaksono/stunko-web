{{-- detail pengajuan satu anak (khusus posyandu): dicek dulu sebelum diterima atau ditolak --}}
@extends('adminlte::page')

@section('title', 'Detail Pengajuan')

@section('content_header')
    <h1>Detail Pengajuan: {{ $anak->nama }}</h1>
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
        // usia anak sekarang dalam tahun dan bulan
        $bulanLahir = $anak->tanggal_lahir->diffInMonths(now());
        $o = $anak->orangTua;
    @endphp

    <div class="mb-3">
        <a href="{{ route('posyandu.pengajuan.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>

    <div class="row">
        {{-- biodata anak --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Biodata Anak</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px">Nama</th>
                            <td>{{ $anak->nama }}</td>
                        </tr>
                        <tr>
                            <th>NIK</th>
                            <td>{{ $anak->nik }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>{{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <th>Tempat, Tanggal Lahir</th>
                            <td>{{ $anak->tempat_lahir }}, {{ $anak->tanggal_lahir->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <th>Usia Sekarang</th>
                            <td>{{ intdiv($bulanLahir, 12) }} tahun {{ $bulanLahir % 12 }} bulan</td>
                        </tr>
                        <tr>
                            <th>Berat Lahir</th>
                            <td>{{ $anak->berat_lahir ? $anak->berat_lahir + 0 . ' kg' : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tinggi Lahir</th>
                            <td>{{ $anak->tinggi_lahir ? $anak->tinggi_lahir + 0 . ' cm' : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Pengajuan</th>
                            <td>
                                @if ($anak->status == 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($anak->status == 'diterima')
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

        {{-- biodata orang tua --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Biodata Orang Tua</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px">Nama Lengkap</th>
                            <td>{{ $o->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <th>NIK</th>
                            <td>{{ $o->nik }}</td>
                        </tr>
                        <tr>
                            <th>No. HP</th>
                            <td>{{ $o->no_hp }}</td>
                        </tr>
                        <tr>
                            <th>Pekerjaan</th>
                            <td>{{ $o->pekerjaan }}</td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>{{ $o->alamat }}</td>
                        </tr>
                        <tr>
                            <th>RT / RW</th>
                            <td>{{ $o->rt }} / {{ $o->rw }}</td>
                        </tr>
                        <tr>
                            <th>Kelurahan</th>
                            <td>{{ $o->kelurahan }}</td>
                        </tr>
                        <tr>
                            <th>Email Akun</th>
                            <td>{{ $o->user->email }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- anak lain milik orang tua yang sama, sebagai bahan pertimbangan (misalnya anak kembar) --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Anak Lain dari Orang Tua Ini ({{ $saudara->count() }})</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>Nama</th>
                        <th>Tanggal Lahir</th>
                        <th>Posyandu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($saudara as $s)
                        <tr>
                            <td>{{ $s->nama }}</td>
                            <td class="text-nowrap">{{ $s->tanggal_lahir->format('d-m-Y') }}</td>
                            <td>{{ $s->posyandu ? $s->posyandu->user->name : '-' }}</td>
                            <td>
                                @if ($s->status == 'belum')
                                    <span class="badge badge-secondary">Belum diajukan</span>
                                @elseif ($s->status == 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($s->status == 'diterima')
                                    <span class="badge badge-success">Diterima</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada anak lain.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- keputusan untuk anak ini saja --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Keputusan untuk {{ $anak->nama }}</h3>
        </div>

        @if ($anak->status == 'menunggu')
            <div class="card-body">
                <div class="row">
                    {{-- terima --}}
                    <div class="col-lg-4 mb-3">
                        <form action="{{ route('posyandu.pengajuan.terima', $anak->id) }}" method="POST"
                            onsubmit="return confirm('Terima {{ $anak->nama }}?')">
                            @csrf
                            <p class="text-muted">
                                Anak masuk daftar Pemeriksaan Anak dan bisa mulai diperiksa.
                            </p>
                            <button type="submit" class="btn btn-success">Terima Anak Ini</button>
                        </form>
                    </div>

                    {{-- tolak, alasan wajib diisi --}}
                    <div class="col-lg-8">
                        <form action="{{ route('posyandu.pengajuan.tolak', $anak->id) }}" method="POST"
                            onsubmit="return confirm('Tolak {{ $anak->nama }}?')">
                            @csrf
                            <div class="form-group">
                                <label>Alasan Penolakan <small class="text-muted">- wajib diisi, akan dibaca orang
                                        tua</small></label>
                                <textarea name="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror"
                                    placeholder="Contoh: NIK tidak sesuai, atau alamat berada di luar wilayah posyandu ini.">{{ old('catatan') }}</textarea>
                                @error('catatan')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger">Tolak Anak Ini</button>
                        </form>
                    </div>
                </div>
            </div>
        @elseif ($anak->status == 'diterima')
            <div class="card-body">
                <span class="badge badge-success">Diterima</span>
                Anak ini sudah diterima dan muncul di menu Pemeriksaan Anak.
            </div>
        @else
            <div class="card-body">
                <span class="badge badge-danger">Ditolak</span>
                Alasan: {{ $anak->catatan ?: '-' }}
            </div>
        @endif
    </div>
@stop
