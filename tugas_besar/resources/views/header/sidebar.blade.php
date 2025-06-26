<div x-data="{ expanded: true }"zzz @mouseenter="expanded = true" 
     @mouseleave="expanded = false"
     :class="{ 'w-64': expanded, 'w-20': !expanded }"
     class="h-full bg-white shadow-md transition-all duration-300 ease-in-out">
    
    <div class="flex items-center justify-center h-16 border-b border-gray-200 px-2">
        <div x-show="expanded" class="flex items-center space-x-3">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 w-auto">
            <h1 class="text-lg font-semibold text-gray-800">Deteksi Telur</h1>
        </div>
        
        <div x-show="!expanded" class="flex justify-center">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="h-10 w-auto">
        </div>
    </div>
     
    <nav class="flex flex-col p-4 space-y-4">
        
        <a href="/dashboard" class="flex items-center px-3 py-2 text-gray-700 rounded hover:bg-egg-orange-10 hover:text-egg-orange transition-colors {{ request()->is('dashboard') ? 'bg-egg-orange-10 text-egg-orange' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            <span x-show="expanded" class="ml-3 transition-all duration-300">Dashboard</span>
        </a>
        
        <!-- Link Deteksi langsung ke form.blade.php -->
        <a href="{{ route('detect.form') }}" class="flex items-center px-3 py-2 text-gray-700 rounded hover:bg-egg-orange-10 hover:text-egg-orange transition-colors {{ request()->is('detect*') ? 'bg-egg-orange-10 text-egg-orange' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
            <span x-show="expanded" class="ml-3 transition-all duration-300">Deteksi Telur</span>
        </a>
        
        @if(Auth::check() && (Auth::user()->role === 'admin' || Auth::user()->role === 'inventor'))
            <a href="{{ route('laporan.index') }}" class="flex items-center px-3 py-2 text-gray-700 rounded hover:bg-egg-orange-10 hover:text-egg-orange transition-colors {{ request()->routeIs('laporan.*') ? 'bg-egg-orange-10 text-egg-orange' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span x-show="expanded" class="ml-3 transition-all duration-300">Laporan</span>
            </a>
        @endif
        
        @if(Auth::check() && Auth::user()->role === 'admin')
            <a href="{{ route('admin.manajemen') }}" class="flex items-center px-3 py-2 text-gray-700 rounded hover:bg-egg-orange-10 hover:text-egg-orange transition-colors {{ request()->routeIs('admin.*') ? 'bg-egg-orange-10 text-egg-orange' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span x-show="expanded" class="ml-3 transition-all duration-300">Manajemen Pengguna</span>
            </a>
        @endif
    </nav>
</div>