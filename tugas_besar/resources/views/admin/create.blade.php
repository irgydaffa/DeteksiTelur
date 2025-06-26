@extends('layout.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h1>
            <a href="{{ route('admin.manajemen') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 text-base rounded-md">
                Kembali
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden p-8">
            <form action="{{ route('admin.store') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="nama" class="block text-base font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" 
                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('nama') border-red-500 @enderror">
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-base font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" 
                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-base font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" id="password" 
                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-base font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="role" class="block text-base font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" id="role"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('role') border-red-500 @enderror">
                            <option value="admin" {{ old('role', isset($user) ? $user->role : '') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="petugas" {{ old('role', isset($user) ? $user->role : '') == 'petugas' ? 'selected' : '' }}>Petugas</option>
                            <option value="inventor" {{ old('role', isset($user) ? $user->role : '') == 'inventor' ? 'selected' : '' }}>Inventor</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 text-base rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection