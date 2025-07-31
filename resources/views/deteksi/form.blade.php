@extends('layout.app', ['headerTitle' => 'Deteksi Mutu Telur'])

@section('title', 'Deteksi Mutu Telur')

@section('styles')
    <style>
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }

        .loading-text {
            color: white;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
    </style>
@endsection

@section('content')
    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Sedang memproses deteksi telur...</div>
        <div class="loading-text text-sm mt-2">Mohon tunggu beberapa saat</div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Deteksi Mutu Telur</h1>

            @if(session('errorType') == 'formatError')
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">Format file tidak didukung</p>
                            <p class="text-xs mt-1">Hanya file gambar dengan format JPG, JPEG, dan PNG yang diperbolehkan.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-lg p-6">

                <form action="{{ route('detect.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                    id="detectionForm">
                    @csrf
                    <div class="mb-4">
                        <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Gambar Telur (maksimal 10 file, maks. 10MB per file)
                        </label>

                        <!-- Custom file input dengan tombol browse yang lebih jelas -->
                        <div class="flex items-center">
                            <label
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-l cursor-pointer border border-gray-300">
                                <i class="fas fa-folder-open mr-2"></i>Browse
                                <input type="file" name="images[]" id="images" multiple
                                    accept="image/jpeg,image/png,image/jpg" class="hidden">
                            </label>
                            <span id="file-chosen"
                                class="border border-l-0 border-gray-300 rounded-r py-2 px-3 bg-white text-gray-500 flex-grow">
                                No files selected
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-gray-500">
                            Format yang didukung: JPG, JPEG, PNG. Ukuran maksimum: 10MB per file.
                        </p>

                        @error('images')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        @error('images.*')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div id="preview" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4"></div>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <a href="{{ route('dashboard') }}"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-search mr-1"></i> Mulai Deteksi
                        </button>
                    </div>
                </form>

                <div class="mt-8 border-t pt-6">
                    <h2 class="text-lg font-semibold mb-4">Panduan Penggunaan</h2>
                    <div class="bg-gray-50 p-4 rounded">
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Pilih satu atau beberapa gambar telur yang akan dideteksi (maksimal 10 gambar)</li>
                            <li>Pastikan gambar telur terlihat jelas dengan pencahayaan yang baik</li>
                            <li>Klik tombol "Mulai Deteksi" untuk memulai proses deteksi</li>
                            <li>Tunggu hingga proses selesai dan hasil deteksi akan ditampilkan</li>
                        </ol>

                        <!-- Tambahan note untuk deteksi telur yang banyak -->
                        <div class="mt-4 bg-blue-50 p-3 rounded border-l-4 border-blue-400">
                            <p class="text-blue-700 font-medium text-sm">
                                <i class="fas fa-lightbulb mr-1"></i> Catatan Penting:
                            </p>
                            <p class="text-blue-600 text-sm mt-1">
                                Jika mendeteksi gambar dengan banyak telur dan beberapa telur tidak terdeteksi dengan baik,
                                coba lakukan deteksi dengan jumlah telur yang lebih sedikit atau foto telur secara
                                individual
                                untuk hasil deteksi yang lebih akurat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // File input handler
            const imagesInput = document.getElementById('images');
            const detectionForm = document.getElementById('detectionForm');
            const loadingOverlay = document.getElementById('loadingOverlay');

            // Pastikan elemen-elemen ada sebelum menambahkan event listener
            if (!imagesInput || !detectionForm || !loadingOverlay) {
                console.error('Element not found:', {
                    imagesInput: !!imagesInput,
                    detectionForm: !!detectionForm,
                    loadingOverlay: !!loadingOverlay
                });
                return;
            }

            // Form submission event to show loading overlay
            detectionForm.addEventListener('submit', function (e) {
                console.log('Form submission detected');

                // Validate if file is selected
                if (imagesInput.files.length === 0) {
                    e.preventDefault();
                    alert('Silakan pilih gambar terlebih dahulu');
                    return;
                }

                console.log('Showing loading overlay');
                // Show loading overlay - pastikan display mode sesuai dengan CSS
                loadingOverlay.style.display = 'flex';
            });

            // File input change handler
            imagesInput.addEventListener('change', function () {
                const fileInput = this;
                const fileCount = fileInput.files.length;
                const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
                const previewContainer = document.getElementById('preview');
                const fileChosen = document.getElementById('file-chosen');

                previewContainer.innerHTML = '';

                if (this.files.length > 10) {
                    alert('Maksimal 10 gambar yang diperbolehkan.');
                    this.value = '';
                    fileChosen.textContent = 'No files selected';
                    return;
                }

                // Update file name display
                if (this.files.length > 0) {
                    fileChosen.textContent = `${this.files.length} file dipilih`;
                } else {
                    fileChosen.textContent = 'No files selected';
                }

                // Periksa setiap file
                let hasInvalidFile = false;
                let errorMessage = '';

                Array.from(fileInput.files).forEach(file => {
                    const fileName = file.name;

                    if (!allowedExtensions.test(fileName)) {
                        hasInvalidFile = true;
                        errorMessage = `File '${fileName}' tidak didukung. Hanya file JPG, JPEG, dan PNG yang diperbolehkan.`;
                        return;
                    }

                    if (file.size > 10 * 1024 * 1024) { // 10MB in bytes
                        hasInvalidFile = true;
                        errorMessage = `File '${fileName}' terlalu besar. Ukuran maksimum adalah 10MB.`;
                        return;
                    }

                    if (file.type.match('image.*')) {
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            const div = document.createElement('div');
                            div.className = 'relative';

                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'h-32 w-full object-cover rounded border border-gray-200';

                            div.appendChild(img);
                            previewContainer.appendChild(div);
                        }

                        reader.readAsDataURL(file);
                    }
                });

                if (hasInvalidFile) {
                    alert(errorMessage);
                    fileInput.value = ''; // Clear file input
                    fileChosen.textContent = 'No files selected';
                    previewContainer.innerHTML = '';
                }
            });

            console.log('All event listeners registered');
        });
    </script>
@endsection