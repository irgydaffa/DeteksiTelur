<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    @vite('resources/css/app.css')
    <style>
        /* Custom color to match logo */
        .bg-egg-orange {
            background-color: #F8A057; /* Use exact color from logo */
        }
        .hover\:bg-egg-orange-dark:hover {
            background-color: #E58B3D; /* Slightly darker shade */
        }
        .focus\:border-egg-orange:focus {
            border-color: #F8A057;
        }
        .focus\:ring-egg-orange:focus {
            --tw-ring-color: rgba(248, 160, 87, 0.5);
        }
        
        /* Password toggle button positioning */
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6B7280;
        }
        
        .password-toggle:hover {
            color: #4B5563;
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">

    <div class="w-full max-w-sm bg-white p-8 rounded-xl shadow-md text-center">
        <div class="flex justify-center mb-6">
            <!-- Logo image -->
            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-16 w-auto">
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-1">Deteksi Kualitas Telur</h2>
        <p class="text-sm text-gray-500 mb-6">Masuk ke dashboard anda</p>

        {{-- Error & Success Message --}}
        @if($errors->any())
            <div class="mb-4 text-sm text-red-500">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="text-left">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm text-gray-600">Email</label>
                <input type="email" name="email"
                    class="w-full mt-1 p-2 border rounded focus:outline-none focus:ring focus:border-egg-orange"
                    placeholder="Masukkan email anda" required>
            </div>
            <div class="mb-2">
                <label for="password" class="block text-sm text-gray-600">Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password"
                        class="w-full mt-1 p-2 border rounded focus:outline-none focus:ring focus:border-egg-orange"
                        placeholder="Masukkan password" required>
                    <span class="password-toggle" id="password-toggle" title="Tampilkan password">
                        <!-- Eye icon (show password) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-open" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                        <!-- Eye-off icon (hidden by default, hide password) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-closed hidden" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                        </svg>
                    </span>
                </div>
            </div>
            <div class="flex justify-between items-center text-sm mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" 
                           class="mr-2 focus:ring focus:ring-egg-orange border-gray-300 rounded"> 
                    Ingat saya
                </label>
            </div>
            <button type="submit"
                class="w-full bg-egg-orange text-white py-2 rounded hover:bg-egg-orange-dark transition duration-200">
                Masuk
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.getElementById('password-toggle');
            const passwordField = document.getElementById('password');
            const eyeOpen = passwordToggle.querySelector('.eye-open');
            const eyeClosed = passwordToggle.querySelector('.eye-closed');
            
            passwordToggle.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    eyeOpen.classList.add('hidden');
                    eyeClosed.classList.remove('hidden');
                    passwordToggle.title = "Sembunyikan password";
                } else {
                    passwordField.type = 'password';
                    eyeOpen.classList.remove('hidden');
                    eyeClosed.classList.add('hidden');
                    passwordToggle.title = "Tampilkan password";
                }
            });
        });
    </script>
</body>

</html>