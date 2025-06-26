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
                    <video id="webcam" autoplay playsinline width="640" height="480" class="mx-auto"></video>
                    <canvas id="canvas" class="hidden"></canvas>
                </div>
                
                <div class="mt-4 flex justify-center space-x-4">
                    <button id="start-button" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-video mr-2"></i> Mulai Webcam
                    </button>
                    <button id="capture-button" disabled class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded opacity-50">
                        <i class="fas fa-camera mr-2"></i> Ambil Gambar
                    </button>
                    <button id="upload-button" disabled class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded opacity-50">
                        <i class="fas fa-upload mr-2"></i> Upload & Deteksi
                    </button>
                </div>
            </div>
            
            <div id="capture-preview" class="hidden mt-6">
                <h3 class="text-lg font-semibold mb-3">Preview Gambar</h3>
                <div class="bg-gray-100 p-4 rounded">
                    <img id="captured-image" src="" alt="Captured" class="mx-auto rounded max-h-96">
                </div>
            </div>
            
            <div class="mt-8 border-t pt-6">
                <h2 class="text-lg font-semibold mb-4">Panduan Penggunaan Webcam</h2>
                <div class="bg-gray-50 p-4 rounded">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Klik tombol "Mulai Webcam" untuk mengaktifkan kamera</li>
                        <li>Pastikan telur terlihat jelas dalam frame kamera</li>
                        <li>Klik tombol "Ambil Gambar" untuk mengambil foto</li>
                        <li>Periksa hasil gambar pada preview</li>
                        <li>Klik "Upload & Deteksi" untuk memulai proses deteksi telur</li>
                    </ol>
                    <p class="mt-4 text-sm text-gray-600">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                        Pastikan pencahayaan cukup dan telur terlihat jelas untuk hasil deteksi terbaik.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let streaming = false;
    let video = document.getElementById('webcam');
    let canvas = document.getElementById('canvas');
    let startButton = document.getElementById('start-button');
    let captureButton = document.getElementById('capture-button');
    let uploadButton = document.getElementById('upload-button');
    let capturedImage = null;

    startButton.addEventListener('click', function() {
        if (!streaming) {
            startWebcam();
            startButton.innerHTML = '<i class="fas fa-stop mr-2"></i> Stop Webcam';
            startButton.classList.remove('bg-blue-500', 'hover:bg-blue-600');
            startButton.classList.add('bg-red-500', 'hover:bg-red-600');
        } else {
            stopWebcam();
            startButton.innerHTML = '<i class="fas fa-video mr-2"></i> Mulai Webcam';
            startButton.classList.remove('bg-red-500', 'hover:bg-red-600');
            startButton.classList.add('bg-blue-500', 'hover:bg-blue-600');
        }
    });

    captureButton.addEventListener('click', function() {
        if (streaming) {
            captureImage();
            document.getElementById('capture-preview').classList.remove('hidden');
        }
    });

    uploadButton.addEventListener('click', function() {
        if (capturedImage) {
            uploadImage();
        }
    });

    function startWebcam() {
        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
            .then(function(stream) {
                video.srcObject = stream;
                video.play();
                streaming = true;
                captureButton.disabled = false;
                captureButton.classList.remove('opacity-50');
            })
            .catch(function(err) {
                console.log("An error occurred: " + err);
                alert("Tidak dapat mengakses webcam. Pastikan browser Anda mengizinkan akses kamera.");
            });
    }

    function stopWebcam() {
        if (streaming) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            streaming = false;
            captureButton.disabled = true;
            captureButton.classList.add('opacity-50');
            uploadButton.disabled = true;
            uploadButton.classList.add('opacity-50');
        }
    }

    function captureImage() {
        const width = video.videoWidth;
        const height = video.videoHeight;
        
        canvas.width = width;
        canvas.height = height;
        
        // Draw the video frame to canvas
        canvas.getContext('2d').drawImage(video, 0, 0, width, height);
        
        // Convert to blob
        canvas.toBlob(function(blob) {
            capturedImage = blob;
            document.getElementById('captured-image').src = URL.createObjectURL(blob);
            uploadButton.disabled = false;
            uploadButton.classList.remove('opacity-50');
        }, 'image/jpeg', 0.9);
    }

    function uploadImage() {
        if (!capturedImage) return;
        
        uploadButton.disabled = true;
        uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        
        const formData = new FormData();
        formData.append('webcam_image', capturedImage, 'webcam.jpg');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("detect.webcam") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Replace current page with response HTML
            document.documentElement.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengupload gambar: ' + error.message);
            uploadButton.disabled = false;
            uploadButton.innerHTML = '<i class="fas fa-upload mr-2"></i> Upload & Deteksi';
        });
    }
</script>
@endsection