@extends('layout.app', ['headerTitle' => 'Hasil Deteksi'])

@section('title', 'Hasil Deteksi')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-6">Hasil Deteksi Telur</h1>

                @if(!isset($hasDetections) || !$hasDetections)
                    <!-- Pesan tidak ada deteksi tetap di posisi awal -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <p class="text-yellow-700 font-medium">Tidak ada objek telur yang terdeteksi dalam gambar.</p>
                        <!-- Konten lain tetap sama -->
                    </div>
                    <div class="bg-gray-50 p-4 rounded mb-6">
                        <h2 class="font-semibold text-lg mb-2">Informasi Gambar</h2>
                        <p><span class="font-medium">File:</span> {{ $fileName ?? 'Tidak diketahui' }}</p>
                        <p><span class="font-medium">Status:</span>
                            <span class="font-bold text-gray-600">TIDAK ADA OBJEK</span>
                        </p>
                    </div>
                @else
                    <!-- Bagian gambar hasil deteksi dipindah ke atas -->
                    <div class="mb-6">
                        <h2 class="font-semibold text-lg mb-4">Gambar Hasil Deteksi</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Original Image -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-2">Gambar Asli:</p>
                                <img src="{{ $imageUrl ?? '' }}" alt="Original Image"
                                    class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                    onclick="openImageModal('{{ $imageUrl ?? '' }}', 'Gambar Asli')">
                            </div>

                            <!-- Detected Image -->
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-2">Hasil Deteksi:</p>
                                <img src="data:image/jpeg;base64,{{ $imageBase64 ?? '' }}" alt="Detection Result"
                                    class="w-full h-auto rounded border border-gray-300 cursor-pointer hover:opacity-80 transition-opacity"
                                    onclick="openImageModal('data:image/jpeg;base64,{{ $imageBase64 ?? '' }}', 'Hasil Deteksi')">
                            </div>
                        </div>
                    </div>

                    <!-- Peringatan telur tidak terdeteksi -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-blue-700 font-medium">Informasi Penting</p>
                                <p class="text-blue-600 mt-1">Sistem hanya mendeteksi telur dengan tingkat kepercayaan ≥ 50%. Beberapa telur mungkin tidak terdeteksi dalam gambar ini.</p>
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

                    <!-- Ringkasan deteksi dipindahkan ke bawah -->
                    <div class="bg-indigo-50 p-4 rounded mb-6">
                        <h2 class="font-semibold text-lg mb-2">Ringkasan Deteksi</h2>

                        @if(isset($detection) && $detection)
                                <p><span class="font-medium">ID Deteksi:</span> {{ $detection->id }}</p>
                                <p><span class="font-medium">File:</span> {{ $fileName }}</p>
                                <p><span class="font-medium">Tanggal:</span>
                                    {{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                                <p><span class="font-medium">Kategori Utama:</span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $detection->kategori == 'MUTU 1' ? 'bg-green-100 text-green-800' :
                            ($detection->kategori == 'MUTU 2' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $detection->kategori }}
                                    </span>
                                </p>

                                <div class="mt-3">
                                    <p class="font-medium mb-1">Jumlah Telur per Kategori:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @if($detection->jumlah_mutu1 > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Mutu 1: {{ $detection->jumlah_mutu1 }}
                                            </span>
                                        @endif

                                        @if($detection->jumlah_mutu2 > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Mutu 2: {{ $detection->jumlah_mutu2 }}
                                            </span>
                                        @endif

                                        @if($detection->jumlah_mutu3 > 0)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Mutu 3: {{ $detection->jumlah_mutu3 }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Tampilkan confidence scores jika data detections tersedia -->
                                @if(isset($detections) && !empty($detections))
                                <div class="mt-3">
                                    <p class="font-medium mb-2">Detail Confidence Score:</p>
                                    <div class="bg-white p-3 rounded border">
                                        <div class="grid grid-cols-1 gap-2">
                                            @foreach($detections as $index => $det)
                                                @php
                                                    $label = $det['label'] ?? 'Unknown';
                                                    // Tentukan warna berdasarkan mutu
                                                    if (stripos($label, 'mutu 1') !== false) {
                                                        $mutuwarna = 'text-green-600';
                                                        $bgwarna = 'bg-green-50';
                                                    } elseif (stripos($label, 'mutu 2') !== false) {
                                                        $mutuwarna = 'text-yellow-600';
                                                        $bgwarna = 'bg-yellow-50';
                                                    } elseif (stripos($label, 'mutu 3') !== false) {
                                                        $mutuwarna = 'text-red-600';
                                                        $bgwarna = 'bg-red-50';
                                                    } else {
                                                        $mutuwarna = 'text-gray-600';
                                                        $bgwarna = 'bg-gray-50';
                                                    }
                                                @endphp
                                                <div class="flex items-center justify-between py-2 px-3 rounded {{ $bgwarna }} border-l-4 
                                                    {{ stripos($label, 'mutu 1') !== false ? 'border-green-400' : 
                                                       (stripos($label, 'mutu 2') !== false ? 'border-yellow-400' : 
                                                        (stripos($label, 'mutu 3') !== false ? 'border-red-400' : 'border-gray-400')) }}">
                                                    <span class="text-sm">
                                                        <span class="font-medium {{ $mutuwarna }}">{{ $label }}</span>
                                                        <span class="text-gray-500 ml-1">#{{ $index + 1 }}</span>
                                                    </span>
                                                    <span class="text-sm font-medium 
                                                        {{ $det['confidence'] >= 0.8 ? 'text-green-700' : 
                                                           ($det['confidence'] >= 0.65 ? 'text-yellow-700' : 'text-orange-700') }}">
                                                        {{ number_format($det['confidence'] * 100, 1) }}%
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="mt-3">
                                    <p class="font-medium">Catatan:</p>
                                    <p class="text-gray-700">{{ $detection->catatan }}</p>
                                </div>
                        @else
                            <p>Deteksi berhasil tapi data tidak disimpan di database.</p>
                        @endif
                    </div>
                @endif

                <!-- Tombol navigasi tetap di bagian paling bawah -->
                <div class="flex space-x-3 mt-8">
                    <a href="{{ route('detect.form') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Deteksi Telur Lainnya
                    </a>
                    <a href="{{ route('dashboard') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Zoom Gambar -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center">
        <div class="relative max-w-4xl max-h-full mx-4">
            <button onclick="closeImageModal()"
                class="absolute top-4 right-4 text-white text-xl bg-gray-800 bg-opacity-75 hover:bg-opacity-100 rounded-full w-8 h-8 flex items-center justify-center z-10">
                ×
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-screen object-contain rounded">
            <div id="modalTitle"
                class="absolute bottom-4 left-4 text-white bg-gray-800 bg-opacity-75 px-3 py-1 rounded text-sm"></div>
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
        document.getElementById('imageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
@endsection