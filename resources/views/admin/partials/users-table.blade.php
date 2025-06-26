<table class="min-w-full bg-white">
    <thead>
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-egg-orange uppercase tracking-wider">
                No
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-egg-orange uppercase tracking-wider">
                Nama
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-egg-orange uppercase tracking-wider">
                Email
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-egg-orange uppercase tracking-wider">
                Role
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-egg-orange uppercase tracking-wider">
                Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-egg-orange uppercase tracking-wider">
                Aksi
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @forelse($users as $key => $user)
            <tr class="hover:bg-egg-orange-10 border-b border-gray-200">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $user->nama }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ $user->email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 rounded-full text-xs 
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' :
        ($user->role === 'inventor' ? 'bg-egg-orange-10 text-egg-orange' :
            'bg-teal-100 text-teal-800') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        class="px-2 py-1 rounded-full text-xs 
                                    {{ $user->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.edit', $user->id) }}" class="text-egg-orange hover:text-egg-orange-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                            </svg>
                        </a>

                        @if(session('user_id') != $user->id)
                            <form method="POST" action="{{ route('admin.toggle-status', $user->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="text-{{ $user->status === 'aktif' ? 'red' : 'green' }}-600 hover:text-{{ $user->status === 'aktif' ? 'red' : 'green' }}-900"
                                    onclick="return confirm('Apakah Anda yakin ingin {{ $user->status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan' }} pengguna ini?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" 
                                    clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.destroy', $user->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            <span class="text-sm text-gray-400 italic">(Akun Anda)</span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-6 px-4 border-b border-gray-200 text-center text-gray-500">
                    Tidak ada data pengguna
                </td>
            </tr>
        @endforelse
    </tbody>
</table>


<div class="px-4 py-3 border-t border-gray-200">
    <div class="pagination-white">
        @if ($users->hasPages())
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <!-- Showing info di sebelah kiri -->
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
                    results
                </div>
                
                <!-- Pagination di sebelah kanan -->
                <nav class="relative z-0 inline-flex shadow-sm rounded-md">
                    <!-- Previous -->
                    @if ($users->onFirstPage())
                        <span
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed">
                        <
                        </span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md text-sm font-medium text-egg-orange bg-white border border-gray-300 hover:text-egg-orange-dark pagination-link">
                            <
                        </a>
                    @endif

                    <!-- Nomor halaman -->
                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        @if ($page == $users->currentPage())
                            <span
                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-egg-orange bg-egg-orange-10 border border-gray-300 cursor-default">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:text-egg-orange hover:bg-egg-orange-10 pagination-link">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    <!-- Next -->
                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md text-sm font-medium text-egg-orange bg-white border border-gray-300 hover:text-egg-orange-dark pagination-link">
                            >
                        </a>
                    @else
                        <span
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed">
                            >
                        </span>
                    @endif
                </nav>
            </div>
        @endif
    </div>
</div>