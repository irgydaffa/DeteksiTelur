<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AuthenticateUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('user_id')) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = $request->session()->get('user_id');
        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget(['user_id', 'user_role']);
            return redirect('/login')->with('error', 'Akun pengguna tidak ditemukan.');
        }

        if ($user->status !== 'aktif') {
            $request->session()->forget(['user_id', 'user_role']);
            return redirect('/login')->with('error', 'Akun Anda telah dinonaktifkan. Silahkan hubungi administrator.');
        }

        // Tetapkan user ke view
        view()->share('currentUser', $user);

        return $next($request);
    }
}