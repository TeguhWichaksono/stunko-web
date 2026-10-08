{{-- halaman pilih posyandu dan status pengajuan orang tua --}}
@extends('adminlte::page')

@section('title', 'Pilih Posyandu')

@section('content_header')
    <h1>Pilih Posyandu</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    {{-- kartu status pengajuan, tampil kalau sudah pernah mengajukan --}}
    @if ($ortu->status_pengajuan && $ortu->posyandu)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pengajuan Anda</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th style="width: 220px">Posyandu</th>
                        <td>{{ $ortu->posyandu->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Kelurahan / Wilayah</th>
                        <td>{{ $ortu->posyandu->kelurahan }} / {{ $ortu->posyandu->wilayah }}</td>
                    </tr>
                    <tr>
                        <th>Kepala Posyandu</th>
                        <td>{{ $ortu->posyandu->nama_kepala }} ({{ $ortu->posyandu->no_hp }})</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if ($ortu->status_pengajuan == 'menunggu')
                                <span class="badge badge-warning">Menunggu persetujuan</span>
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
    @endif

    @if (!$syarat)
        {{-- syarat belum terpenuhi: arahkan melengkapi biodata dan data anak --}}
        <div class="alert alert-warning">
            Sebelum mengajukan, lengkapi
            <a href="{{ route('ortu.biodata.edit') }}">Biodata Orang Tua</a>
            dan isi minimal satu <a href="{{ route('ortu.anak.index') }}">Data Anak</a>.
        </div>
    @elseif (in_array($ortu->status_pengajuan, ['menunggu', 'diterima']))
        {{-- sudah punya pengajuan aktif, daftar posyandu tidak ditampilkan --}}
    @else
        {{-- boleh mengajukan: tampilkan daftar posyandu --}}
        @if ($ortu->status_pengajuan == 'ditolak')
            <div class="alert alert-danger">
                Pengajuan Anda ditolak. Silakan pilih posyandu lain.
            </div>
        @endif

        <div class="alert alert-info">
            Pilih posyandu yang wilayahnya sesuai dengan alamat Anda:
            <b>RT {{ $ortu->rt }} / RW {{ $ortu->rw }}, Kelurahan {{ $ortu->kelurahan }}</b>.
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Nama Posyandu</th>
                            <th>Kelurahan</th>
                            <th>Wilayah</th>
                            <th>Alamat</th>
                            <th>Kepala / No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $p)
                            <tr>
                                <td>{{ $p->user->name }}</td>
                                <td>{{ $p->kelurahan }}</td>
                                <td>{{ $p->wilayah }}</td>
                                <td>{{ $p->alamat }}</td>
                                <td>{{ $p->nama_kepala }}<br>{{ $p->no_hp }}</td>
                                <td>
                                    {{-- satu form kecil untuk tiap posyandu --}}
                                    <form action="{{ route('ortu.posyandu.ajukan') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="posyandu_id" value="{{ $p->id }}">
                                        <button type="submit" class="btn btn-sm btn-primary">Ajukan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada posyandu yang tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop
