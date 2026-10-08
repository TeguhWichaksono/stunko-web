<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    // $roles berisi daftar peran yang boleh masuk, contoh: role:admin
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // kalau peran akun tidak ada di daftar, tolak dengan error 403
        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}