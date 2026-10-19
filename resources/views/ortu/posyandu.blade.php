{{-- halaman Pilih Posyandu (orang tua): daftar anak, posyandu, status, dan tombol ajukan per anak --}}
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

    {{-- syarat sebelum mengajukan: biodata lengkap dan sudah ada data anak --}}
    @if (!$ortu->lengkap())
        <div class="alert alert-warning">
            Sebelum mengajukan, lengkapi dulu
            <a href="{{ route('ortu.biodata.edit') }}">Biodata Orang Tua</a>.
        </div>
    @elseif ($daftar->isEmpty())
        <div class="alert alert-warning">
            Belum ada data anak. Tambahkan dulu di menu
            <a href="{{ route('ortu.anak.index') }}">Data Anak</a>.
        </div>
    @else
        <div class="alert alert-info">
            Ajukan tiap anak ke posyandu sesuai wilayah tempat tinggalnya.
            Anak yang sudah diterima tetap di posyandunya, dan anak lain bisa diajukan ke posyandu yang berbeda.
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Tanggal Lahir</th>
                        <th>Posyandu</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau belum ada anak, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $a)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $a->nama }}</td>
                            <td class="text-nowrap">{{ $a->tanggal_lahir->format('d-m-Y') }}</td>

                            {{-- posyandu pilihan anak ini beserta wilayah dan kontaknya --}}
                            <td>
                                @if ($a->posyandu)
                                    {{ $a->posyandu->user->name }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $a->posyandu->kelurahan }} / {{ $a->posyandu->wilayah }}<br>
                                        {{ $a->posyandu->nama_kepala }} ({{ $a->posyandu->no_hp }})
                                    </small>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- status pengajuan anak ini --}}
                            <td>
                                @if ($a->status == 'belum')
                                    <span class="badge badge-secondary">Belum diajukan</span>
                                @elseif ($a->status == 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($a->status == 'diterima')
                                    <span class="badge badge-success">Diterima</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>

                            {{-- keterangan sesuai status, termasuk alasan penolakan --}}
                            <td style="min-width: 220px">
                                @if ($a->status == 'ditolak')
                                    <small class="text-danger">Alasan penolakan: {{ $a->catatan ?: '-' }}</small>
                                @elseif ($a->status == 'belum')
                                    <small class="text-muted">Belum diajukan ke posyandu mana pun.</small>
                                @elseif ($a->status == 'menunggu')
                                    <small class="text-muted">Menunggu persetujuan posyandu.</small>
                                @else
                                    <small class="text-muted">Anak sudah terdaftar di posyandu ini.</small>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                {{-- tombol hanya untuk anak yang belum diajukan atau ditolak --}}
                                @if (in_array($a->status, ['belum', 'ditolak']))
                                    @if ($ortu->lengkap())
                                        <a href="{{ route('ortu.anak.ajukan.form', $a->id) }}"
                                            class="btn btn-sm btn-primary">
                                            {{ $a->status == 'ditolak' ? 'Ajukan Ulang' : 'Ajukan' }}
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-primary" disabled>Ajukan</button>
                                    @endif
                                    @if ($a->status == 'ditolak')
                                        <br><small class="text-muted">Perbaiki dulu datanya di Data Anak kalau
                                            perlu.</small>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data anak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
