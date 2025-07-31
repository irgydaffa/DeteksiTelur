<?php
namespace App\Http\Controllers;

use App\Models\DeteksiTelur;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class DeteksiController extends Controller
{
    public function index()
    {
        return view('deteksi.form', ['headerTitle' => 'Deteksi Mutu Telur']);
    }

    public function detect(Request $request)
    {
        // Custom error messages untuk validasi
        $messages = [
            'images.required' => 'Silakan pilih minimal satu gambar untuk dideteksi.',
            'images.array' => 'Format upload tidak valid.',
            'images.*.image' => 'File yang dipilih bukan gambar yang valid.',
            'images.*.mimes' => 'Format file tidak didukung. Format yang didukung: JPG, JPEG, dan PNG.',
            'images.*.max' => 'Ukuran gambar tidak boleh lebih dari 10MB.'
        ];

        // Validasi dengan custom messages
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:10240',
        ], $messages);

        // Jika validasi gagal, redirect kembali dengan pesan error
        if ($validator->fails()) {
            return redirect()->route('detect.form')
                ->withErrors($validator)
                ->withInput()
                ->with('errorType', 'formatError');
        }

        if (!file_exists(public_path('detections'))) {
            mkdir(public_path('detections'), 0755, true);
        }

        $results = [];
        $totalProcessed = 0;

        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . $totalProcessed . '.' . $image->getClientOriginalExtension();
            // Pindahkan file dengan benar
            $image->move(public_path('detections'), $imageName);
            $imagePath = public_path('detections/' . $imageName);
            $imageUrl = asset('detections/' . $imageName); // URL lokal untuk gambar

            try {
                $response = Http::attach(
                    'image',
                    file_get_contents($imagePath),
                    $imageName
                )->post('http://127.0.0.1:5000/detect');

                if ($response->successful()) {
                    $responseData = $response->json();
                    $hasDetections = !empty($responseData['detections']);

                    // Prepare result data
                    $resultData = [
                        'imageBase64' => $responseData['image_base64'] ?? null,
                        'fileName' => $imageName,
                        'imageUrl' => $imageUrl,
                        'hasDetections' => $hasDetections,
                        'detections' => $responseData['detections'] ?? []
                    ];

                    // Initialize egg counts
                    $eggCounts = [
                        'MUTU 1' => 0,
                        'MUTU 2' => 0,
                        'MUTU 3' => 0,
                    ];

                    // Only save to database if there are detections
                    if ($hasDetections) {
                        // Count eggs by category
                        foreach ($responseData['detections'] as $detection) {
                            $label = $detection['label'];

                            if (stripos($label, 'mutu 1') !== false) {
                                $eggCounts['MUTU 1']++;
                            } elseif (stripos($label, 'mutu 2') !== false) {
                                $eggCounts['MUTU 2']++;
                            } elseif (
                                stripos($label, 'mutu 3') !== false ||
                                in_array($label, ['retak', 'pecah', 'kotor_parah'])
                            ) {
                                $eggCounts['MUTU 3']++;
                            }
                        }

                        // Determine main category
                        $kategori = $this->determineEggCategory($responseData['detections']);

                        // Save detection to database with local URL
                        $detection = DeteksiTelur::create([
                            'user_id' => Auth::id(),
                            'nama_file' => $imageName,
                            'image_url' => $imageUrl, // URL lokal
                            'kategori' => $kategori,
                            'catatan' => $this->generateNotes($responseData['detections'], $eggCounts),
                            'jumlah_mutu1' => $eggCounts['MUTU 1'],
                            'jumlah_mutu2' => $eggCounts['MUTU 2'],
                            'jumlah_mutu3' => $eggCounts['MUTU 3']
                        ]);

                        $resultData['detection'] = $detection;
                        $resultData['eggCounts'] = $eggCounts;
                    }

                    $results[] = $resultData;
                    $totalProcessed++;
                } else {
                    // Tambahkan error response ke hasil
                    $results[] = [
                        'fileName' => $imageName,
                        'error' => 'API detection failed: ' . $response->status(),
                        'hasDetections' => false,
                        'imageBase64' => null,
                        'imageUrl' => $imageUrl // URL lokal
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Detection error: ' . $e->getMessage());
                $results[] = [
                    'fileName' => $imageName,
                    'error' => $e->getMessage(),
                    'hasDetections' => false,
                    'imageBase64' => null,
                    'imageUrl' => $imageUrl // URL lokal
                ];
            }
        }

        // If only one image was uploaded, use the single result page
        if (count($results) === 1) {
            return view('deteksi.result', $results[0]);
        }

        // For multiple images, use the multiple results page
        return view('deteksi.multiple-result', [
            'results' => $results,
            'totalProcessed' => $totalProcessed
        ]);
    }

    private function determineEggCategory($detections)
    {
        if (empty($detections)) {
            return null;
        }

        $counts = ['MUTU 1' => 0, 'MUTU 2' => 0, 'MUTU 3' => 0];

        foreach ($detections as $detection) {
            $label = $detection['label'];

            if (stripos($label, 'mutu 3') !== false || in_array($label, ['retak', 'pecah', 'kotor_parah'])) {
                $counts['MUTU 3']++;
            } elseif (stripos($label, 'mutu 2') !== false || in_array($label, ['kotor_ringan', 'tidak_rata'])) {
                $counts['MUTU 2']++;
            } elseif (stripos($label, 'mutu 1') !== false) {
                $counts['MUTU 1']++;
            }
        }

        if ($counts['MUTU 3'] > 0) {
            return 'MUTU 3';
        } elseif ($counts['MUTU 2'] > 0) {
            return 'MUTU 2';
        } else {
            return 'MUTU 1';
        }
    }

    private function generateNotes($detections, $eggCounts = null)
    {
        if (empty($detections)) {
            return 'Tidak ada telur yang terdeteksi.';
        }

        $totalEggs = 0;
        if ($eggCounts) {
            $totalEggs = $eggCounts['MUTU 1'] + $eggCounts['MUTU 2'] + $eggCounts['MUTU 3'];
        } else {
            $totalEggs = count($detections);
        }

        $notes = 'Terdeteksi ' . $totalEggs . ' telur: ';

        if ($eggCounts) {
            $details = [];
            if ($eggCounts['MUTU 1'] > 0) {
                $details[] = $eggCounts['MUTU 1'] . ' telur Mutu 1';
            }
            if ($eggCounts['MUTU 2'] > 0) {
                $details[] = $eggCounts['MUTU 2'] . ' telur Mutu 2';
            }
            if ($eggCounts['MUTU 3'] > 0) {
                $details[] = $eggCounts['MUTU 3'] . ' telur Mutu 3';
            }
            $notes .= implode(', ', $details) . '.';
        } else {
            foreach ($detections as $detection) {
                $notes .= $detection['label'] . ' (' . number_format($detection['confidence'] * 100, 1) . '%), ';
            }
            $notes = rtrim($notes, ', ') . '.';
        }

        return $notes;
    }
    //buatkan controller webcam
    public function webcam()
    {
        return view('deteksi.webcam', ['headerTitle' => 'Deteksi Mutu Telur - Webcam']);
    }

    public function webcamDetect(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'webcam_image' => 'required|file|image|max:10240',
        ], [
            'webcam_image.required' => 'Tidak ada gambar yang dikirim.',
            'webcam_image.image' => 'File yang dikirim bukan gambar yang valid.',
            'webcam_image.max' => 'Ukuran gambar tidak boleh lebih dari 10MB.'
        ]);

        if ($validator->fails()) {
            return redirect()->route('webcam')
                ->withErrors($validator)
                ->with('error', 'Gagal memproses gambar: ' . $validator->errors()->first());
        }

        // Buat direktori jika belum ada
        if (!file_exists(public_path('detections'))) {
            mkdir(public_path('detections'), 0755, true);
        }

        // Simpan gambar dari webcam
        $image = $request->file('webcam_image');
        $imageName = time() . '_webcam.' . $image->getClientOriginalExtension();
        $image->move(public_path('detections'), $imageName);
        $imagePath = public_path('detections/' . $imageName);
        $imageUrl = asset('detections/' . $imageName);

        try {
            $response = Http::attach(
                'image',
                file_get_contents($imagePath),
                $imageName
            )->post('http://127.0.0.1:5000/detect');

            if ($response->successful()) {
                $responseData = $response->json();
                $hasDetections = !empty($responseData['detections']);

                // Prepare result data
                $resultData = [
                    'imageBase64' => $responseData['image_base64'] ?? null,
                    'fileName' => $imageName,
                    'imageUrl' => $imageUrl,
                    'hasDetections' => $hasDetections,
                    'detections' => $responseData['detections'] ?? []
                ];

                // Hanya simpan ke database jika ada deteksi
                if ($hasDetections) {
                    // Hitung telur berdasarkan kategori
                    $eggCounts = [
                        'MUTU 1' => 0,
                        'MUTU 2' => 0,
                        'MUTU 3' => 0,
                    ];

                    foreach ($responseData['detections'] as $detection) {
                        $label = $detection['label'];

                        if (stripos($label, 'mutu 1') !== false) {
                            $eggCounts['MUTU 1']++;
                        } elseif (stripos($label, 'mutu 2') !== false) {
                            $eggCounts['MUTU 2']++;
                        } elseif (
                            stripos($label, 'mutu 3') !== false ||
                            in_array($label, ['retak', 'pecah', 'kotor_parah'])
                        ) {
                            $eggCounts['MUTU 3']++;
                        }
                    }

                    // Tentukan kategori utama
                    $kategori = $this->determineEggCategory($responseData['detections']);

                    // Simpan deteksi ke database
                    $detection = DeteksiTelur::create([
                        'user_id' => Auth::id(),
                        'nama_file' => $imageName,
                        'image_url' => $imageUrl,
                        'kategori' => $kategori,
                        'catatan' => $this->generateNotes($responseData['detections'], $eggCounts),
                        'jumlah_mutu1' => $eggCounts['MUTU 1'],
                        'jumlah_mutu2' => $eggCounts['MUTU 2'],
                        'jumlah_mutu3' => $eggCounts['MUTU 3']
                    ]);

                    $resultData['detection'] = $detection;
                    $resultData['eggCounts'] = $eggCounts;
                }

                // Tampilkan hasil
                return view('deteksi.result', $resultData);
            } else {
                return redirect()->route('webcam')
                    ->with('error', 'API detection failed: ' . $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Webcam detection error: ' . $e->getMessage());
            return redirect()->route('webcam')
                ->with('error', 'Gagal mendeteksi gambar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $detection = DeteksiTelur::with('user')->findOrFail($id);

            // All authenticated users can view any detection (no access restriction)

            // Get all related images for this detection (if any)
            $relatedImages = [];

            // Primary image
            if ($detection->image_url && file_exists(public_path('detections/' . $detection->nama_file))) {
                $relatedImages[] = [
                    'nama_file' => $detection->nama_file,
                    'image_url' => $detection->image_url,
                    'is_primary' => true
                ];
            }

            // Look for other images with similar timestamp (for multiple results)
            if ($detection->nama_file) {
                // Extract timestamp from filename (format: timestamp_index.ext)
                $parts = explode('_', pathinfo($detection->nama_file, PATHINFO_FILENAME));
                if (count($parts) >= 2) {
                    $timestamp = $parts[0];
                    $detectionDir = public_path('detections');

                    if (is_dir($detectionDir)) {
                        $files = glob($detectionDir . '/' . $timestamp . '_*.{jpg,jpeg,png}', GLOB_BRACE);

                        foreach ($files as $file) {
                            $filename = basename($file);
                            if ($filename !== $detection->nama_file) {
                                $relatedImages[] = [
                                    'nama_file' => $filename,
                                    'image_url' => asset('detections/' . $filename),
                                    'is_primary' => false
                                ];
                            }
                        }
                    }
                }
            }

            // Get detection results for all images
            $detectionResults = [];
            foreach ($relatedImages as $image) {
                $detectedImageBase64 = null;
                $detectionDetails = null;

                try {
                    $imagePath = public_path('detections/' . $image['nama_file']);
                    if (file_exists($imagePath)) {
                        $response = Http::attach(
                            'image',
                            file_get_contents($imagePath),
                            $image['nama_file']
                        )->post('http://127.0.0.1:5000/detect');

                        if ($response->successful()) {
                            $responseData = $response->json();
                            $detectedImageBase64 = $responseData['image_base64'] ?? null;
                            $detectionDetails = $responseData['detections'] ?? [];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to get detection details for ' . $image['nama_file'] . ': ' . $e->getMessage());
                }

                $detectionResults[] = [
                    'image' => $image,
                    'detectedImageBase64' => $detectedImageBase64,
                    'detectionDetails' => $detectionDetails,
                    'hasDetections' => !empty($detectionDetails)
                ];
            }

            return view('deteksi.detail', compact('detection', 'detectionResults'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Detail deteksi tidak ditemukan.');
        }
    }
}