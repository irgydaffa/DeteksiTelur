@extends('layout.app')

@section('title', 'Webcam Deteksi')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Deteksi Telur via Webcam</h1>

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="mb-6">
                    <div id="webcam-container" class="bg-black rounded relative overflow-hidden">
                        <img id="video-feed" src="" alt="Live Detection Feed" width="640" height="480"
                            class="mx-auto hidden">
                        <div id="loading-message" class="text-center py-20 text-gray-500">
                            <i class="fas fa-video text-4xl mb-4"></i>
                            <p>Klik "Mulai Live Detection" untuk memulai deteksi real-time</p>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-center space-x-4">
                        <button id="start-button"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-video mr-2"></i> Mulai Live Detection
                        </button>
                        <button id="status-indicator" disabled
                            class="bg-gray-400 text-white font-bold py-2 px-4 rounded opacity-50">
                            <i class="fas fa-circle mr-2"></i> Offline
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <div class="inline-flex items-center space-x-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span>Mutu 1 (Baik)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                <span>Mutu 2 (Sedang)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span>Mutu 3 (Buruk)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="capture-preview" class="hidden mt-6">
                    <h3 class="text-lg font-semibold mb-3">Informasi Live Detection</h3>
                    <div class="bg-gray-100 p-4 rounded">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            <div class="bg-white p-3 rounded shadow">
                                <div class="text-2xl font-bold text-green-600" id="mutu1-count">0</div>
                                <div class="text-sm text-gray-600">Mutu 1</div>
                            </div>
                            <div class="bg-white p-3 rounded shadow">
                                <div class="text-2xl font-bold text-yellow-600" id="mutu2-count">0</div>
                                <div class="text-sm text-gray-600">Mutu 2</div>
                            </div>
                            <div class="bg-white p-3 rounded shadow">
                                <div class="text-2xl font-bold text-red-600" id="mutu3-count">0</div>
                                <div class="text-sm text-gray-600">Mutu 3</div>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                Deteksi berjalan secara real-time. Telur akan otomatis terdeteksi dan ditandai dengan kotak
                                berwarna.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 border-t pt-6">
                    <h2 class="text-lg font-semibold mb-4">Panduan Penggunaan Live Detection</h2>
                    <div class="bg-gray-50 p-4 rounded">
                        <ol class="list-decimal list-inside space-y-2">
                            <li>Klik tombol "Mulai Live Detection" untuk mengaktifkan deteksi real-time</li>
                            <li>Posisikan telur di depan kamera dengan pencahayaan yang cukup</li>
                            <li>Sistem akan otomatis mendeteksi dan menandai telur dengan kotak berwarna</li>
                            <li>Lihat hasil deteksi secara real-time pada video feed</li>
                            <li>Klik "Stop Detection" untuk menghentikan proses deteksi</li>
                        </ol>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-3 rounded">
                                <h4 class="font-semibold text-blue-800 mb-2">
                                    <i class="fas fa-lightbulb mr-2"></i>Tips Deteksi Optimal:
                                </h4>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• Pastikan pencahayaan yang cukup dan merata</li>
                                    <li>• Posisikan telur dengan jelas dalam frame kamera</li>
                                    <li>• Hindari bayangan yang berlebihan</li>
                                    <li>• Jaga jarak yang optimal dari kamera</li>
                                </ul>
                            </div>
                            <div class="bg-green-50 p-3 rounded">
                                <h4 class="font-semibold text-green-800 mb-2">
                                    <i class="fas fa-palette mr-2"></i>Kode Warna Deteksi:
                                </h4>
                                <ul class="text-sm text-green-700 space-y-1">
                                    <li>• <span class="text-green-600 font-bold">Hijau</span>: Mutu 1 (Kualitas Baik)</li>
                                    <li>• <span class="text-yellow-600 font-bold">Kuning</span>: Mutu 2 (Kualitas Sedang)
                                    </li>
                                    <li>• <span class="text-red-600 font-bold">Merah</span>: Mutu 3 (Kualitas Buruk)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isStreaming = false;
        let videoFeed = document.getElementById('video-feed');
        let startButton = document.getElementById('start-button');
        let statusIndicator = document.getElementById('status-indicator');
        let loadingMessage = document.getElementById('loading-message');

        startButton.addEventListener('click', function () {
            if (!isStreaming) {
                startLiveDetection();
            } else {
                stopLiveDetection();
            }
        });

        function startLiveDetection() {
            // Update UI
            startButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Connecting...';
            startButton.disabled = true;

            // Test if API is available first
            fetch('http://127.0.0.1:5000/video_feed')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('API tidak tersedia');
                    }

                    // Hide loading message and show video feed
                    loadingMessage.classList.add('hidden');
                    videoFeed.classList.remove('hidden');

                    // Set video feed source
                    videoFeed.src = 'http://127.0.0.1:5000/video_feed';

                    // Update button state
                    isStreaming = true;
                    startButton.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Detection';
                    startButton.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                    startButton.classList.add('bg-red-500', 'hover:bg-red-600');
                    startButton.disabled = false;

                    // Update status indicator
                    statusIndicator.innerHTML = '<i class="fas fa-circle mr-2 text-green-400"></i> Live Detection Active';
                    statusIndicator.classList.remove('bg-gray-400', 'opacity-50');
                    statusIndicator.classList.add('bg-green-500');

                    // Show info panel
                    document.getElementById('capture-preview').classList.remove('hidden');

                    // Start monitoring (simulate real-time counting)
                    startDetectionMonitoring();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal terhubung ke API deteksi. Pastikan server Python berjalan di http://127.0.0.1:5000');

                    // Reset button state
                    startButton.innerHTML = '<i class="fas fa-video mr-2"></i> Mulai Live Detection';
                    startButton.disabled = false;
                });
        }

        function stopLiveDetection() {
            // Stop video feed
            videoFeed.src = '';
            videoFeed.classList.add('hidden');
            loadingMessage.classList.remove('hidden');

            // Update button state
            isStreaming = false;
            startButton.innerHTML = '<i class="fas fa-video mr-2"></i> Mulai Live Detection';
            startButton.classList.remove('bg-red-500', 'hover:bg-red-600');
            startButton.classList.add('bg-blue-500', 'hover:bg-blue-600');

            // Update status indicator
            statusIndicator.innerHTML = '<i class="fas fa-circle mr-2"></i> Offline';
            statusIndicator.classList.remove('bg-green-500');
            statusIndicator.classList.add('bg-gray-400', 'opacity-50');

            // Hide info panel
            document.getElementById('capture-preview').classList.add('hidden');

            // Reset counters
            document.getElementById('mutu1-count').textContent = '0';
            document.getElementById('mutu2-count').textContent = '0';
            document.getElementById('mutu3-count').textContent = '0';

            // Stop monitoring
            if (window.detectionInterval) {
                clearInterval(window.detectionInterval);
            }
        }

        function startDetectionMonitoring() {
            // This is a simulation since we can't directly get detection counts from video stream
            // In a real implementation, you might need WebSocket or SSE for real-time data
            let detectionData = {
                mutu1: 0,
                mutu2: 0,
                mutu3: 0
            };

            window.detectionInterval = setInterval(() => {
                if (!isStreaming) return;

                // Simulate detection updates (you can replace this with actual data from API)
                // This is just for demonstration - real data would come from WebSocket or polling
                updateDetectionCounts(detectionData.mutu1, detectionData.mutu2, detectionData.mutu3);
            }, 1000);
        }

        function updateDetectionCounts(mutu1, mutu2, mutu3) {
            document.getElementById('mutu1-count').textContent = mutu1;
            document.getElementById('mutu2-count').textContent = mutu2;
            document.getElementById('mutu3-count').textContent = mutu3;
        }

        // Handle image load errors
        videoFeed.addEventListener('error', function (e) {
            console.error('Video feed error:', e);
            if (isStreaming) {
                alert('Koneksi video feed terputus. Mencoba menghubungkan kembali...');
                setTimeout(() => {
                    if (isStreaming) {
                        videoFeed.src = 'http://127.0.0.1:5000/video_feed';
                    }
                }, 2000);
            }
        });

        // Handle page unload
        window.addEventListener('beforeunload', function () {
            if (isStreaming) {
                stopLiveDetection();
            }
        });
    </script>
@endsection