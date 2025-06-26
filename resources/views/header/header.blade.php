<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
        class="flex items-center space-x-2 text-gray-700 hover:text-egg-orange focus:outline-none">
        <span class="text-sm font-medium">{{ Auth::user()->nama ?? 'User' }}</span>
        <div class="h-8 w-8 rounded-full bg-egg-orange flex items-center justify-center text-white">
            {{ substr(Auth::user()->nama ?? 'U', 0, 1) }}
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
        </svg>
    </button>
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-egg-orange-10 hover:text-egg-orange">
                Logout
            </button>
        </form>
    </div>
</div>