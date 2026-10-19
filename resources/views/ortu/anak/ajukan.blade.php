{{-- halaman pilih posyandu untuk satu anak (orang tua) --}}
@extends('adminlte::page')

@section('title', 'Pilih Posyandu')

@section('content_header')
    <h1>Pilih Posyandu untuk {{ $anak->nama }}</h1>
@stop

@section('content')
    {{-- pesan gagal --}}
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
    </div>

    {{-- alasan penolakan sebelumnya, kalau ada --}}
    @if ($anak->status == 'ditolak' && $anak->catatan)
        <div class="alert alert-danger">
            Pengajuan sebelumnya ditolak. Alasan: {{ $anak->catatan }}
        </div>
    @endif

    {{-- data anak yang akan diajukan --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Anak</h3>
        </div>
        <div class="card-body">
            <table class="table table-borderless mb-0">
                <tr>
                    <th style="width: 200px">Nama</th>
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
            </table>
        </div>
    </div>

    {{-- petunjuk memilih posyandu sesuai alamat --}}
    <div class="alert alert-info">
        Pilih posyandu yang wilayahnya sesuai dengan alamat tempat tinggal anak ini sekarang.
        Alamat pada biodata Anda: <b>RT {{ $ortu->rt }} / RW {{ $ortu->rw }}, Kelurahan {{ $ortu->kelurahan }}</b>.
    </div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
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
                        {{-- baris posyandu yang dipakai anak lain diberi warna dan penanda --}}
                        <tr class="{{ $pilihanAwal == $p->id ? 'table-info' : '' }}">
                            <td>
                                {{ $p->user->name }}
                                @if ($pilihanAwal == $p->id)
                                    <br><span class="badge badge-info">Posyandu anak Anda yang lain</span>
                                @endif
                            </td>
                            <td>{{ $p->kelurahan }}</td>
                            <td>{{ $p->wilayah }}</td>
                            <td>{{ $p->alamat }}</td>
                            <td>{{ $p->nama_kepala }}<br>{{ $p->no_hp }}</td>
                            <td>
                                {{-- satu form kecil untuk tiap posyandu, dengan konfirmasi sebelum dikirim --}}
                                <form action="{{ route('ortu.anak.ajukan', $anak->id) }}" method="POST"
                                    onsubmit="return confirm('Ajukan {{ $anak->nama }} ke {{ $p->user->name }}?')">
                                    @csrf
                                    <input type="hidden" name="posyandu_id" value="{{ $p->id }}">
                                    <button type="submit" class="btn btn-sm btn-primary">Ajukan ke sini</button>
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
@stop
