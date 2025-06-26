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
                // Kirim ke API deteksi
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
                        'imageUrl' => $imageUrl, // URL lokal
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
}