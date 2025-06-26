<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
{
    use AuthenticatesUsers;
    public function showLoginForm()
    {
        return view('layout.login');
    }

    /**
     * Validate the login request.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        $email = $request->email;
        $password = $request->password;

        // Cari user berdasarkan email
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status !== 'aktif') {
                return redirect()->back()
                    ->with('error', 'Akun Anda telah dinonaktifkan. Silahkan hubungi administrator.');
            }

            
            Auth::login($user, $request->filled('remember'));

            // Set juga session untuk middleware kustom (jaga-jaga)
            session(['user_id' => $user->id]);
            session(['user_role' => $user->role]);

            return redirect()->intended('/dashboard');
        }

        // Login gagal
        return redirect()->back()
            ->with('error', 'Email atau password salah.');
    }

    public function logout(Request $request)
    {
        // // Hapus data session
        // $request->session()->forget(['user_id', 'user_role']);
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();
        Auth::logout();

        return redirect('/login')->with('success', 'Berhasil logout.');
    }
}