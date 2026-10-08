<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class PrediksiService
{
    // kirim data satu anak ke FastAPI, lalu kembalikan hasil prediksinya (array)
    public function prediksi(string $jk, float $usiaBulan, float $berat, float $tinggi, ?float $lila = null): array
    {
        // data yang dikirim, nama kuncinya harus sama dengan yang diminta FastAPI
        $data = [
            'jk' => $jk,
            'usia_bulan' => $usiaBulan,
            'berat' => $berat,
            'tinggi' => $tinggi,
        ];

        // LiLA hanya dikirim kalau diisi
        if ($lila !== null) {
            $data['lila'] = $lila;
        }

        try {
            // tunggu jawaban FastAPI paling lama 15 detik
            $respon = Http::timeout(15)->post(config('services.stunko.url') . '/prediksi', $data);
        } catch (Exception $e) {
            // FastAPI mati atau tidak terjangkau
            throw new Exception('Layanan prediksi tidak dapat dihubungi. Pastikan FastAPI sedang berjalan.');
        }

        // FastAPI menolak data (misalnya angka di luar batas)
        if ($respon->failed()) {
            throw new Exception('Layanan prediksi menolak data (kode ' . $respon->status() . ').');
        }

        return $respon->json();
    }
}
