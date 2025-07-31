@extends('layout.app', ['headerTitle' => 'Detail Riwayat Deteksi'])

@section('title', 'Detail Deteksi')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-6">Detail Riwayat Deteksi</h1>

                <!-- Navigation Buttons -->
                <div class="mb-6">
                    <a href="{{ route('dashboard') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                        Kembali ke Dashboard
                    </a>
                    @if($detection->image_url)
                        <a href="{{ $detection->image_url }}" download="{{ $detection->nama_file }}"
                           class="ml-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Unduh Gambar
                        </a>
                    @endif
                </div>

                <!-- Detection Image Section -->
                <div class="mb-6">
                    <h2 class="font-semibold text-lg mb-4">
                        Gambar Hasil Deteksi 
                        @if(isset($detectionResults) && count($detectionResults) > 1)
                            <span class="text-sm font-normal text-gray-600">({{ count($detectionResults) }} gambar)</span>
                        @endif
                    </h2>
                    
                    @if(isset($detectionResults) && !empty($detectionResults))
                        @if(count($detectionResults) == 1)
                            <!-- Single Image Layout -->
                            @php $result = $detectionResults[0]; @endphp
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Original Image -->
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-2">Gambar Asli:</p>
                                    <img src="{{ $result['image']['image_url'] }}" 
                                         alt="Gambar Asli {{ $result['image']['nama_file'] }}"
                                         class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                         onclick="openImageModal('{{ $result['image']['image_url'] }}', 'Gambar Asli - {{ $result['image']['nama_file'] }}')">
                                </div>
                                
                                <!-- Detected Image -->
                                <div>
                                    <p class="text-sm font-medium text-gray-700 mb-2">Hasil Deteksi:</p>
                                    @if($result['detectedImageBase64'])
                                        <img src="data:image/jpeg;base64,{{ $result['detectedImageBase64'] }}" 
                                             alt="Hasil Deteksi {{ $result['image']['nama_file'] }}"
                                             class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                             onclick="openImageModal('data:image/jpeg;base64,{{ $result['detectedImageBase64'] }}', 'Hasil Deteksi - {{ $result['image']['nama_file'] }}')">
                                    @else
                                        <div class="w-full h-auto rounded border border-gray-300 bg-gray-100 flex items-center justify-center" style="min-height: 200px;">
                                            <div class="text-center text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                                </svg>
                                                <p class="text-sm">Gambar deteksi tidak tersedia</p>
                                                <p class="text-xs mt-1">API mungkin tidak aktif</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Detection Details for Single Image -->
                            @if($result['hasDetections'] && !empty($result['detectionDetails']))
                                <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                                    <h3 class="font-medium text-gray-700 mb-3">Detail Deteksi ({{ count($result['detectionDetails']) }} objek terdeteksi)</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($result['detectionDetails'] as $detail)
                                            @php
                                                $label = $detail['label'] ?? 'Unknown';
                                                $confidence = isset($detail['confidence']) ? round($detail['confidence'] * 100, 1) : 0;
                                                
                                                // Tentukan warna berdasarkan mutu
                                                if (stripos($label, 'mutu 1') !== false) {
                                                    $badgeClass = 'bg-green-100 text-green-800';
                                                } elseif (stripos($label, 'mutu 2') !== false) {
                                                    $badgeClass = 'bg-yellow-100 text-yellow-800';
                                                } elseif (stripos($label, 'mutu 3') !== false) {
                                                    $badgeClass = 'bg-red-100 text-red-800';
                                                } else {
                                                    $badgeClass = 'bg-gray-100 text-gray-800';
                                                }
                                            @endphp
                                            <div class="flex items-center justify-between p-2 bg-white rounded border">
                                                <div class="flex items-center">
                                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $badgeClass }}">
                                                        {{ ucfirst($label) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-medium text-blue-600">{{ $confidence }}%</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Multiple Images Layout -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($detectionResults as $index => $result)
                                    <div class="bg-white border rounded-lg overflow-hidden">
                                        <div class="p-4">
                                            <h3 class="text-lg font-semibold mb-2">
                                                Gambar {{ $index + 1 }}: {{ $result['image']['nama_file'] }}
                                                @if($result['image']['is_primary'])
                                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded ml-2">Primary</span>
                                                @endif
                                            </h3>

                                            <div class="grid grid-cols-2 gap-2 mb-4">
                                                <!-- Original Image -->
                                                <div>
                                                    <p class="text-xs font-medium text-gray-600 mb-1">Gambar Asli:</p>
                                                    <img src="{{ $result['image']['image_url'] }}" 
                                                         alt="Original Image" 
                                                         class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                                         onclick="openImageModal('{{ $result['image']['image_url'] }}', 'Gambar Asli - {{ $result['image']['nama_file'] }}')">
                                                </div>
                                                
                                                <!-- Detected Image -->
                                                <div>
                                                    <p class="text-xs font-medium text-gray-600 mb-1">Hasil Deteksi:</p>
                                                    @if($result['detectedImageBase64'])
                                                        <img src="data:image/jpeg;base64,{{ $result['detectedImageBase64'] }}" 
                                                             alt="Detection Result" 
                                                             class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                                             onclick="openImageModal('data:image/jpeg;base64,{{ $result['detectedImageBase64'] }}', 'Hasil Deteksi - {{ $result['image']['nama_file'] }}')">
                                                    @else
                                                        <div class="w-full h-24 flex items-center justify-center bg-gray-100 rounded border border-gray-300">
                                                            <p class="text-gray-500 text-xs">Tidak ada hasil deteksi</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(!$result['hasDetections'])
                                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-3">
                                                    <p class="text-yellow-700 text-sm font-medium">Tidak ada objek telur yang terdeteksi dalam gambar ini.</p>
                                                </div>
                                            @else
                                                <!-- Detection Details for Multiple Images -->
                                                @if(!empty($result['detectionDetails']))
                                                    <div class="bg-gray-50 p-3 rounded">
                                                        <h4 class="font-medium text-gray-700 mb-2 text-sm">Detail Deteksi ({{ count($result['detectionDetails']) }} objek)</h4>
                                                        <div class="grid grid-cols-1 gap-2">
                                                            @foreach($result['detectionDetails'] as $detail)
                                                                @php
                                                                    $label = $detail['label'] ?? 'Unknown';
                                                                    $confidence = isset($detail['confidence']) ? round($detail['confidence'] * 100, 1) : 0;
                                                                    
                                                                    if (stripos($label, 'mutu 1') !== false) {
                                                                        $badgeClass = 'bg-green-100 text-green-800';
                                                                    } elseif (stripos($label, 'mutu 2') !== false) {
                                                                        $badgeClass = 'bg-yellow-100 text-yellow-800';
                                                                    } elseif (stripos($label, 'mutu 3') !== false) {
                                                                        $badgeClass = 'bg-red-100 text-red-800';
                                                                    } else {
                                                                        $badgeClass = 'bg-gray-100 text-gray-800';
                                                                    }
                                                                @endphp
                                                                <div class="flex items-center justify-between p-2 bg-white rounded border text-xs">
                                                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $badgeClass }}">
                                                                        {{ ucfirst($label) }}
                                                                    </span>
                                                                    <span class="font-medium text-blue-600">{{ $confidence }}%</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @elseif($detection->image_url)
                        <!-- Fallback: If detectionResults not available, show original detection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Original Image -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-2">Gambar Asli:</p>
                                <img src="{{ $detection->image_url }}" 
                                     alt="Gambar Asli {{ $detection->nama_file }}"
                                     class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                     onclick="openImageModal('{{ $detection->image_url }}', 'Gambar Asli - {{ $detection->nama_file }}')">
                            </div>
                            
                            <!-- Detected Image -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-2">Hasil Deteksi:</p>
                                <div class="w-full h-auto rounded border border-gray-300 bg-gray-100 flex items-center justify-center" style="min-height: 200px;">
                                    <div class="text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                        <p class="text-sm">Gambar deteksi tidak tersedia</p>
                                        <p class="text-xs mt-1">Menggunakan data lama</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-lg p-8 text-center">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-gray-500">Gambar tidak tersedia</p>
                        </div>
                    @endif

                    <!-- Info Panel -->
                    <div class="mt-4 bg-gray-50 p-4 rounded">
                        <h3 class="font-medium text-gray-700 mb-3">Informasi File</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-600">Nama File:</span>
                                <p class="text-gray-800 font-mono">{{ $detection->nama_file }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Pengguna:</span>
                                <p class="text-gray-800">{{ $detection->user->nama ?? 'Unknown' }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Tanggal:</span>
                                <p class="text-gray-800">{{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-600">Total Telur:</span>
                                <p class="font-bold text-blue-600">{{ $detection->jumlah_mutu1 + $detection->jumlah_mutu2 + $detection->jumlah_mutu3 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Message -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-blue-700 font-medium">Informasi Deteksi</p>
                            <p class="text-blue-600 mt-1">Hasil deteksi telur dengan sistem AI yang telah divalidasi.</p>
                        </div>
                    </div>
                </div>

                <!-- Detection Summary -->
                <div class="bg-indigo-50 p-4 rounded mb-6">
                    <h2 class="font-semibold text-lg mb-4">Ringkasan Deteksi</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Info -->
                        <div>
                            <p><span class="font-medium">ID Deteksi:</span> {{ $detection->id }}</p>
                            <p><span class="font-medium">File:</span> {{ $detection->nama_file }}</p>
                            <p><span class="font-medium">Tanggal:</span> {{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                            <p><span class="font-medium">Pengguna:</span> {{ $detection->user->nama ?? 'Unknown' }}</p>
                        </div>
                        
                        <!-- Category Info -->
                        <div>
                            <p><span class="font-medium">Kategori Utama:</span> 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $detection->kategori == 'MUTU 1' ? 'bg-green-100 text-green-800' : 
                                    ($detection->kategori == 'MUTU 2' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $detection->kategori ?? 'Tidak Diketahui' }}
                                </span>
                            </p>
                            
                            <div class="mt-3">
                                <p class="font-medium mb-1">Jumlah Telur per Kategori:</p>
                                <div class="flex flex-wrap gap-2">
                                    @if($detection->jumlah_mutu1 > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Mutu 1: {{ $detection->jumlah_mutu1 }}
                                        </span>
                                    @endif
                                    
                                    @if($detection->jumlah_mutu2 > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Mutu 2: {{ $detection->jumlah_mutu2 }}
                                        </span>
                                    @endif
                                    
                                    @if($detection->jumlah_mutu3 > 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                            Mutu 3: {{ $detection->jumlah_mutu3 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($detection->catatan)
                        <div class="mt-4 pt-4 border-t border-indigo-200">
                            <p class="font-medium">Catatan:</p>
                            <p class="text-gray-700 mt-1">{{ $detection->catatan }}</p>
                        </div>
                    @endif
                </div>

                <!-- Detailed Statistics -->
                @if(($detection->jumlah_mutu1 + $detection->jumlah_mutu2 + $detection->jumlah_mutu3) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-lg mb-4">Statistik Detail</h3>
                        
                        @php
                            $total = $detection->jumlah_mutu1 + $detection->jumlah_mutu2 + $detection->jumlah_mutu3;
                            $mutu1Percent = $total > 0 ? ($detection->jumlah_mutu1 / $total) * 100 : 0;
                            $mutu2Percent = $total > 0 ? ($detection->jumlah_mutu2 / $total) * 100 : 0;
                            $mutu3Percent = $total > 0 ? ($detection->jumlah_mutu3 / $total) * 100 : 0;
                        @endphp
                        
                        <div class="space-y-4">
                            <!-- Mutu 1 -->
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $detection->jumlah_mutu1 > 0 ? 'bg-green-50' : 'bg-gray-50' }}">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">
                                        Mutu 1
                                    </span>
                                    <div class="text-sm">
                                        <p class="font-medium">{{ $detection->jumlah_mutu1 }} telur</p>
                                        <p class="text-gray-500">{{ number_format($mutu1Percent, 1) }}% dari total</p>
                                    </div>
                                </div>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $mutu1Percent }}%"></div>
                                </div>
                            </div>

                            <!-- Mutu 2 -->
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $detection->jumlah_mutu2 > 0 ? 'bg-yellow-50' : 'bg-gray-50' }}">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-3">
                                        Mutu 2
                                    </span>
                                    <div class="text-sm">
                                        <p class="font-medium">{{ $detection->jumlah_mutu2 }} telur</p>
                                        <p class="text-gray-500">{{ number_format($mutu2Percent, 1) }}% dari total</p>
                                    </div>
                                </div>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $mutu2Percent }}%"></div>
                                </div>
                            </div>

                            <!-- Mutu 3 -->
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg {{ $detection->jumlah_mutu3 > 0 ? 'bg-red-50' : 'bg-gray-50' }}">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-3">
                                        Mutu 3
                                    </span>
                                    <div class="text-sm">
                                        <p class="font-medium">{{ $detection->jumlah_mutu3 }} telur</p>
                                        <p class="text-gray-500">{{ number_format($mutu3Percent, 1) }}% dari total</p>
                                    </div>
                                </div>
                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ $mutu3Percent }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Zoom Gambar -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center">
        <div class="relative max-w-4xl max-h-full p-4">
            <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white text-2xl font-bold z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75">
                &times;
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded">
            <p id="modalCaption" class="text-white text-center mt-4 text-lg"></p>
        </div>
    </div>

    <script>
        function openImageModal(src, caption) {
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('modalImage').src = src;
            document.getElementById('modalCaption').textContent = caption;
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endsection
