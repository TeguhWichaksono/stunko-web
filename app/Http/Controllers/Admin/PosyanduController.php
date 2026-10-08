<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PosyanduController extends Controller
{
    // tampilkan daftar akun posyandu
    public function index()
    {
        $daftar = User::where('role', 'posyandu')->orderBy('name')->get();

        return view('admin.posyandu.index', compact('daftar'));
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
