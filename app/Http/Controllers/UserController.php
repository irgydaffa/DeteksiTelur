<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{


    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = User::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply ordering to the same query object
        $query->orderBy('created_at', 'desc');

        // Paginate the results from the query
        $users = $query->paginate(10);

        // Ensure pagination retains the search parameter
        if ($search) {
            $users->appends(['search' => $search]);
        }

        if (request()->ajax() || request()->has('ajax')) {
            return view('admin.partials.users-table', compact('users'));
        }
        return view('admin.manajemen', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna
     */
    public function create()
    {
        return view('admin.create');
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,inventor,petugas',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'aktif',
        ]);

        return redirect()->route('admin.manajemen')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit pengguna
     */
    public function edit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    /**
     * Update data pengguna
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,petugas,inventor',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userData = [
            'nama' => $request->nama,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('admin.manajemen')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna
     */
    public function destroy(User $user)
    {
        // Cek jika user mencoba menghapus dirinya sendiri
        if ($user->id == session('user_id')) {
            return redirect()->route('admin.manajemen')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.manajemen')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Toggle status pengguna (aktif/nonaktif)
     */
    public function toggleStatus(User $user)
    {
        // Cek jika user mencoba mengubah status dirinya sendiri
        if ($user->id == session('user_id')) {
            return redirect()->route('admin.manajemen')
                ->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        // Toggle status
        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        $statusText = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.manajemen')
            ->with('success', "Pengguna {$user->nama} berhasil {$statusText}.");
    }


}