@extends('layout.app', ['headerTitle' => 'Hasil Deteksi Multiple'])

@section('title', 'Hasil Deteksi Multiple')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Hasil Deteksi Telur ({{ $totalProcessed }} Gambar)</h1>

        <div class="mb-6">
            <a href="{{ route('detect.form') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Deteksi Telur Lainnya
            </a>
            <a href="{{ route('dashboard') }}"
                class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Kembali ke Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @foreach($results as $index => $result)
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2">Gambar {{ $index + 1 }}: {{ $result['fileName'] }}</h3>
                        
                        @if(isset($result['error']))
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                                <p class="font-medium">Error:</p>
                                <p>{{ $result['error'] }}</p>
                            </div>
                        @endif

                        @if(!isset($result['hasDetections']) || !$result['hasDetections'])
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <p class="text-yellow-700 font-medium">Tidak ada objek telur yang terdeteksi dalam gambar ini.</p>
                                <div class="text-yellow-600 mt-2 text-sm space-y-1">
                                    <p><i class="fas fa-info-circle mr-1"></i> Beberapa kemungkinan penyebab:</p>
                                    <ul class="list-disc list-inside pl-2">
                                        <li>Pencahayaan kurang memadai atau terlalu berlebihan</li>
                                        <li>Posisi telur tidak terlihat dengan jelas</li>
                                        <li>Gambar buram atau terlalu kecil</li>
                                        <li>Telur tertutup oleh objek lain</li>
                                        <li>Latar belakang terlalu kompleks</li>
                                    </ul>
                                    <p class="pt-1"><i class="fas fa-lightbulb mr-1"></i> Saran perbaikan:</p>
                                    <ul class="list-disc list-inside pl-2">
                                        <li>Ambil gambar dengan pencahayaan merata</li>
                                        <li>Pastikan telur terlihat jelas dan tidak tertutup</li>
                                        <li>Gunakan latar belakang polos</li>
                                        <li>Jaga jarak pengambilan gambar yang optimal</li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            <!-- Sekarang semua deteksi yang dikembalikan sudah >= 50% confidence -->
                            @if(isset($result['detection']))
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-700 mb-2">Ringkasan:</h4>
                                    <div class="bg-indigo-50 p-3 rounded">
                                        <p><span class="font-medium">Kategori Utama:</span> 
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $result['detection']->kategori == 'MUTU 1' ? 'bg-green-100 text-green-800' : 
                                                ($result['detection']->kategori == 'MUTU 2' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $result['detection']->kategori }}
                                            </span>
                                        </p>
                                        
                                        <div class="mt-2">
                                            <span class="font-medium">Jumlah Telur:</span>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @if($result['detection']->jumlah_mutu1 > 0)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Mutu 1: {{ $result['detection']->jumlah_mutu1 }}
                                                    </span>
                                                @endif
                                                
                                                @if($result['detection']->jumlah_mutu2 > 0)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Mutu 2: {{ $result['detection']->jumlah_mutu2 }}
                                                    </span>
                                                @endif
                                                
                                                @if($result['detection']->jumlah_mutu3 > 0)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Mutu 3: {{ $result['detection']->jumlah_mutu3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Tampilkan confidence scores jika data detections tersedia -->
                                        @if(isset($result['detections']) && !empty($result['detections']))
                                        <div class="mt-3">
                                            <span class="font-medium">Detail Confidence:</span>
                                            <div class="mt-1 bg-white p-2 rounded border text-xs">
                                                @foreach($result['detections'] as $index => $det)
                                                    @php
                                                        $label = $det['label'] ?? 'Unknown';
                                                        // Tentukan warna berdasarkan mutu
                                                        if (stripos($label, 'mutu 1') !== false) {
                                                            $mutuwarna = 'text-green-600';
                                                            $bgwarna = 'bg-green-50';
                                                            $borderwarna = 'border-green-300';
                                                        } elseif (stripos($label, 'mutu 2') !== false) {
                                                            $mutuwarna = 'text-yellow-600';
                                                            $bgwarna = 'bg-yellow-50';
                                                            $borderwarna = 'border-yellow-300';
                                                        } elseif (stripos($label, 'mutu 3') !== false) {
                                                            $mutuwarna = 'text-red-600';
                                                            $bgwarna = 'bg-red-50';
                                                            $borderwarna = 'border-red-300';
                                                        } else {
                                                            $mutuwarna = 'text-gray-600';
                                                            $bgwarna = 'bg-gray-50';
                                                            $borderwarna = 'border-gray-300';
                                                        }
                                                    @endphp
                                                    <div class="flex justify-between py-1 px-2 mb-1 rounded {{ $bgwarna }} {{ $borderwarna }} border-l-2">
                                                        <span class="{{ $mutuwarna }} font-medium">{{ $label }} #{{ $index + 1 }}</span>
                                                        <span class="font-medium 
                                                            {{ $det['confidence'] >= 0.8 ? 'text-green-700' : 
                                                               ($det['confidence'] >= 0.65 ? 'text-yellow-700' : 'text-orange-700') }}">
                                                            {{ number_format($det['confidence'] * 100, 1) }}%
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <!-- Original Image -->
                            <div>
                                <p class="text-xs font-medium text-gray-600 mb-1">Gambar Asli:</p>
                                <img src="{{ $result['imageUrl'] }}" alt="Original Image" class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity" onclick="openImageModal('{{ $result['imageUrl'] }}', 'Gambar Asli - {{ $result['fileName'] }}')">
                            </div>
                            
                            <!-- Detected Image -->
                            <div>
                                <p class="text-xs font-medium text-gray-600 mb-1">Hasil Deteksi:</p>
                                @if(isset($result['imageBase64']))
                                    <img src="data:image/jpeg;base64,{{ $result['imageBase64'] }}" alt="Detection Result" class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity" onclick="openImageModal('data:image/jpeg;base64,{{ $result['imageBase64'] }}', 'Hasil Deteksi - {{ $result['fileName'] }}')">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded border border-gray-300">
                                        <p class="text-gray-500 text-xs">Tidak ada hasil deteksi</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Ringkasan Deteksi Keseluruhan -->
        @php
            $totalDetections = count($results);
            $totalEggsDetected = 0;
            $totalMutu1 = 0;
            $totalMutu2 = 0;
            $totalMutu3 = 0;
            $successfulDetections = 0;
            
            foreach($results as $result) {
                if(isset($result['hasDetections']) && $result['hasDetections'] && isset($result['detection'])) {
                    $successfulDetections++;
                    $totalMutu1 += $result['detection']->jumlah_mutu1 ?? 0;
                    $totalMutu2 += $result['detection']->jumlah_mutu2 ?? 0;
                    $totalMutu3 += $result['detection']->jumlah_mutu3 ?? 0;
                }
            }
            $totalEggsDetected = $totalMutu1 + $totalMutu2 + $totalMutu3;
        @endphp

        <div class="bg-indigo-50 p-4 rounded-lg shadow-md mb-6">
            <h2 class="font-semibold text-lg mb-2">Ringkasan Deteksi Keseluruhan</h2>
            
            <p><span class="font-medium">Total Gambar:</span> {{ $totalDetections }}</p>
            <p><span class="font-medium">Gambar Berhasil Deteksi:</span> {{ $successfulDetections }}</p>
            <p><span class="font-medium">Tanggal:</span> {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
            
            @php
                $mainCategory = 'MUTU 1';
                if($totalMutu2 > $totalMutu1 && $totalMutu2 > $totalMutu3) {
                    $mainCategory = 'MUTU 2';
                } elseif($totalMutu3 > $totalMutu1 && $totalMutu3 > $totalMutu2) {
                    $mainCategory = 'MUTU 3';
                }
            @endphp
            
            <p><span class="font-medium">Kategori Utama:</span> 
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    {{ $mainCategory == 'MUTU 1' ? 'bg-green-100 text-green-800' : 
                    ($mainCategory == 'MUTU 2' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $mainCategory }}
                </span>
            </p>
            
            <div class="mt-3">
                <p class="font-medium mb-1">Jumlah Telur per Kategori:</p>
                <div class="flex flex-wrap gap-2">
                    @if($totalMutu1 > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            Mutu 1: {{ $totalMutu1 }}
                        </span>
                    @endif
                    
                    @if($totalMutu2 > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                            Mutu 2: {{ $totalMutu2 }}
                        </span>
                    @endif
                    
                    @if($totalMutu3 > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                            Mutu 3: {{ $totalMutu3 }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="mt-3">
                <p class="font-medium">Catatan:</p>
                <p class="text-gray-700">
                    @if($totalEggsDetected > 0)
                        Terdeteksi total {{ $totalEggsDetected }} telur dari {{ $successfulDetections }} gambar yang berhasil diproses.
                    @else
                        Tidak ada telur yang terdeteksi dalam semua gambar yang diupload.
                    @endif
                </p>
            </div>
        </div>
        
        <!-- Catatan untuk telur yang tidak terdeteksi dalam kasus banyak telur -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-blue-700 font-medium">Informasi Penting</p>
                    <p class="text-blue-600 mt-1">Sistem hanya mendeteksi telur dengan tingkat kepercayaan ≥ 50%. Beberapa telur mungkin tidak terdeteksi dalam gambar-gambar di atas.</p>
                </div>
            </div>
            <div class="text-blue-600 mt-2 text-sm">
                <p class="font-medium mb-1">Beberapa penyebab telur tidak terdeteksi:</p>
                <ul class="list-disc list-inside pl-2 space-y-1">
                    <li>Tingkat kepercayaan deteksi di bawah 50% (otomatis difilter)</li>
                    <li>Telur tumpang tindih atau saling menutupi</li>
                    <li>Telur berada di bagian tepi gambar</li>
                    <li>Kontras atau pencahayaan pada telur tertentu kurang baik</li>
                    <li>Telur hanya terlihat sebagian atau tertutup bayangan</li>
                </ul>
                <p class="mt-2 font-medium">Saran:</p>
                <ul class="list-disc list-inside pl-2 space-y-1">
                    <li>Atur posisi telur agar tidak saling tumpang tindih</li>
                    <li>Pastikan pencahayaan merata pada semua telur</li>
                    <li>Ambil foto dengan sudut yang dapat menampilkan seluruh bagian telur</li>
                    <li>Dalam kasus banyak telur, coba deteksi dalam kelompok yang lebih kecil</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Zoom Gambar -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center">
        <div class="relative max-w-4xl max-h-full mx-4">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-xl bg-gray-800 bg-opacity-75 hover:bg-opacity-100 rounded-full w-8 h-8 flex items-center justify-center z-10">
                ×
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-screen object-contain rounded">
            <div id="modalTitle" class="absolute bottom-4 left-4 text-white bg-gray-800 bg-opacity-75 px-3 py-1 rounded text-sm"></div>
        </div>
    </div>

    <script>
        function openImageModal(imageSrc, title) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Tutup modal ketika mengklik area luar gambar
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endsection