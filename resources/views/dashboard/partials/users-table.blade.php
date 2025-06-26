<table class="min-w-full bg-white table-fixed">
    <thead>
        <tr>
            <th class="w-1/5 py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-xs font-semibold text-egg-orange truncate">
                Nama</th>
            <th class="w-1/4 py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-xs font-semibold text-egg-orange truncate">
                Email</th>
            <th class="w-1/6 py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-xs font-semibold text-egg-orange truncate">
                Role</th>
            <th class="w-1/6 py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-xs font-semibold text-egg-orange truncate">
                Status</th>
            <th class="w-1/5 py-2 px-3 border-b border-gray-200 bg-egg-orange-10 text-left text-xs font-semibold text-egg-orange truncate">
                Terdaftar</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
            <tr class="hover:bg-egg-orange-10">
                <td class="py-1 px-3 border-b border-gray-200 text-sm truncate">{{ $user->nama }}</td>
                <td class="py-1 px-3 border-b border-gray-200 text-sm truncate">{{ $user->email }}</td>
                <td class="py-1 px-3 border-b border-gray-200 text-sm truncate">
                    <span class="px-2 py-1 rounded-full text-xs 
                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' :
                    ($user->role === 'inventor' ? 'bg-egg-orange-10 text-egg-orange' : 'bg-teal-100 text-teal-800') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="py-1 px-3 border-b border-gray-200 text-sm truncate">
                    <span class="px-2 py-1 rounded-full text-xs 
                    {{ $user->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td class="py-1 px-3 border-b border-gray-200 text-sm truncate">
                    {{ $user->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="py-2 text-center text-gray-500">Tidak ada pengguna</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Pagination -->
@if ($users->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
            </div>
            
            <nav class="relative z-0 inline-flex shadow-sm rounded-md">
                @if ($users->onFirstPage())
                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                        <span class="sr-only">Previous</span>
                        &laquo;
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="users-pagination-link relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-egg-orange-10">
                        <span class="sr-only">Previous</span>
                        &laquo;
                    </a>
                @endif
                
                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                    @if ($page == $users->currentPage())
                        <span aria-current="page" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-egg-orange-10 text-sm font-medium text-egg-orange">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="users-pagination-link relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-egg-orange-10">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
                
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="users-pagination-link relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-egg-orange-10">
                        <span class="sr-only">Next</span>
                        &raquo;
                    </a>
                @else
                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                        <span class="sr-only">Next</span>
                        &raquo;
                    </span>
                @endif
            </nav>
        </div>
    </div>
@endif