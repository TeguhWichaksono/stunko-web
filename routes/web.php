<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PosyanduController;
use App\Http\Controllers\PosyanduProfilController;
use App\Http\Controllers\OrtuBiodataController;
use App\Http\Controllers\OrtuAnakController;
use App\Http\Controllers\OrtuPosyanduController;
use App\Http\Controllers\PosyanduPengajuanController;
use App\Http\Controllers\PosyanduPemeriksaanController;
use App\Http\Controllers\PosyanduRiwayatController;
use App\Http\Controllers\PosyanduDashboardController;
use App\Http\Controllers\OrtuRiwayatController;
use App\Http\Controllers\AdminBantuanController;
use App\Http\Controllers\AdminJenisBantuanController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminRiwayatController;

// halaman awal langsung diarahkan ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// route login, register, dan logout bawaan
Auth::routes();

// setelah login, arahkan ke dashboard sesuai peran akun
Route::get('/home', function () {
    $role = auth()->user()->role;

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if ($role === 'posyandu') {
        return redirect()->route('posyandu.dashboard');
    }
    return redirect()->route('ortu.dashboard');
})->middleware('auth')->name('home');

// halaman khusus admin (puskesmas)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // dashboard admin: angkanya dihitung di controller
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // kelola akun posyandu
    Route::get('/posyandu', [PosyanduController::class, 'index'])->name('posyandu.index');
    Route::get('/posyandu/tambah', [PosyanduController::class, 'create'])->name('posyandu.create');
    Route::post('/posyandu', [PosyanduController::class, 'store'])->name('posyandu.store');

    // detail satu posyandu: biodata, ringkasan, dan daftar anaknya
    Route::get('/posyandu/{id}', [PosyanduController::class, 'detail'])->whereNumber('id')->name('posyandu.detail');

    // riwayat pemeriksaan seluruh posyandu: halaman daftar dan unduh Excel
    Route::get('/riwayat', [AdminRiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/unduh', [AdminRiwayatController::class, 'unduh'])->name('riwayat.unduh');

    // daftar calon penerima bantuan (pemeriksaan terbaru tiap anak, diurutkan dari skor tertinggi)
    Route::get('/bantuan', [AdminBantuanController::class, 'index'])->name('bantuan.index');

    // detail satu anak calon penerima bantuan
    Route::get('/bantuan/{id}', [AdminBantuanController::class, 'detail'])->name('bantuan.detail');

    // tindak lanjut bantuan: catat dihubungi, hasil dokter spesialis, dan keputusan
    Route::post('/bantuan/{id}/hubungi', [AdminBantuanController::class, 'hubungi'])->name('bantuan.hubungi');
    Route::post('/bantuan/{id}/hasil', [AdminBantuanController::class, 'hasil'])->name('bantuan.hasil');
    Route::post('/bantuan/{id}/putuskan', [AdminBantuanController::class, 'putuskan'])->name('bantuan.putuskan');

    // jenis bantuan (susu): daftar, tambah, dan ubah
    Route::get('/jenis-bantuan', [AdminJenisBantuanController::class, 'index'])->name('jenis-bantuan.index');
    Route::get('/jenis-bantuan/tambah', [AdminJenisBantuanController::class, 'create'])->name('jenis-bantuan.create');
    Route::post('/jenis-bantuan', [AdminJenisBantuanController::class, 'store'])->name('jenis-bantuan.store');
    Route::get('/jenis-bantuan/{id}/edit', [AdminJenisBantuanController::class, 'edit'])->name('jenis-bantuan.edit');
    Route::put('/jenis-bantuan/{id}', [AdminJenisBantuanController::class, 'update'])->name('jenis-bantuan.update');
});

// halaman khusus posyandu
Route::middleware(['auth', 'role:posyandu'])->prefix('posyandu')->name('posyandu.')->group(function () {
    // dashboard posyandu: angkanya dihitung di controller
    Route::get('/dashboard', [PosyanduDashboardController::class, 'index'])->name('dashboard');

    // profil posyandu: tampilkan form dan simpan isiannya
    Route::get('/profil', [PosyanduProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [PosyanduProfilController::class, 'update'])->name('profil.update');

    // pengajuan per anak: daftar, detail, terima, dan tolak
    Route::get('/pengajuan', [PosyanduPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [PosyanduPengajuanController::class, 'detail'])->name('pengajuan.detail');
    Route::post('/pengajuan/{id}/terima', [PosyanduPengajuanController::class, 'terima'])->name('pengajuan.terima');
    Route::post('/pengajuan/{id}/tolak', [PosyanduPengajuanController::class, 'tolak'])->name('pengajuan.tolak');

    // pemeriksaan anak: daftar anak, form pemeriksaan, dan simpan hasilnya
    Route::get('/pemeriksaan', [PosyanduPemeriksaanController::class, 'index'])->name('pemeriksaan.index');
    Route::get('/pemeriksaan/{id}', [PosyanduPemeriksaanController::class, 'create'])->name('pemeriksaan.create');
    Route::post('/pemeriksaan/{id}', [PosyanduPemeriksaanController::class, 'store'])->name('pemeriksaan.store');

    // riwayat pemeriksaan: daftar, bisa disaring per bulan dan dicari per nama
    Route::get('/riwayat', [PosyanduRiwayatController::class, 'index'])->name('riwayat.index');
    // detail anak: biodata anak, biodata orang tua, dan riwayat pemeriksaan
    Route::get('/anak/{id}', [PosyanduPemeriksaanController::class, 'detail'])->name('anak.detail');

    // informasi anak: daftar semua anak di posyandu ini
    Route::get('/informasi-anak', [PosyanduPemeriksaanController::class, 'informasi'])->name('informasi.index');
});

// halaman khusus orang tua
Route::middleware(['auth', 'role:ortu'])->prefix('ortu')->name('ortu.')->group(function () {
    Route::get('/dashboard', function () {
        return view('ortu.dashboard');
    })->name('dashboard');

    // biodata orang tua: tampilkan data/form dan simpan isiannya
    Route::get('/biodata', [OrtuBiodataController::class, 'edit'])->name('biodata.edit');
    Route::put('/biodata', [OrtuBiodataController::class, 'update'])->name('biodata.update');

    // data anak: daftar, tambah, dan ubah
    Route::get('/anak', [OrtuAnakController::class, 'index'])->name('anak.index');
    Route::get('/anak/tambah', [OrtuAnakController::class, 'create'])->name('anak.create');
    Route::post('/anak', [OrtuAnakController::class, 'store'])->name('anak.store');
    Route::get('/anak/{id}/edit', [OrtuAnakController::class, 'edit'])->name('anak.edit');
    Route::put('/anak/{id}', [OrtuAnakController::class, 'update'])->name('anak.update');

    // ajukan anak ke posyandu: halaman pilih posyandu dan kirim pengajuan (per anak)
    Route::get('/anak/{id}/ajukan', [OrtuAnakController::class, 'formAjukan'])->name('anak.ajukan.form');
    Route::post('/anak/{id}/ajukan', [OrtuAnakController::class, 'ajukan'])->name('anak.ajukan');

    // halaman Pilih Posyandu: daftar anak dan status pengajuannya
    Route::get('/posyandu', [OrtuPosyanduController::class, 'index'])->name('posyandu.index');

    // riwayat pemeriksaan anak (hanya lihat)
    Route::get('/riwayat', [OrtuRiwayatController::class, 'index'])->name('riwayat.index');
});
