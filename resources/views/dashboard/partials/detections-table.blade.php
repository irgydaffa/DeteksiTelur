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
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                Mutu 1</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                Mutu 2</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                Mutu 3</th>
            <th
                class="py-2 px-4 border-b border-gray-200 bg-egg-orange-10 text-center text-sm font-semibold text-egg-orange">
                Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($detections as $detection)
            <tr class="hover:bg-egg-orange-10">
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    {{ ($detections->currentPage() - 1) * $detections->perPage() + $loop->iteration }}
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    {{ $detection->user->nama ?? Auth::user()->nama }}
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm">
                    {{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm text-center font-medium">
                    <span class="text-lg font-bold {{ $detection->jumlah_mutu1 > 0 ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $detection->jumlah_mutu1 }}
                    </span>
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm text-center font-medium">
                    <span
                        class="text-lg font-bold {{ $detection->jumlah_mutu2 > 0 ? 'text-yellow-600' : 'text-gray-400' }}">
                        {{ $detection->jumlah_mutu2 }}
                    </span>
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm text-center font-medium">
                    <span class="text-lg font-bold {{ $detection->jumlah_mutu3 > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $detection->jumlah_mutu3 }}
                    </span>
                </td>
                <td class="py-2 px-4 border-b border-gray-200 text-sm text-center">
                    <a href="{{ route('deteksi.detail', $detection->id) }}"
                        class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        Lihat
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-4 text-center text-gray-500">Belum ada deteksi yang dilakukan</td>
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