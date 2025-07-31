@extends('layout.app')

@section('title', 'Tambah Pengguna Baru')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h1>
            <a href="{{ route('admin.manajemen') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 text-base rounded-md">
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
                    <div class="relative">
                        <input type="password" name="password" id="password"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 pr-12 @error('password') border-red-500 @enderror">
                        <button type="button" onclick="togglePassword('password')"
                            class="absolute inset-y-0 right-4 flex items-center ">
                            <svg id="eye-icon-password" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none"
                                viewBox="0 0 25 25" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-slash-icon-password" class="h-5 w-5 text-gray-400 hover:text-gray-600 hidden"
                                fill="none" viewBox="0 0 25 25" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L7.05 7.05M9.878 9.878a3 3 0 013.128-2.878m0 0L17.8 2.2M17.8 2.2l-8 8M17.8 2.2L2.2 17.8" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-base font-medium text-gray-700 mb-2">Konfirmasi
                        Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-base border-gray-300 rounded-md py-3 px-4 pr-12">
                        <button type="button" onclick="togglePassword('password_confirmation')"
                            class="absolute inset-y-0 right-4 flex items-center">
                            <svg id="eye-icon-password_confirmation" class="h-5 w-5 text-gray-400 hover:text-gray-600"
                                fill="none" viewBox="0 0 25 25" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-slash-icon-password_confirmation"
                                class="h-5 w-5 text-gray-400 hover:text-gray-600 hidden" fill="none" viewBox="0 0 25 25"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L7.05 7.05M9.878 9.878a3 3 0 013.128-2.878m0 0L17.8 2.2M17.8 2.2l-8 8M17.8 2.2L2.2 17.8" />
                            </svg>
                        </button>
                    </div>
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

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(`eye-icon-${fieldId}`);
            const eyeSlashIcon = document.getElementById(`eye-slash-icon-${fieldId}`);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
@endsection