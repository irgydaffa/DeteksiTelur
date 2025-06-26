<table class="min-w-full bg-white">
    <thead>
        <tr>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                No</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                Nama</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                Tanggal</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-left text-sm font-semibold text-egg-orange">
                Kategori</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                Jumlah Telur</th>
        </tr>
    </thead>
    <tbody>
        @forelse($detections as $detection)
            <tr class="hover:bg-egg-orange-10">
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    {{ ($detections->currentPage() - 1) * $detections->perPage() + $loop->iteration }}</td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    {{ $detection->user->nama ?? Auth::user()->nama }}
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    {{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    @if($detection->jumlah_mutu1 > 0)
                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 inline-block mr-1 mb-1">
                            MUTU 1
                        </span>
                    @endif
                    @if($detection->jumlah_mutu2 > 0)
                        <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800 inline-block mr-1 mb-1">
                            MUTU 2
                        </span>
                    @endif
                    @if($detection->jumlah_mutu3 > 0)
                        <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 inline-block mr-1 mb-1">
                            MUTU 3
                        </span>
                    @endif
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm text-center font-medium">
                    {{ $detection->jumlah_mutu1 + $detection->jumlah_mutu2 + $detection->jumlah_mutu3 }}
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">{{ $detection->jumlah_mutu1 }}</span> /
                        <span class="text-yellow-600">{{ $detection->jumlah_mutu2 }}</span> /
                        <span class="text-red-600">{{ $detection->jumlah_mutu3 }}</span>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="py-4 text-center text-gray-500">Belum ada deteksi yang dilakukan</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Pagination Controls -->
<div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between" id="pagination-controls">
    <div class="flex-1 flex justify-between sm:hidden">
        <a href="{{ $detections->previousPageUrl() }}"
            class="pagination-link {{ !$detections->onFirstPage() ? 'bg-egg-orange hover:bg-egg-orange-dark' : 'bg-gray-300 cursor-not-allowed' }} relative inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md">
            &larr; Sebelumnya
        </a>
        <a href="{{ $detections->nextPageUrl() }}"
            class="pagination-link {{ $detections->hasMorePages() ? 'bg-egg-orange hover:bg-egg-orange-dark' : 'bg-gray-300 cursor-not-allowed' }} relative inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md">
            Berikutnya &rarr;
        </a>
    </div>
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div class="text-sm text-gray-700 mb-4 sm:mb-0">
            Showing {{ $detections->firstItem() ?? 0 }} to {{ $detections->lastItem() ?? 0 }} of
            {{ $detections->total() }} results
        </div>
        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous Page -->
                <a href="{{ $detections->previousPageUrl() }}"
                    class="pagination-link {{ !$detections->onFirstPage() ? 'hover:bg-egg-orange-10 hover:text-egg-orange' : 'opacity-50 cursor-not-allowed' }} relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500">
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </a>

                <!-- Page Numbers -->
                @php
                    $currentPage = $detections->currentPage();
                    $lastPage = $detections->lastPage();

                    // Hitung range untuk menampilkan 5 halaman
                    $startPage = max(1, min($currentPage - 2, $lastPage - 4));
                    $endPage = min($lastPage, max($currentPage + 2, 5));

                    // Pastikan selalu menampilkan 5 halaman jika total halaman >= 5
                    if ($lastPage >= 5) {
                        if ($endPage - $startPage + 1 < 5) {
                            if ($currentPage < $lastPage - 2) {
                                $startPage = $currentPage;
                                $endPage = $startPage + 4 > $lastPage ? $lastPage : $startPage + 4;
                            } else {
                                $endPage = $lastPage;
                                $startPage = $endPage - 4 < 1 ? 1 : $endPage - 4;
                            }
                        }
                    } else {
                        $startPage = 1;
                        $endPage = $lastPage;
                    }
                @endphp

                <!-- First Page (if not in range) -->
                @if($startPage > 1)
                    <a href="{{ $detections->url(1) }}"
                        class="pagination-link bg-white border-gray-300 text-gray-500 hover:bg-egg-orange-10 hover:text-egg-orange relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                        1
                    </a>
                    @if($startPage > 2)
                        <span
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                    @endif
                @endif

                <!-- Sliding Window Pages -->
                @for($i = $startPage; $i <= $endPage; $i++)
                            <a href="{{ $detections->url($i) }}"
                                class="pagination-link {{ $i == $detections->currentPage()
                    ? 'bg-egg-orange-10 border-egg-orange text-egg-orange relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-egg-orange-10 hover:text-egg-orange relative inline-flex items-center px-4 py-2 border text-sm font-medium' }}">
                                {{ $i }}
                            </a>
                @endfor

                <!-- Last Page (if not in range) -->
                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <span
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                    @endif
                    <a href="{{ $detections->url($lastPage) }}"
                        class="pagination-link bg-white border-gray-300 text-gray-500 hover:bg-egg-orange-10 hover:text-egg-orange relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                        {{ $lastPage }}
                    </a>
                @endif

                <!-- Next Page -->
                <a href="{{ $detections->nextPageUrl() }}"
                    class="pagination-link {{ $detections->hasMorePages() ? 'hover:bg-egg-orange-10 hover:text-egg-orange' : 'opacity-50 cursor-not-allowed' }} relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500">
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </nav>
        </div>
    </div>
</div>