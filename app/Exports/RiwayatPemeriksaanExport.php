<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// membuat file Excel riwayat pemeriksaan, urutan kolomnya sama dengan tabel di halaman riwayat admin
class RiwayatPemeriksaanExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithCustomValueBinder
{
    // data pemeriksaan (sudah lengkap dengan anak, orang tua, dan posyandu)
    private $data;

    // penghitung nomor urut baris
    private $no = 0;

    // kolom yang harus tersimpan sebagai TEKS: NIK (B), RT (O), RW (P)
    // supaya NIK 16 digit tidak berubah jadi angka ilmiah dan angka 0 di depan tidak hilang
    private $kolomTeks = ['B', 'O', 'P'];

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    // judul kolom (urutannya harus sama dengan fungsi map di bawah)
    public function headings(): array
    {
        return [
            // 29 kolom pertama mengikuti format Excel puskesmas
            'No', 'NIK', 'Nama', 'JK', 'Tgl Lahir', 'BB Lahir', 'TB Lahir', 'Nama Ortu',
            'Prov', 'Kab/Kota', 'Kec', 'Puskesmas', 'Desa/Kel', 'Posyandu', 'RT', 'RW', 'Alamat',
            'Usia Saat Ukur', 'Tanggal Pengukuran', 'Berat', 'Tinggi', 'Cara Ukur', 'LiLA',
            'BB/U', 'ZS BB/U', 'TB/U', 'ZS TB/U', 'BB/TB', 'ZS BB/TB',
            // hasil prediksi dari sistem
            'Status Stunting', 'Keterangan Stunting', 'Status Gizi', 'Keterangan Gizi',
        ];
    }

    // isi satu baris Excel untuk satu pemeriksaan
    public function map($p): array
    {
        $this->no++;
        $a = $p->anak;
        $o = $a->orangTua;

        return [
            // identitas anak dan orang tua
            $this->no,
            $a->nik,
            $a->nama,
            $a->jenis_kelamin,
            $a->tanggal_lahir->format('d-m-Y'),
            $a->berat_lahir !== null ? (float) $a->berat_lahir : '',
            $a->tinggi_lahir !== null ? (float) $a->tinggi_lahir : '',
            $o->nama_lengkap,

            // wilayah dan posyandu (wilayah diisi dari config/stunko.php)
            config('stunko.provinsi'),
            config('stunko.kabupaten'),
            config('stunko.kecamatan'),
            config('stunko.puskesmas'),
            $o->kelurahan,
            $p->posyandu->user->name ?? '-',
            $o->rt,
            $o->rw,
            $o->alamat,

            // data pemeriksaan (angka tetap angka supaya bisa dihitung di Excel)
            $p->usia_teks,
            $p->tanggal_ukur->format('d-m-Y'),
            (float) $p->berat,
            (float) $p->tinggi,
            $p->cara_ukur ? ucfirst($p->cara_ukur) : '',
            $p->lila !== null ? (float) $p->lila : '',

            // z-score beserta kategorinya
            data_get($p->hasil_api, 'kategori_zscore.bb_u') ?? '',
            $p->z_bb_u !== null ? (float) $p->z_bb_u : '',
            data_get($p->hasil_api, 'kategori_zscore.tb_u') ?? '',
            $p->z_tb_u !== null ? (float) $p->z_tb_u : '',
            data_get($p->hasil_api, 'kategori_zscore.bb_tb') ?? '',
            $p->z_bb_tb !== null ? (float) $p->z_bb_tb : '',

            // hasil prediksi dari sistem
            $p->status_stunting,
            data_get($p->hasil_api, 'penjelasan.stunting') ?? '',
            $p->status_gizi,
            data_get($p->hasil_api, 'penjelasan.gizi') ?? '',
        ];
    }

    // judul kolom dibuat tebal
    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    // kolom NIK, RT, dan RW disimpan sebagai teks, kolom lain memakai aturan bawaan
    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), $this->kolomTeks) && $cell->getRow() > 1 && $value !== null && $value !== '') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
