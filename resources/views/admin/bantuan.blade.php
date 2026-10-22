{{-- halaman calon penerima bantuan (khusus admin): pemeriksaan terbaru tiap anak, urut dari skor tertinggi --}}
@extends('adminlte::page')

@section('title', 'Calon Penerima Bantuan')

@section('content_header')
    <h1>Calon Penerima Bantuan</h1>
@stop

@section('content')
    {{-- ringkasan jumlah anak --}}
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-child"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Anak yang Sudah Diperiksa</span>
                    <span class="info-box-number">{{ $ringkasan['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-hand-holding-heart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Calon Penerima Bantuan</span>
                    <span class="info-box-number">{{ $ringkasan['calon'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Bukan Calon Penerima</span>
                    <span class="info-box-number">{{ $ringkasan['bukan'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- form cari dan penyaring: dikirim lewat alamat (GET) --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.bantuan.index') }}" method="GET" class="form-inline">
                <input type="text" name="cari" value="{{ $cari }}"
                       class="form-control mr-2 mb-2" style="min-width: 280px"
                       placeholder="Cari nama anak, NIK anak, atau nama orang tua">

                {{-- penyaring posyandu --}}
                <select name="posyandu" class="form-control mr-2 mb-2">
                    <option value="">Semua posyandu</option>
                    @foreach ($daftarPosyandu as $ps)
                        <option value="{{ $ps->id }}" {{ $posyanduId == $ps->id ? 'selected' : '' }}>
                            {{ $ps->user->name }}
                        </option>
                    @endforeach
                </select>

                {{-- penyaring keputusan --}}
                <select name="keputusan" class="form-control mr-2 mb-2">
                    <option value="">Semua keputusan</option>
                    <option value="calon" {{ $keputusan == 'calon' ? 'selected' : '' }}>Calon Penerima Bantuan</option>
                    <option value="bukan" {{ $keputusan == 'bukan' ? 'selected' : '' }}>Bukan Calon Penerima</option>
                </select>

                <button type="submit" class="btn btn-primary mr-2 mb-2">Cari</button>
                <a href="{{ route('admin.bantuan.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menampilkan <b>{{ $daftar->count() }}</b> anak, diurutkan dari skor tertinggi</span>
            <small class="text-muted ml-3">
                Memakai pemeriksaan terbaru tiap anak. Skor 0-100 dihitung dari probabilitas model prediksi,
                makin tinggi makin diprioritaskan. Z-Score merah = di bawah -2.
            </small>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr class="text-nowrap">
                        <th>Peringkat</th>
                        <th>Nama Anak</th>
                        <th>Orang Tua</th>
                        <th>Posyandu</th>
                        <th>Tanggal Ukur</th>
                        <th>Usia (bln)</th>
                        <th>Z BB/U</th>
                        <th>Z TB/U</th>
                        <th>Z BB/TB</th>
                        <th>Status Stunting</th>
                        <th>Status Gizi</th>
                        <th>Skor</th>
                        <th>Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- kalau tidak ada data, tampilkan tulisan kosong --}}
                    @forelse ($daftar as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->anak->nama }}</td>
                            <td>{{ $p->anak->orangTua->nama_lengkap }}</td>
                            <td>{{ $p->anak->posyandu ? $p->anak->posyandu->user->name : '-' }}</td>
                            <td class="text-nowrap">{{ $p->tanggal_ukur->format('d-m-Y') }}</td>
                            <td>{{ number_format($p->usia_bulan, 1, ',', '') }}</td>

                            {{-- z-score, merah kalau di bawah -2, strip kalau pemeriksaan lama --}}
                            <td class="text-nowrap {{ $p->z_bb_u !== null && $p->z_bb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_u !== null ? number_format($p->z_bb_u, 2, ',', '.') : '-' }}
                            </td>
                            <td class="text-nowrap {{ $p->z_tb_u !== null && $p->z_tb_u < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_tb_u !== null ? number_format($p->z_tb_u, 2, ',', '.') : '-' }}
                            </td>
                            <td class="text-nowrap {{ $p->z_bb_tb !== null && $p->z_bb_tb < -2 ? 'text-danger font-weight-bold' : '' }}">
                                {{ $p->z_bb_tb !== null ? number_format($p->z_bb_tb, 2, ',', '.') : '-' }}
                            </td>

                            {{-- status stunting (hasil ML) --}}
                            <td>
                                @if ($p->status_stunting == 'Stunting')
                                    <span class="badge badge-danger">Stunting</span>
                                @elseif ($p->status_stunting == 'Berisiko')
                                    <span class="badge badge-warning">Berisiko</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>

                            {{-- status gizi (hasil ML) --}}
                            <td>
                                @if ($p->status_gizi == 'Gizi Kurang')
                                    <span class="badge badge-danger">Gizi Kurang</span>
                                @elseif ($p->status_gizi == 'Gizi Lebih')
                                    <span class="badge badge-warning">Gizi Lebih</span>
                                @else
                                    <span class="badge badge-success">Normal</span>
                                @endif
                            </td>

                            {{-- skor beserta batang kecil --}}
                            <td style="min-width: 110px">
                                <b>{{ number_format($p->skor, 1, ',', '') }}</b>
                                <div class="progress" style="height: 6px">
                                    <div class="progress-bar {{ $p->keputusan == 'Calon Penerima Bantuan' ? 'bg-danger' : 'bg-secondary' }}"
                                         style="width: {{ min(100, max(0, $p->skor)) }}%"></div>
                                </div>
                            </td>

                            {{-- keputusan akhir --}}
                            <td class="text-nowrap">
                                @if ($p->keputusan == 'Calon Penerima Bantuan')
                                    <span class="badge badge-danger">Calon Penerima</span>
                                @else
                                    <span class="badge badge-secondary">Bukan Calon</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center">Belum ada data pemeriksaan yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

