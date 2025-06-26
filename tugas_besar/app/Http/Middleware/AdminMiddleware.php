<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Cek menggunakan Auth facade
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // Cek menggunakan session (fallback)
        if ($request->session()->get('user_role') === 'admin') {
            return $next($request);
        }

        // Jika bukan admin, redirect dengan pesan
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}