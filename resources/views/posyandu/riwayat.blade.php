{{-- halaman riwayat pemeriksaan (khusus posyandu), ada kolom cari dan pilihan bulan --}}
@extends('adminlte::page')

@section('title', 'Riwayat Pemeriksaan')

@section('content_header')
    <h1>Riwayat Pemeriksaan</h1>
@stop

@section('content')
    {{-- form cari: dikirim lewat alamat (GET), jadi hasilnya bisa di-refresh --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('posyandu.riwayat.index') }}" method="GET" class="form-inline">
                <input type="text" name="cari" value="{{ $cari }}" class="form-control mr-2 mb-2"
                    style="min-width: 260px" placeholder="Cari nama anak atau nama orang tua">

                <input type="month" name="bulan" value="{{ $bulan }}" class="form-control mr-2 mb-2">

                <button type="submit" class="btn btn-primary mr-2 mb-2">Cari</button>
                <a href="{{ route('posyandu.riwayat.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menampilkan <b>{{ $daftar->count() }}</b> data</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Ukur</th>
                        <th>Nama Anak</th>
                        <th>Orang Tua</th>
                        <th>Usia (bulan)</th>
                        <th>Berat (kg)</th>
                        <th>Tinggi (cm)</th>
                        <th>LiLA (cm)</th>
                        <th>Status Stunting</th>
                        <th>Status Gizi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau tidak ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->tanggal_ukur->format('d-m-Y') }}</td>
                            <td>{{ $p->anak->nama }}</td>
                            <td>{{ $p->anak->orangTua->nama_lengkap }}</td>
                            <td>{{ number_format($p->usia_bulan, 1) }}</td>
                            <td>{{ $p->berat + 0 }}</td>
                            <td>{{ $p->tinggi + 0 }}</td>
                            <td>{{ $p->lila !== null ? $p->lila + 0 : '-' }}</td>
                            <td>
                                {{-- warna lencana: merah = Stunting, kuning = Berisiko, hijau = Normal --}}
                                @if ($p->status_stunting == 'Stunting')
                                    <span class="badge badge-danger">Stunting</span>
                                @elseif ($p->status_stunting == 'Berisiko')
                                    <span class="badge badge-warning">Berisiko</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                            <td>
                                {{-- merah = Gizi Kurang, kuning = Gizi Lebih, hijau = Normal --}}
                                @if ($p->status_gizi == 'Gizi Kurang')
                                    <span class="badge badge-danger">Gizi Kurang</span>
                                @elseif ($p->status_gizi == 'Gizi Lebih')
                                    <span class="badge badge-warning">Gizi Lebih</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
