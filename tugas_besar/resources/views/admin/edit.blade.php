@extends('layout.app')

@section('title', 'Edit Pengguna')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
            <a href="{{ route('admin.manajemen') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 text-base rounded-md">
                Kembali
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden p-8">
            <form action="{{ route('admin.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="nama" class="block text-base font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $user->nama) }}"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('nama') border-red-500 @enderror">
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-base font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-base font-medium text-gray-700 mb-2">
                        Password (kosongkan jika tidak ingin mengubah)
                    </label>
                    <input type="password" name="password" id="password"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-base font-medium text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="role" class="block text-base font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" id="role"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('role') border-red-500 @enderror">
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="petugas" {{ $user->role == 'petugas' ? 'selected' : '' }}>Petugas</option>
                            <option value="inventor" {{ $user->role == 'inventor' ? 'selected' : '' }}>Inventor</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- <div>
                            <label for="status" class="block text-base font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="status"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 @error('status') border-red-500 @enderror">
                                <option value="active" {{ ($user->status == 'aktif') ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ ($user->status == 'nonaktif') ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div> -->
                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin memperbarui data pengguna?')"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 text-base rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection