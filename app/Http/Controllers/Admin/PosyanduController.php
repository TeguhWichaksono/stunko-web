<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Pengukuran;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PosyanduController extends Controller
{
    // daftar akun posyandu beserta biodata dan jumlah anaknya
    public function index()
    {
        $akun = User::where('role', 'posyandu')->orderBy('name')->get();

        // profil tiap posyandu, dikelompokkan berdasarkan id akun
        $profil = Posyandu::all()->keyBy('user_id');

        // jumlah anak per posyandu dan per status: [id posyandu][status] = jumlah
        $hitung = [];
        $baris = Anak::whereNotNull('posyandu_id')
            ->selectRaw('posyandu_id, status, count(*) as jumlah')
            ->groupBy('posyandu_id', 'status')
            ->get();
        foreach ($baris as $b) {
            $hitung[$b->posyandu_id][$b->status] = $b->jumlah;
        }

        // satu baris data untuk tiap akun posyandu
        $daftar = $akun->map(function ($u) use ($profil, $hitung) {
            $p = $profil->get($u->id);

            return (object) [
                'user' => $u,
                'profil' => $p,
                'lengkap' => $p ? $p->lengkap() : false,
                'diterima' => $p ? ($hitung[$p->id]['diterima'] ?? 0) : 0,
                'menunggu' => $p ? ($hitung[$p->id]['menunggu'] ?? 0) : 0,
            ];
        });

        return view('admin.posyandu.index', compact('daftar'));
    }

    // halaman detail satu posyandu: biodata, ringkasan, dan daftar anaknya
    public function detail($id)
    {
        // $id adalah id akun posyandu, selain akun posyandu otomatis 404
        $user = User::where('role', 'posyandu')->findOrFail($id);

        // profil bisa kosong kalau posyandu belum pernah mengisinya
        $profil = Posyandu::where('user_id', $user->id)->first();

        $anak = collect();
        $terbaru = collect();
        $ringkasan = ['diterima' => 0, 'menunggu' => 0, 'ditolak' => 0, 'diperiksa' => 0];

        if ($profil) {
            // jumlah anak per status
            $semua = Anak::where('posyandu_id', $profil->id)->get();
            $ringkasan['diterima'] = $semua->where('status', 'diterima')->count();
            $ringkasan['menunggu'] = $semua->where('status', 'menunggu')->count();
            $ringkasan['ditolak'] = $semua->where('status', 'ditolak')->count();

            // anak yang sudah diterima, beserta orang tuanya
            $anak = Anak::with('orangTua')
                ->where('posyandu_id', $profil->id)
                ->where('status', 'diterima')
                ->orderBy('nama')
                ->get();

            // pemeriksaan TERBARU tiap anak, dikelompokkan berdasarkan id anak
            $terbaru = Pengukuran::whereIn('anak_id', $anak->pluck('id'))
                ->orderByDesc('tanggal_ukur')
                ->get()
                ->groupBy('anak_id')
                ->map(fn($baris) => $baris->first());

            // anak yang sudah diperiksa bulan ini
            $ringkasan['diperiksa'] = Pengukuran::whereIn('anak_id', $anak->pluck('id'))
                ->where('periode', now()->format('Y-m'))
                ->distinct()
                ->count('anak_id');
        }

        return view('admin.posyandu.detail', compact('user', 'profil', 'ringkasan', 'anak', 'terbaru'));
    }

    // tampilkan form tambah posyandu
    public function create()
    {
        return view('admin.posyandu.create');
    }

    // simpan akun posyandu baru
    public function store(Request $request)
    {
        // cek isian form: email tidak boleh sudah dipakai, password minimal 8 karakter
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // peran diisi 'posyandu' oleh sistem, bukan dari form
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = 'posyandu';
        $user->save();

        return redirect()->route('admin.posyandu.index')
            ->with('sukses', 'Akun posyandu berhasil dibuat.');
    }
}
