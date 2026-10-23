{{-- halaman calon penerima bantuan (khusus admin): skor minimal bisa diatur, ada status tindak lanjut --}}
@extends('adminlte::page')

@section('title', 'Calon Penerima Bantuan')

@section('content_header')
    <h1>Calon Penerima Bantuan</h1>
@stop

@section('content')
    {{-- ringkasan jumlah anak per status tindak lanjut --}}
    <div class="row">
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-child"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Anak Terpantau</span>
                    <span class="info-box-number">{{ $ringkasan['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Belum Ditindaklanjuti</span>
                    <span class="info-box-number">{{ $ringkasan['baru'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-phone"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sudah Dihubungi</span>
                    <span class="info-box-number">{{ $ringkasan['dihubungi'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-hand-holding-heart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Berhak Bantuan</span>
                    <span class="info-box-number">{{ $ringkasan['berhak'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg">
            <div class="info-box">
                <span class="info-box-icon bg-dark"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tidak Berhak</span>
                    <span class="info-box-number">{{ $ringkasan['tidak'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- skor minimal, pencarian, dan penyaring: dikirim lewat alamat (GET) --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.bantuan.index') }}" method="GET" class="form-inline">
                {{-- skor minimal yang masuk daftar --}}
                <label class="mr-2 mb-2">Skor minimal</label>
                <input type="number" name="min" min="0" max="100" step="1"
                    value="{{ $min + 0 }}" class="form-control mr-3 mb-2" style="width: 90px">

                <input type="text" name="cari" value="{{ $cari }}" class="form-control mr-2 mb-2"
                    style="min-width: 240px" placeholder="Cari nama anak, NIK, atau orang tua">

                {{-- penyaring posyandu --}}
                <select name="posyandu" class="form-control mr-2 mb-2">
                    <option value="">Semua posyandu</option>
                    @foreach ($daftarPosyandu as $ps)
                        <option value="{{ $ps->id }}" {{ $posyanduId == $ps->id ? 'selected' : '' }}>
                            {{ $ps->user->name }}
                        </option>
                    @endforeach
                </select>

                {{-- penyaring status tindak lanjut --}}
                <select name="status" class="form-control mr-2 mb-2">
                    <option value="">Semua status</option>
                    <option value="baru" {{ $status == 'baru' ? 'selected' : '' }}>Belum ditindaklanjuti</option>
                    <option value="dihubungi" {{ $status == 'dihubungi' ? 'selected' : '' }}>Sudah dihubungi</option>
                    <option value="berhak" {{ $status == 'berhak' ? 'selected' : '' }}>Berhak</option>
                    <option value="tidak" {{ $status == 'tidak' ? 'selected' : '' }}>Tidak berhak</option>
                </select>

                <button type="submit" class="btn btn-primary mr-2 mb-2">Tampilkan</button>
                <a href="{{ route('admin.bantuan.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
            <small class="text-muted">
                Daftar berisi anak yang skor pemeriksaan terakhirnya minimal {{ $min + 0 }}, ditambah anak yang sudah
                ditindaklanjuti. Skor lebih rendah berarti lebih banyak anak terpantau, jadi lebih sedikit yang terlewat.
            </small>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menampilkan <b>{{ $daftar->count() }}</b> anak</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>No</th>
                        <th>Nama Anak</th>
                        <th>Usia (bln)</th>
                        <th>Posyandu</th>
                        <th>Orang Tua</th>
                        <th>Skor</th>
                        <th>Status Tindak Lanjut</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau tidak ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $p)
                        @php
                            // status tindak lanjut anak ini
                            $b = $p->anak->bantuan;
                            $st = $b->status ?? 'baru';
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $p->anak->nama }}
                                <br><small
                                    class="text-muted">{{ $p->anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                            </td>
                            <td>{{ number_format($p->usia_bulan, 1, ',', '') }}</td>
                            <td>{{ $p->anak->posyandu ? $p->anak->posyandu->user->name : '-' }}</td>
                            <td>
                                {{ $p->anak->orangTua->nama_lengkap }}
                                <br><small class="text-muted">{{ $p->anak->orangTua->no_hp }}</small>
                            </td>

                            {{-- skor beserta batang kecil --}}
                            <td style="min-width: 110px">
                                <b>{{ number_format($p->skor, 1, ',', '') }}</b>
                                <div class="progress" style="height: 6px">
                                    <div class="progress-bar {{ $p->skor >= $min ? 'bg-danger' : 'bg-secondary' }}"
                                        style="width: {{ min(100, max(0, $p->skor)) }}%"></div>
                                </div>
                            </td>

                            {{-- status tindak lanjut --}}
                            <td>
                                @if ($st == 'baru')
                                    <span class="badge badge-secondary">Belum ditindaklanjuti</span>
                                @elseif ($st == 'dihubungi')
                                    <span class="badge badge-warning">Sudah dihubungi</span>
                                @elseif ($st == 'berhak')
                                    <span class="badge badge-success">Berhak</span>
                                    @if ($b->jenisBantuan)
                                        <br><small>{{ $b->jenisBantuan->nama }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-dark">Tidak berhak</span>
                                @endif
                            </td>

                            {{-- tombol Detail membuka biodata, riwayat, dan grafik anak --}}
                            <td>
                                <a href="{{ route('admin.bantuan.detail', $p->anak_id) }}"
                                    class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada anak yang memenuhi skor minimal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
