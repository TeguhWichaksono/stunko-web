<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PosyanduController;
use App\Http\Controllers\PosyanduProfilController;
use App\Http\Controllers\OrtuBiodataController;
use App\Http\Controllers\OrtuAnakController;
use App\Http\Controllers\OrtuPosyanduController;
use App\Http\Controllers\PosyanduPengajuanController;

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
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // kelola akun posyandu
    Route::get('/posyandu', [PosyanduController::class, 'index'])->name('posyandu.index');
    Route::get('/posyandu/tambah', [PosyanduController::class, 'create'])->name('posyandu.create');
    Route::post('/posyandu', [PosyanduController::class, 'store'])->name('posyandu.store');
});

// halaman khusus posyandu
Route::middleware(['auth', 'role:posyandu'])->prefix('posyandu')->name('posyandu.')->group(function () {
    Route::get('/dashboard', function () {
        return view('posyandu.dashboard');
    })->name('dashboard');

    // profil posyandu: tampilkan form dan simpan isiannya
    Route::get('/profil', [PosyanduProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [PosyanduProfilController::class, 'update'])->name('profil.update');

    // pengajuan dari orang tua: daftar, terima, dan tolak
    Route::get('/pengajuan', [PosyanduPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::post('/pengajuan/{id}/terima', [PosyanduPengajuanController::class, 'terima'])->name('pengajuan.terima');
    Route::post('/pengajuan/{id}/tolak', [PosyanduPengajuanController::class, 'tolak'])->name('pengajuan.tolak');
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

    // pilih posyandu: tampilkan pilihan dan kirim pengajuan
    Route::get('/posyandu', [OrtuPosyanduController::class, 'index'])->name('posyandu.index');
    Route::post('/posyandu', [OrtuPosyanduController::class, 'ajukan'])->name('posyandu.ajukan');
});
