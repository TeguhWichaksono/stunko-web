{{-- halaman daftar pengajuan dari orang tua (khusus posyandu) --}}
@extends('adminlte::page')

@section('title', 'Pengajuan Orang Tua')

@section('content_header')
    <h1>Pengajuan Orang Tua</h1>
@stop

@section('content')
    {{-- pesan sukses dan pesan gagal --}}
    @if (session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if (session('galat'))
        <div class="alert alert-danger">{{ session('galat') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Nama Orang Tua</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>RT / RW</th>
                        <th>Kelurahan</th>
                        <th>Jumlah Anak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada pengajuan, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $o)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $o->nama_lengkap }}</td>
                            <td>{{ $o->no_hp }}</td>
                            <td>{{ $o->alamat }}</td>
                            <td class="text-nowrap">{{ $o->rt }} / {{ $o->rw }}</td>
                            <td>{{ $o->kelurahan }}</td>
                            <td class="text-nowrap">
                                {{ $o->anak_count }}
                                {{-- penanda kalau ada anak yang belum diverifikasi --}}
                                @if ($o->anak_menunggu > 0)
                                    <span class="badge badge-warning">{{ $o->anak_menunggu }} menunggu verifikasi</span>
                                @endif
                            </td>
                            <td>
                                @if ($o->status_pengajuan == 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($o->status_pengajuan == 'diterima')
                                    <span class="badge badge-success">Diterima</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                {{-- keputusan diambil di halaman detail, bukan langsung dari daftar --}}
                                @if ($o->status_pengajuan == 'menunggu' || $o->anak_menunggu > 0)
                                    <a href="{{ route('posyandu.pengajuan.detail', $o->id) }}"
                                       class="btn btn-sm btn-warning">Periksa &amp; Putuskan</a>
                                @else
                                    <a href="{{ route('posyandu.pengajuan.detail', $o->id) }}"
                                       class="btn btn-sm btn-info">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada pengajuan yang masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
