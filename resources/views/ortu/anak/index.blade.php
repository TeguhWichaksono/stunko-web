{{-- halaman Data Anak (orang tua): hanya biodata anak, tambah dan edit --}}
@extends('adminlte::page')

@section('title', 'Data Anak')

@section('content_header')
    <h1>Data Anak</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    {{-- kalau biodata orang tua belum lengkap, anak belum bisa ditambah --}}
    @if (!$ortu->lengkap())
        <div class="alert alert-warning">
            Lengkapi <a href="{{ route('ortu.biodata.edit') }}">Biodata Orang Tua</a>
            dulu sebelum menambah data anak.
        </div>
    @endif

    <div class="mb-3">
        {{-- tombol Tambah Anak aktif hanya kalau biodata sudah lengkap --}}
        @if ($ortu->lengkap())
            <a href="{{ route('ortu.anak.create') }}" class="btn btn-primary">Tambah Anak</a>
        @else
            <button class="btn btn-primary" disabled>Tambah Anak</button>
        @endif
    </div>

    <div class="row">
        {{-- satu kartu untuk tiap anak, jadi anak kembar tampil berdampingan --}}
        @forelse ($daftar as $a)
            @php
                // usia anak sekarang dalam tahun dan bulan
                $bulanLahir = $a->tanggal_lahir->diffInMonths(now());
            @endphp
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $a->nama }}</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th style="width: 200px">Nama</th>
                                <td>{{ $a->nama }}</td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td>{{ $a->nik }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <th>Tempat, Tanggal Lahir</th>
                                <td>{{ $a->tempat_lahir }}, {{ $a->tanggal_lahir->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <th>Usia Sekarang</th>
                                <td>{{ intdiv($bulanLahir, 12) }} tahun {{ $bulanLahir % 12 }} bulan</td>
                            </tr>
                            <tr>
                                <th>Berat Lahir</th>
                                <td>{{ $a->berat_lahir ? $a->berat_lahir + 0 . ' kg' : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tinggi Lahir</th>
                                <td>{{ $a->tinggi_lahir ? $a->tinggi_lahir + 0 . ' cm' : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{-- Edit hanya untuk anak yang belum diajukan atau ditolak --}}
                        @if (in_array($a->status, ['belum', 'ditolak']))
                            <a href="{{ route('ortu.anak.edit', $a->id) }}" class="btn btn-warning">Edit</a>
                        @else
                            <small class="text-muted">
                                Data terkunci karena sedang diproses atau sudah diterima posyandu.
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary">Belum ada data anak.</div>
            </div>
        @endforelse
    </div>
@stop
